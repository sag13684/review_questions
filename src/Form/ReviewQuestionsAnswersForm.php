<?php

namespace Drupal\review_questions\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\review_questions\Entity\Answers;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReviewQuestionsAnswersForm.
 *
 * @package Drupal\review_questions\Form
 */
class ReviewQuestionsAnswersForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * ReviewQuestionsAnswerForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, MailManagerInterface $mail_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('plugin.manager.mail'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'review_questions_answers_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EntityInterface $node = NULL) {
    if (!empty($node)) {
      $paragraph_ids = $node->field_review_questions->getValue();
      if (!empty($paragraph_ids)) {
        // Show submit button flag.
        $show_submit_button = FALSE;
        $paragraph_storage = $this->entityTypeManager->getStorage('paragraph');
        // Load review questions paragraphs.
        $paragraphs = $paragraph_storage->loadMultiple(array_column($paragraph_ids, 'target_id'));
        if (!empty($paragraphs)) {
          foreach ($paragraphs as $paragraph_id => $paragraph) {
            if ($paragraph->field_show_question->value) {
              $form['review_questions']['#tree'] = TRUE;
              $answer = $this->getAnswerEntity($node->id(), $paragraph_id);
              $form['review_questions'][$paragraph_id]['answer'] = [
                '#title' => $paragraph->field_question->value,
                '#type' => 'textarea',
                '#maxlength' => 600,
                '#default_value' => $answer->answer->value ?? '',
                '#required' => TRUE,
              ];
              // Show submit button flag set true if one or more elements are visible.
              $show_submit_button = TRUE;
            }
          }
          if ($show_submit_button) {
            // Save paragraphs info in the form.
            $form['#paragraphs'] = $paragraphs;
            $form['#node'] = $node;
            // Show submit button.
            $form['submit'] = [
              '#type' => 'submit',
              '#value' => $this->t('Submit Answers'),
            ];
          }
        }
      }
    }
    return $form;
  }

  /**
   * Gets answer entity by node id and paragraph id.
   *
   * @param $node_id
   *   Node id.
   * @param $paragraph_id
   *   Paragraph id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getAnswerEntity($node_id, $paragraph_id) {
    $answer = NULL;
    $answer_storage = $this->entityTypeManager->getStorage('answers');
    // Load answers by node id and paragraph id.
    $answers = $answer_storage->loadByProperties([
      'entity_id' => $node_id,
      'fc_entity_id' => $paragraph_id,
    ]);
    if (!empty($answers)) {
      $answer = reset($answers);
    }
    return $answer;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all form values.
    $values = $form_state->getValues();
    // Check if review_questions key has values.
    if (isset($values['review_questions']) && !empty($values['review_questions'])) {
      // Get paragraphs entities from the form.
      $paragraphs = $form['#paragraphs'];
      // Get node entity from the form.
      $node = $form['#node'];
      $questions = [];
      // Loap through questions from  the form.
      foreach ($values['review_questions'] as $paragraph_id => $item) {
        if (!empty($item['answer'])) {
          // Check if answer entity already exists for the question.
          $answer = $this->getAnswerEntity($node->id(), $paragraph_id);
          if (!empty($answer)) {
            // Update values for answer entity.
            $answer->question->value = $paragraphs[$paragraph_id]->field_question->value;
            $answer->answer->value = $item['answer'];
            $answer->uid->target_id = $this->currentUser->id();
          }
          else {
            // Create new answer entity.
            $answer = Answers::create([
              'entity_id' => $node->id(),
              'fc_entity_id' => $paragraph_id,
              'question' => $paragraphs[$paragraph_id]->field_question->value,
              'answer' => $item['answer'],
              'uid' => $this->currentUser->id(),
            ]);
          }
          // Save answer entity.
          $answer->save();
          // Form questions array to used for sending mail notification.
          $questions['questions'][$paragraph_id]['question'] = $paragraphs[$paragraph_id]->field_question->value;
          $questions['questions'][$paragraph_id]['answer'] = $item['answer'];
          $questions['questions'][$paragraph_id]['paragraph_entity'] = $paragraphs[$paragraph_id];
          $questions['questions'][$paragraph_id]['answer_entity'] = $answer;
          $questions['user'] = $this->currentUser;
          $questions['node'] = $node;
        }
      }
      // Show message to the user upon successful submission.
      $this->messenger()
        ->addMessage($this->t('Your answers to the review questions have been submitted.'));
      if (!empty($questions)) {
        // Send mail containing questions and answers to the configured
        // email addresses.
        $this->sendMail($questions);
      }
    }
  }

  /**
   * Sends email containing answers to configured emails.
   *
   * @param $questions
   *   Array with questions, user and node keys.
   */
  protected function sendMail($questions) {
    if (!empty($questions)) {
      $module = 'review_questions';
      $key = 'answers_notification';
      $config = $this->config('review_questions.settings');
      // Get configured email addresses from the config.
      $emails = $config->get('emails');
      if (!empty($emails)) {
        // Replace line  breaks with comma and space.
        $emails = preg_replace('/\r\n|[\r\n]/', ', ', $emails);
        // Renderable Email body.
        $body = [
          '#theme' => 'review_questions_mail',
          '#review_questions' => $questions,
        ];
        $params['message'] = $this->renderer->renderPlain($body)->__toString();
        // Email subject.
        $params['node_title'] = $questions['node']->label();
        $langcode = $this->currentUser->getPreferredLangcode();
        $send = TRUE;
        // Send email to configure email addresses.
        $result = $this->mailManager->mail($module, $key, $emails, $langcode, $params, NULL, $send);
        if ($result['result'] !== TRUE) {
          $this->messenger()
            ->addError($this->t('There was a problem sending your message and it was not sent.'));
        }
      }
    }
  }

}
