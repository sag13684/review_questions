<?php

namespace Drupal\review_questions\Entity;

use Drupal;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Answers entity.
 *
 * @ingroup review_questions
 *
 * @ContentEntityType(
 *   id = "answers",
 *   label = @Translation("Answers"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\review_questions\AnswersListBuilder",
 *     "views_data" = "Drupal\review_questions\Entity\AnswersViewsData",
 *     "translation" = "Drupal\review_questions\AnswersTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\review_questions\Form\AnswersForm",
 *       "add" = "Drupal\review_questions\Form\AnswersForm",
 *       "edit" = "Drupal\review_questions\Form\AnswersForm",
 *       "delete" = "Drupal\review_questions\Form\AnswersDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\review_questions\AnswersHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\review_questions\AnswersAccessControlHandler",
 *   },
 *   base_table = "answers",
 *   translatable = FALSE,
 *   fieldable = FALSE,
 *   admin_permission = "administer answers entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "entity_id" = "entity_id",
 *     "fc_entity_id" = "fc_entity_id",
 *     "question" = "question",
 *     "answer" = "answer",
 *     "uid" = "uid",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/answers/{answers}",
 *     "add-form" = "/admin/structure/answers/add",
 *     "edit-form" = "/admin/structure/answers/{answers}/edit",
 *     "delete-form" = "/admin/structure/answers/{answers}/delete",
 *     "collection" = "/admin/structure/answers",
 *   },
 *   field_ui_base_route = "answers.settings",
 * )
 */
class Answers extends ContentEntityBase implements AnswersInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['id']->setDescription(t('The ID of answers entity.'));

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question Entity'))
      ->setDescription(t('The Entity ID of the entity to which questions are added.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['fc_entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Paragraph ID'))
      ->setDescription(t('The Paragraph ID of Review Paragraphs.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Answers entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['question'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Question'))
      ->setDescription(t('The Question of the Answers entity.'))
      ->setSettings([
        'max_length' => 255,
        'default_value' => '',
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['answer'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Answer'))
      ->setDescription(t('The answer of the Answers entity.'))
      ->setSettings([
        'max_length' => 600,
        'default_value' => '',
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

}
