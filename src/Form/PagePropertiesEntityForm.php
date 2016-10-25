<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\Form\PagePropertiesEntityForm.
 */

namespace Drupal\wkbe_page_properties\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;

/**
 * Form controller for Page properties edit forms.
 *
 * @ingroup wkbe_page_properties
 */
class PagePropertiesEntityForm extends ContentEntityForm {

  use ContextAwarePluginAssignmentTrait;

  /**
   * @var \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   * The ConditionManager for building the visibility UI.
   */
  protected $condition_manager;

  protected $contextRepository;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * The language manager.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    parent::__construct($entity_manager);
    $container =  \Drupal::getContainer();
    $this->condition_manager = $container->get('plugin.manager.condition');
    $this->language_manager = $container->get('language_manager');
    $this->contextRepository = $container->get('context.repository');
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $form['name']['#disabled'] = !\Drupal::currentUser()->hasPermission('administer page properties entities');
    $form['field_pages']['#access'] = \Drupal::currentUser()->hasPermission('administer page properties entities');

    $form_state->setTemporaryValue('gathered_contexts', $this->contextRepository->getAvailableContexts());

    $this->buildVisibilityInterface($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    /* @var $entity \Drupal\wkbe_page_properties\Entity\PagePropertiesEntity */
    $entity = $this->entity;

    // Submit visibility condition settings.
    $visibility = [];
    foreach ($form_state->getValue('visibility') as $condition_id => $values) {

      // When no conditions are set, don't save it.
      $condition_values = $values;
      $condition_values = array_filter(array_shift($condition_values));
      if (empty($condition_values)) {
        continue;
      }

      // Allow the condition to submit the form.
      $condition = $form_state->get(['conditions', $condition_id]);
      $condition_values = (new FormState())
        ->setValues($values);
      $condition->submitConfigurationForm($form, $condition_values);
      if ($condition instanceof ContextAwarePluginInterface) {
        $context_mapping = isset($values['context_mapping']) ? $values['context_mapping'] : [];
        $condition->setContextMapping($context_mapping);
      }
      // Update the original form values.
      $condition_configuration = $condition->getConfiguration();
      $form_state->setValue(['visibility', $condition_id], $condition_configuration);
      // Update the visibility conditions on the block.

      $visibility[$condition_id] = $condition_configuration;

    }

    $entity->setVisibility($visibility);

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Page properties.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Page properties.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.page_properties_entity.edit_form', ['page_properties_entity' => $entity->id()]);
  }

  /**
   * Helper function for building the visibility UI form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form array with the visibility UI added in.
   */
  protected function buildVisibilityInterface(array &$form, FormStateInterface $form_state) {

    $form['visibility'] = [
      '#tree' => TRUE,
      '#access' => \Drupal::currentUser()->hasPermission('edit page properties visibility settings'),
      '#weight' => 20,
    ];

    $form['visibility']['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
      '#attached' => [
        'library' => [
          'block/drupal.block',
        ],
      ],
    ];

    /* @var $entity \Drupal\wkbe_page_properties\Entity\PagePropertiesEntity */
    $visibility = $this->entity->getVisibilityConditions();
    $definitions = $this->condition_manager->getDefinitions();
    foreach ($definitions as $condition_id => $definition) {

      /**
       * Exclude conditions that we don't want on our form:
       * - current theme condition, languages are done via translation.
       * - language condition
       * - request_path, pages are done via separate field, so we can query directly for better performance. (Otherwise we need to load all entities of current language)
       **/
      if ($condition_id == 'current_theme' || $condition_id == 'language' || $condition_id == 'request_path') {
        continue;
      }

      /** @var \Drupal\Core\Condition\ConditionInterface $condition */
      $configuration = [];
      if ($visibility->has($condition_id)) {
        $configuration = $visibility->get($condition_id)->getConfiguration();
      }
      $condition = $this->condition_manager->createInstance($condition_id, $configuration);
      $form_state->set(['conditions', $condition_id], $condition);
      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'visibility_tabs';
      $form['visibility'][$condition_id] = $condition_form;
    }

    if (isset($form['visibility']['node_type'])) {
      $form['visibility']['node_type']['#title'] = $this->t('Content types');
      $form['visibility']['node_type']['bundles']['#title'] = $this->t('Content types');
      $form['visibility']['node_type']['negate']['#type'] = 'value';
      $form['visibility']['node_type']['negate']['#title_display'] = 'invisible';
      $form['visibility']['node_type']['negate']['#value'] = $form['visibility']['node_type']['negate']['#default_value'];
    }
    if (isset($form['visibility']['user_role'])) {
      $form['visibility']['user_role']['#title'] = $this->t('Roles');
      unset($form['visibility']['user_role']['roles']['#description']);
      $form['visibility']['user_role']['negate']['#type'] = 'value';
      $form['visibility']['user_role']['negate']['#value'] = $form['visibility']['user_role']['negate']['#default_value'];
    }
    if (isset($form['visibility']['request_path'])) {
      $form['visibility']['request_path']['#title'] = $this->t('Pages');
      $form['visibility']['request_path']['negate']['#type'] = 'radios';
      $form['visibility']['request_path']['negate']['#default_value'] = (int) $form['request_path']['negate']['#default_value'];
      $form['visibility']['request_path']['negate']['#title_display'] = 'invisible';
      $form['visibility']['request_path']['negate']['#options'] = [
        $this->t('Show for the listed pages'),
        $this->t('Hide for the listed pages'),
      ];
    }
  }

}
