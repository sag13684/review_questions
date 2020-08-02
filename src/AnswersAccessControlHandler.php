<?php

namespace Drupal\review_questions;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Answers entity.
 *
 * @see \Drupal\review_questions\Entity\Answers.
 */
class AnswersAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\review_questions\Entity\AnswersInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view answers entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit answers entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete answers entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add answers entities');
  }

}
