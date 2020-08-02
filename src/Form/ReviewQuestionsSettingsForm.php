<?php

namespace Drupal\review_questions\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReviewQuestionsSettingsForm.
 *
 * @package Drupal\review_questions\Form
 */
class ReviewQuestionsSettingsForm extends ConfigFormBase {

  /**
   * Email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ReviewQuestionsSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal config factory.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   Email validator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EmailValidatorInterface $email_validator, EntityTypeManagerInterface $entity_type_manager) {
    $this->emailValidator = $email_validator;
    $this->entityTypeManager = $entity_type_manager;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('email.validator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'review_questions_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get config for review_questions.
    $config = $this->config('review_questions.settings');
    // Get all node types.
    $options = node_type_get_names();

    // Checkboxes for content types.
    $form['node_types'] = [
      '#title' => $this->t('Select content types'),
      '#type' => 'checkboxes',
      '#description' => $this->t('Select content types for which Review Questions form should appear.'),
      '#options' => $options,
      '#default_value' => !empty($config->get('node_types')) ? $config->get('node_types') : [],
    ];

    // Email addresses text area.
    $form['emails'] = [
      '#title' => $this->t('Email addresses'),
      '#type' => 'textarea',
      '#description' => $this->t('Enter email address one per line.'),
      '#default_value' => !empty($config->get('emails')) ? $config->get('emails') : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Validate email addresses.
    if (!empty($form_state->getValue('emails'))) {
      $emails = preg_replace('/\r\n|[\r\n]/', ',', $form_state->getValue('emails'));
      $emails = explode(', ', $emails);
      foreach ($emails as $email) {
        if (!$this->emailValidator->isValid($email)) {
          $form_state->setErrorByName('review_questions_emails', $this->t('Please enter valid email address'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('review_questions.settings');
    // Save submitted values in config.
    $config
      ->set('node_types', $form_state->getValue('node_types'))
      ->set('emails', $form_state->getValue('emails'))
      ->save(TRUE);
    // Get all node types.
    $node_types = $form_state->getValue('node_types');
    if (!empty($node_types)) {
      foreach ($node_types as $type => $value) {
        // If the node type was selected.
        if ($value) {
          // Check if field storage exists.
          $field_storages = $this->entityTypeManager
            ->getStorage('field_storage_config')
            ->loadByProperties([
              'id' => 'node.field_review_questions',
            ]);
          // Create field storage field_review_questions.
          if (!$field_storages) {
            $field_storage = $this->entityTypeManager
              ->getStorage('field_storage_config')
              ->create([
                'field_name' => 'field_review_questions',
                'entity_type' => 'node',
                'type' => 'entity_reference_revisions',
                'cardinality' => -1,
                'settings' => ['target_type' => 'paragraph'],
              ]);
            $field_storage->save();
          }
          // Check if field already exist.
          $fields = $this->entityTypeManager
            ->getStorage('field_config')
            ->loadByProperties(['id' => 'node.' . $type . '.field_review_questions']);
          // If no fields found, create one.
          if (!$fields) {
            $field = $this->entityTypeManager
              ->getStorage('field_config')
              ->create([
                'field_name' => 'field_review_questions',
                'entity_type' => 'node',
                'bundle' => $type,
                'label' => 'Review Questions',
                'field_type' => 'entity_reference_revisions',
                'settings' => [
                  'handler' => 'default:paragraph',
                  'handler_settings' => [
                    'negate' => 0,
                    'target_bundles_drag_drop' => [
                      'review_questions' => ['enabled' => TRUE],
                    ],
                  ],
                ],
              ]);
            // Save field.
            $field->save();
            // Adds entity default view display settings for field field_review_questions.
            $view_display = $this->entityTypeManager
              ->getStorage('entity_view_display')
              ->load('node.' . $type . '.default');
            $view_display->setComponent('field_review_questions', [
              'type' => 'entity_reference_revisions_entity_view',
              'region' => 'content',
              'label' => 'above',
              'settings' => [
                'view_mode' => 'default',
                'link' => '',
              ],
            ]);
            // Save display settings.
            $view_display->save();

            // Adds entity default view form settings for field field_review_questions.
            $form_display = $this->entityTypeManager
              ->getStorage('entity_form_display')
              ->load('node.' . $type . '.default');
            $form_display->setComponent('field_review_questions', [
              'type' => 'entity_reference_paragraphs',
              'weight' => 3,
              'region' => 'content',
              'settings' => [
                'title' => 'Paragraph',
                'title_plural' => 'Paragraphs',
                'edit_mode' => 'open',
                'add_mode' => 'dropdown',
                'form_display_mode' => 'default',
                'default_paragraph_type' => 'review_questions',
              ],
            ]);
            // Save form display settings.
            $form_display->save();
          }
        }
        else {
          // Find if field exist for the content type.
          $fields = $this->entityTypeManager
            ->getStorage('field_config')
            ->loadByProperties([
              'id' => 'node.' . $type . '.field_review_questions',
            ]);
          if (!empty($fields)) {
            $field = reset($fields);
            // Delete the field.
            $field->delete();
          }
        }
      }
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['review_questions.settings'];
  }

}
