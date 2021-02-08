<?php

namespace Drupal\rpgc\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RPGCEntityTypeForm.
 */
class RPGCEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $rpgc_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $rpgc_entity_type->label(),
      '#description' => $this->t("Label for the RPGC Entity type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rpgc_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\rpgc\Entity\RPGCEntityType::load',
      ],
      '#disabled' => !$rpgc_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $rpgc_entity_type = $this->entity;
    $status = $rpgc_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label RPGC Entity type.', [
          '%label' => $rpgc_entity_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label RPGC Entity type.', [
          '%label' => $rpgc_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($rpgc_entity_type->toUrl('collection'));
  }

}
