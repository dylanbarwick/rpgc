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
    $header['race'] = $this->t('Race');
    $header['class'] = $this->t('Class/Profession');
    $header['created'] = $this->t('Created');
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
      $row['race'] = $entity->get('race')->getString();
      $row['class'] = $entity->get('class')->getString();
      $created = $entity->get('created')->getString();
      $row['created'] = \Drupal::service('date.formatter')->format($created);

      return $row + parent::buildRow($entity);
    }
    return NULL;
  }

}
