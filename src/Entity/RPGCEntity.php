<?php

namespace Drupal\rpgc\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the RPGC Entity entity.
 *
 * @ingroup rpgc
 *
 * @ContentEntityType(
 *   id = "rpgc_entity",
 *   label = @Translation("RPGC Entity"),
 *   bundle_label = @Translation("RPGC Entity type"),
 *   handlers = {
 *     "storage" = "Drupal\rpgc\RPGCEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\rpgc\RPGCEntityListBuilder",
 *     "views_data" = "Drupal\rpgc\Entity\RPGCEntityViewsData",
 *     "translation" = "Drupal\rpgc\RPGCEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\rpgc\Form\RPGCEntityForm",
 *       "add" = "Drupal\rpgc\Form\RPGCEntityForm",
 *       "edit" = "Drupal\rpgc\Form\RPGCEntityForm",
 *       "delete" = "Drupal\rpgc\Form\RPGCEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\rpgc\RPGCEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\rpgc\RPGCEntityAccessControlHandler",
 *   },
 *   base_table = "rpgc_entity",
 *   data_table = "rpgc_entity_field_data",
 *   revision_table = "rpgc_entity_revision",
 *   revision_data_table = "rpgc_entity_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer rpgc entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}",
 *     "add-page" = "/admin/structure/rpgc/rpgc_entity/add",
 *     "add-form" = "/rpgc/add/{rpgc_entity_type}",
 *     "edit-form" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/edit",
 *     "delete-form" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/delete",
 *     "version-history" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/revisions",
 *     "revision" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/revisions/{rpgc_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/revisions/{rpgc_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/revisions/{rpgc_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/rpgc/rpgc_entity/{rpgc_entity}/revisions/{rpgc_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/rpgc/rpgc_entity",
 *   },
 *   bundle_entity_type = "rpgc_entity_type",
 *   field_ui_base_route = "entity.rpgc_entity_type.edit_form"
 * )
 */
class RPGCEntity extends EditorialContentEntityBase implements RPGCEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the rpgc_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the RPGC Entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the character.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['sex'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Sex'))
      ->setDescription(t('The sex of the character'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
        'settings' => [
          'placeholder' => 'Male/Female',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['race'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Race'))
      ->setDescription(t('The race/species of the character'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
        'setting' => [
          'placeholder' => 'Human/Elf/Orc/Centaur...',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['class'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Class'))
      ->setDescription(t('The chosen occupation of the character'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
        'settings' => [
          'placeholder' => 'Fighter/Cleric/Professor/Dilletante/Space Pirate...',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['history'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('History'))
      ->setDescription(t("Character's backstory, relevant notes etc"))
      ->setDefaultValue('')
      ->setRequired(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'basic_string',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 5,
        'settings' => ['rows' => 4],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the RPGC Entity is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 20,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Spit out text function.
   */
  public function spitOutText() {
    return 'spat out text';
  }

}
