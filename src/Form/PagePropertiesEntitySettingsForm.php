<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\Form\PagePropertiesEntitySettingsForm.
 */

namespace Drupal\wkbe_page_properties\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PagePropertiesEntitySettingsForm.
 *
 * @package Drupal\wkbe_page_properties\Form
 *
 * @ingroup wkbe_page_properties
 */
class PagePropertiesEntitySettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'PagePropertiesEntity_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Defines the settings form for Page properties entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['PagePropertiesEntity_settings']['#markup'] = 'Settings form for Page properties entities. Manage field settings here.';
    return $form;
  }

}
