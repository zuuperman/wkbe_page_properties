<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\PagePropertiesEntityListBuilder.
 */

namespace Drupal\wkbe_page_properties;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Page properties entities.
 *
 * @ingroup wkbe_page_properties
 */
class PagePropertiesEntityListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\wkbe_page_properties\Entity\PagePropertiesEntity */
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.page_properties_entity.edit_form', array(
          'page_properties_entity' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
