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
    $header['name'] = $this->t('Name');
    $header['system'] = $this->t('System');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\rpgc\Entity\RPGCEntity $entity */
    if ($entity->get('user_id')->getString() == \Drupal::currentUser()->id()) {
      $row['name'] = Link::createFromRoute(
        $entity->label(),
        'entity.rpgc_entity.edit_form',
        ['rpgc_entity' => $entity->id()]
      );
      $row['system'] = $entity->type->entity->label();

      return $row + parent::buildRow($entity);
    }
    return NULL;
  }

}
