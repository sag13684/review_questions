<?php

/**
 * @file
 * Contains answers.page.inc.
 *
 * Page callback for Answers entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Answers templates.
 *
 * Default template: answers.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_answers(array &$variables) {
  // Fetch Answers Entity Object.
  $answers = $variables['elements']['#answers'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
