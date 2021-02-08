<?php

namespace Drupal\rpgc;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface RPGCEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of RPGC Entity revision IDs for a specific RPGC Entity.
   *
   * @param \Drupal\rpgc\Entity\RPGCEntityInterface $entity
   *   The RPGC Entity entity.
   *
   * @return int[]
   *   RPGC Entity revision IDs (in ascending order).
   */
  public function revisionIds(RPGCEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as RPGC Entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   RPGC Entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\rpgc\Entity\RPGCEntityInterface $entity
   *   The RPGC Entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(RPGCEntityInterface $entity);

  /**
   * Unsets the language for all RPGC Entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
