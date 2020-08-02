<?php

namespace Drupal\review_questions\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Answers entities.
 *
 * @ingroup review_questions
 */
interface AnswersInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Answers name.
   *
   * @return string
   *   Name of the Answers.
   */
  public function getName();

  /**
   * Sets the Answers name.
   *
   * @param string $name
   *   The Answers name.
   *
   * @return \Drupal\review_questions\Entity\AnswersInterface
   *   The called Answers entity.
   */
  public function setName($name);

  /**
   * Gets the Answers creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Answers.
   */
  public function getCreatedTime();

  /**
   * Sets the Answers creation timestamp.
   *
   * @param int $timestamp
   *   The Answers creation timestamp.
   *
   * @return \Drupal\review_questions\Entity\AnswersInterface
   *   The called Answers entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Answers changed timestamp.
   *
   * @return int
   *   Changed timestamp of the Answers.
   */
  public function getChangedTime();

  /**
   * Sets the Answers creation timestamp.
   *
   * @param int $timestamp
   *   The Answers creation timestamp.
   *
   * @return \Drupal\review_questions\Entity\AnswersInterface
   *   The called Answers entity.
   */
  public function setChangedTime($timestamp);

}
