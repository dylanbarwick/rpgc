<?php

namespace Drupal\rpgc\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the RPGC Entity type entity.
 *
 * @ConfigEntityType(
 *   id = "rpgc_entity_type",
 *   label = @Translation("RPGC Entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\rpgc\RPGCEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\rpgc\Form\RPGCEntityTypeForm",
 *       "edit" = "Drupal\rpgc\Form\RPGCEntityTypeForm",
 *       "delete" = "Drupal\rpgc\Form\RPGCEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\rpgc\RPGCEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "rpgc_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "rpgc_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/rpgc/rpgc_entity_type/{rpgc_entity_type}",
 *     "add-form" = "/admin/structure/rpgc/rpgc_entity_type/add",
 *     "edit-form" = "/admin/structure/rpgc/rpgc_entity_type/{rpgc_entity_type}/edit",
 *     "delete-form" = "/admin/structure/rpgc/rpgc_entity_type/{rpgc_entity_type}/delete",
 *     "collection" = "/admin/structure/rpgc/rpgc_entity_type"
 *   }
 * )
 */
class RPGCEntityType extends ConfigEntityBundleBase implements RPGCEntityTypeInterface {

  /**
   * The RPGC Entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The RPGC Entity type label.
   *
   * @var string
   */
  protected $label;

}
