<?php

namespace Drupal\rpgc\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining RPGC Entity entities.
 *
 * @ingroup rpgc
 */
interface RPGCEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the RPGC Entity name.
   *
   * @return string
   *   Name of the RPGC Entity.
   */
  public function getName();

  /**
   * Sets the RPGC Entity name.
   *
   * @param string $name
   *   The RPGC Entity name.
   *
   * @return \Drupal\rpgc\Entity\RPGCEntityInterface
   *   The called RPGC Entity entity.
   */
  public function setName($name);

  /**
   * Gets the RPGC Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the RPGC Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the RPGC Entity creation timestamp.
   *
   * @param int $timestamp
   *   The RPGC Entity creation timestamp.
   *
   * @return \Drupal\rpgc\Entity\RPGCEntityInterface
   *   The called RPGC Entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the RPGC Entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the RPGC Entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\rpgc\Entity\RPGCEntityInterface
   *   The called RPGC Entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the RPGC Entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the RPGC Entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\rpgc\Entity\RPGCEntityInterface
   *   The called RPGC Entity entity.
   */
  public function setRevisionUserId($uid);

}
