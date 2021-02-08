<?php

namespace Drupal\rpgc\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for RPGC Entity entities.
 */
class RPGCEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
