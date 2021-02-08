<?php

namespace Drupal\rpgc;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\rpgc\Entity\RPGCEntityInterface;

/**
 * Defines the storage handler class for RPGC Entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * RPGC Entity entities.
 *
 * @ingroup rpgc
 */
class RPGCEntityStorage extends SqlContentEntityStorage implements RPGCEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(RPGCEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {rpgc_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {rpgc_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(RPGCEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {rpgc_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('rpgc_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
