<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\PagePropertiesEntityInterface.
 */

namespace Drupal\wkbe_page_properties;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Page properties entities.
 *
 * @ingroup wkbe_page_properties
 */
interface PagePropertiesEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Page properties name.
   *
   * @return string
   *   Name of the Page properties.
   */
  public function getName();

  /**
   * Sets the Page properties name.
   *
   * @param string $name
   *   The Page properties name.
   *
   * @return \Drupal\wkbe_page_properties\PagePropertiesEntityInterface
   *   The called Page properties entity.
   */
  public function setName($name);

  /**
   * Gets the Page properties creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Page properties.
   */
  public function getCreatedTime();

  /**
   * Sets the Page properties creation timestamp.
   *
   * @param int $timestamp
   *   The Page properties creation timestamp.
   *
   * @return \Drupal\wkbe_page_properties\PagePropertiesEntityInterface
   *   The called Page properties entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Page properties published status indicator.
   *
   * Unpublished Page properties are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Page properties is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Page properties.
   *
   * @param bool $published
   *   TRUE to set this Page properties to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\wkbe_page_properties\PagePropertiesEntityInterface
   *   The called Page properties entity.
   */
  public function setPublished($published);

  /**
   * Return the visibility settings for current entity as serialized blob.
   *
   * @return mixed
   */
  public function getVisibility();

  /**
   * Set the visibility settings.
   *
   * @param array $visibility
   *   The visibility settings to set.
   *
   * @return \Drupal\wkbe_page_properties\PagePropertiesEntityInterface
   *   The called Page properties entity.
   */
  public function setVisibility(array $visibility);

  /**
   * Return the visibility conditions for current entity as ConditionPluginCollection.
   *
   * @return Drupal\Core\Condition\ConditionPluginCollection
   */
  public function getVisibilityConditions();

}
