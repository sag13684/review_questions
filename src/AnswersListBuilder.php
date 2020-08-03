<?php

namespace Drupal\review_questions;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Answers entities.
 *
 * @ingroup review_questions
 */
class AnswersListBuilder extends EntityListBuilder {

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * AnswersListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_format
   * @param \Drupal\Core\Entity\Query\Sql\QueryFactory $entity_query
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_format) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_format;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Make Table headers sortable.
    $entity_query = $this->storage->getQuery();
    $header = $this->buildHeader();

    $entity_query->pager(50);
    $entity_query->tableSort($header);

    $uids = $entity_query->execute();

    return $this->storage->loadMultiple($uids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Table headers.
    $header['id'] = [
      'data' => $this->t('Answers ID'),
      'field' => 'id',
      'specifier' => 'id',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['entity_id'] = [
      'data' => $this->t('Entity ID'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['paragraph_id'] = [
      'data' => $this->t('Paragraph ID'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['question'] = [
      'data' => $this->t('Question'),
    ];
    $header['answer'] = [
      'data' => $this->t('Answer'),
    ];
    $header['uid'] = [
      'data' => $this->t('Owner'),
    ];
    $header['created'] = [
      'data' => $this->t('Created'),
      'field' => 'created',
      'specifier' => 'created',
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['changed'] = [
      'data' => $this->t('Updated'),
      'field' => 'changed',
      'specifier' => 'changed',
      'sort' => 'desc',
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    $header['operations'] = [
      'data' => $this->t('Operations'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\review_questions\Entity\Answers $entity */
    // Answer entity id.
    $row['id'] = Link::createFromRoute(
      $entity->id(),
      'entity.answers.edit_form',
      ['answers' => $entity->id()]
    );
    // Questions node entity id.
    $row['entity_id'] = Link::createFromRoute(
      $entity->entity_id->entity->label() . '(Nid: ' . $entity->entity_id->target_id . ')',
      'entity.node.canonical', ['node' => $entity->entity_id->target_id]);
    // Paragraph entity id.
    $row['paragraph_id'] = $entity->paragraph_id->value;
    // Question text.
    $row['question'] = $entity->question->value;
    // Answer text.
    $row['answer'] = $entity->answer->value;
    // User who submitted the answer.
    $row['uid'] = Link::createFromRoute(
      $entity->uid->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->uid->target_id]
    );
    // Answer created time.
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'short');
    // Answer modified time.
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');
    return $row + parent::buildRow($entity);
  }

}
