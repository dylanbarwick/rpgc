<?php

/**
 * @file
 * Contains rpgc_entity.page.inc.
 *
 * Page callback for RPGC Entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for RPGC Entity templates.
 *
 * Default template: rpgc_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_rpgc_entity(array &$variables) {
  // Fetch RPGCEntity Entity Object.
  $rpgc_entity = $variables['elements']['#rpgc_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
