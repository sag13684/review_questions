<?php

namespace Drupal\review_questions\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxy;
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
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * ReviewQuestionsAnswerForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
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
              $form['review_questions'][$paragraph_id]['container'] = ['#type' => 'container'];
              $form['review_questions'][$paragraph_id]['container']['answer'] = [
                '#title' => $paragraph->field_question->value,
                '#type' => 'textarea',
                '#maxlength' => 600,
              ];
              // Show submit button flag set true if one or more elements are visible.
              $show_submit_button = TRUE;
            }
          }
          if ($show_submit_button) {
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Show message to the user upon successful submission.
    $this->messenger()
      ->addMessage($this->t('Your answers to the review questions have been submitted.'));
  }

}
