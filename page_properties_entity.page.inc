<?php

/**
 * @file
 * Contains page_properties_entity.page.inc..
 *
 * Page callback for Page properties entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Page properties templates.
 *
 * Default template: page_properties_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_page_properties_entity(array &$variables) {
  // Fetch PagePropertiesEntity Entity Object.
  $page_properties_entity = $variables['elements']['#page_properties_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
