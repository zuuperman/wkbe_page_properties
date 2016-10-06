<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\Entity\PagePropertiesEntity.
 */

namespace Drupal\wkbe_page_properties\Entity;

use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\wkbe_page_properties\PagePropertiesEntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Page properties entity.
 *
 * @ingroup wkbe_page_properties
 *
 * @ContentEntityType(
 *   id = "page_properties_entity",
 *   label = @Translation("Page property"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wkbe_page_properties\PagePropertiesEntityListBuilder",
 *     "views_data" = "Drupal\wkbe_page_properties\Entity\PagePropertiesEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\wkbe_page_properties\Form\PagePropertiesEntityForm",
 *       "add" = "Drupal\wkbe_page_properties\Form\PagePropertiesEntityForm",
 *       "edit" = "Drupal\wkbe_page_properties\Form\PagePropertiesEntityForm",
 *       "delete" = "Drupal\wkbe_page_properties\Form\PagePropertiesEntityDeleteForm",
 *     },
 *     "access" = "Drupal\wkbe_page_properties\PagePropertiesEntityAccessControlHandler",
 *   },
 *   base_table = "page_properties_entity",
 *   data_table = "page_properties_entity_field_data",
 *   revision_table = "page_properties_entity_revision",
 *   revision_data_table = "page_properties_entity_revision_field_data",
 *   admin_permission = "administer PagePropertiesEntity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   translatable = TRUE,
 *   links = {
 *     "canonical" = "/admin/page_properties_entity/{page_properties_entity}/edit",
 *     "edit-form" = "/admin/page_properties_entity/{page_properties_entity}/edit",
 *     "delete-form" = "/admin/page_properties_entity/{page_properties_entity}/delete",
 *     "drupal:content-translation-overview" = "/admin/page_properties_entity/{page_properties_entity}/translate"
 *   },
 *   field_ui_base_route = "page_properties_entity.settings"
 * )
 */
class PagePropertiesEntity extends ContentEntityBase implements PagePropertiesEntityInterface {
  use EntityChangedTrait;

  /**
   * @var \Drupal\Core\Condition\ConditionPluginCollection
   * The condition plugin collection.
   */
  protected $visibilityConditions;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibility() {
    return $this->get('visibility')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVisibility(array $visibility) {

    $this->set('visibility', serialize($visibility));

    $conditions = $this->getVisibilityConditions();
    $conditions->setConfiguration($visibility);

    return $this;

  }

  /**
   * {@inheritdoc}
   */
  public function getVisibilityConditions() {

    if ($this->visibilityConditions === NULL) {
      $conditionPluginManager = \Drupal::service('plugin.manager.condition');
      $visibility = $this->getVisibility() ? unserialize($this->getVisibility()) : [];
      $this->visibilityConditions = new ConditionPluginCollection($conditionPluginManager, $visibility);
    }

    return $this->visibilityConditions;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Page properties entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Page properties entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Page properties entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Page properties entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setTranslatable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Page properties is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Page properties entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['visibility'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Visibility'))
      ->setDescription(t('The visibility settings.'))
      ->setSetting('case_sensitive', TRUE);

    return $fields;
  }

}
