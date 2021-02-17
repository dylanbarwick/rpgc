<?php

namespace Drupal\rpgc;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal;

/**
 * Provides dynamic permissions for RPGC Entity of different types.
 *
 * @ingroup rpgc
 */
class RPGCEntityPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The RPGCEntity by bundle permissions.
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function generatePermissions() {
    $perms = [];

    $bundles = Drupal::service('entity_type.bundle.info')->getBundleInfo('rpgc_entity');
    foreach ($bundles as $key => $value) {
      $value['bundle_type'] = $key;
      $perms += $this->buildBundlePermissions($value);
    }

    return $perms;
  }

  /**
   * Returns a list of node permissions for a given node type.
   *
   * @param array $bundle_info
   *   The id and label of a sub module entity.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildBundlePermissions(array $bundle_info) {
    $type_id = $bundle_info['bundle_type'];
    $type_params = ['%type_name' => $bundle_info['label']];

    return [
      "$type_id create entities" => [
        'title' => $this->t('Create new %type_name entities', $type_params),
      ],
      "$type_id edit own entities" => [
        'title' => $this->t('Edit own %type_name entities', $type_params),
      ],
      "$type_id edit any entities" => [
        'title' => $this->t('Edit any %type_name entities', $type_params),
      ],
      "$type_id delete own entities" => [
        'title' => $this->t('Delete own %type_name entities', $type_params),
      ],
      "$type_id delete any entities" => [
        'title' => $this->t('Delete any %type_name entities', $type_params),
      ],
      "$type_id view revisions" => [
        'title' => $this->t('View %type_name revisions', $type_params),
        'description' => t('To view a revision, you also need permission to view the entity item.'),
      ],
      "$type_id revert revisions" => [
        'title' => $this->t('Revert %type_name revisions', $type_params),
        'description' => t('To revert a revision, you also need permission to edit the entity item.'),
      ],
      "$type_id delete revisions" => [
        'title' => $this->t('Delete %type_name revisions', $type_params),
        'description' => $this->t('To delete a revision, you also need permission to delete the entity item.'),
      ],
    ];
  }

}
