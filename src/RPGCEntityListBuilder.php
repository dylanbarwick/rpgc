<?php

namespace Drupal\rpgc;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of RPGC Entity entities.
 *
 * @ingroup rpgc
 */
class RPGCEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('RPGC Entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\rpgc\Entity\RPGCEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.rpgc_entity.edit_form',
      ['rpgc_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
