<?php

/**
 * @file
 * Contains \Drupal\wkbe_page_properties\Entity\PagePropertiesEntity.
 */

namespace Drupal\wkbe_page_properties\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Page properties entities.
 */
class PagePropertiesEntityViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['page_properties_entity']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Page properties'),
      'help' => $this->t('The Page properties ID.'),
    );

    return $data;
  }

}
