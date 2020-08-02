<?php

namespace Drupal\review_questions\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Answers entities.
 */
class AnswersViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
