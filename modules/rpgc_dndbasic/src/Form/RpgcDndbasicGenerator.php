<?php

namespace Drupal\rpgc_dndbasic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RpgcDndbasicGenerator.
 */
class RpgcDndbasicGenerator extends FormBase {

  /**
   * Drupal\rpgc_dndbasic\Services\RpgcDndbasicCreationServiceInterface definition.
   *
   * @var \Drupal\rpgc_dndbasic\Services\RpgcDndbasicCreationServiceInterface
   */
  protected $rpgcDndbasicCreation;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->rpgcDndbasicCreation = $container->get('rpgc_dndbasic.creation');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rpgc_dndbasic_generator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // dump($this->getRequest()->request->all());
    // exit;
    // dump($this->rpgcDndbasicCreation->getSystemConfig());
    // dump($form_state->getValues());
    $request = $this->getRequest()->query->all();
    $rpgc_create = $this->rpgcDndbasicCreation;
    $systemconfig = $rpgc_create->getSystemConfig();
    $form['classes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Character classes'),
      '#description' => $this->t('If none of the above is selected the system will choose from all of them.'),
    ];
    foreach ($systemconfig['classes'] as $key => $value) {
      $default_value = 0;
      if (!empty($request[$key])) {
        $default_value = $request[$key];
      }
      $form['classes'][$key] = [
        '#type' => 'checkbox',
        '#title' => $value['label'],
        '#weight' => '0',
        '#default_value' => $default_value,
      ];
    }

    $form['defaultdicedetails'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dice defaults'),
    ];

    $dice_text = [
      'dietype' => [
        'title' => $this->t('Die type for stats'),
        'description' => $this->t('This should be d6 but feel free to change it for crazy results.'),
      ],
      'numthrown' => [
        'title' => $this->t('Number of dice thrown for stats'),
        'description' => $this->t('Usually it would be 4 but if you feel generous then increase to 5, 6 or more. Your choice.'),
      ],
      'numcounted' => [
        'title' => $this->t('The number of dice counted'),
        'description' => $this->t('This should always be 3 unless you are rolling up a bunch of plague victims with scurvy, in which case make it 2.'),
      ],
      'addition' => [
        'title' => $this->t('An arbitrary addition to the sum.'),
        'description' => $this->t('Leave it at 0 unless you want get NPCs with stats of more than 18.'),
      ],
      'minimumhitpoints' => [
        'title' => $this->t('Minimum hitpoints'),
        'description' => $this->t('What it says...'),
      ],
    ];

    foreach ($systemconfig['defaultdicedetails'] as $key => $value) {
      if (!empty($request[$key])) {
        $default = $request[$key];
      }
      else {
        $default = $systemconfig['defaultdicedetails'][$key];
      }
      $form['defaultdicedetails'][$key] = [
        '#type' => 'number',
        '#title' => $dice_text[$key]['title'],
        '#default_value' => $default,
        '#description' => $dice_text[$key]['description'],
        '#weight' => '0',
      ];
    }

    $form['misc'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Miscelaneous'),
    ];

    $form['misc']['align'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Alignment'),
      '#description' => $this->t('If none of the above is selected the system will choose from all of them.'),
    ];

    $form['misc']['sex'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sex'),
      '#description' => $this->t('If neither of the above is selected the system will choose from both of them.'),
    ];

    foreach ($systemconfig['alignment'] as $key => $value) {
      $default_value = 0;
      if (!empty($request[$key])) {
        $default_value = $request[$key];
      }
      $form['misc']['align'][$key] = [
        '#type' => 'checkbox',
        '#title' => $value,
        '#weight' => '0',
        '#default_value' => $default_value,
      ];
    }

    foreach ($systemconfig['sex'] as $key => $value) {
      $default_value = 0;
      if (!empty($request[$key])) {
        $default_value = $request[$key];
      }
      $form['misc']['sex'][$key] = [
        '#type' => 'checkbox',
        '#title' => $value,
        '#weight' => '0',
        '#default_value' => $default_value,
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      // \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format' ? $value['value'] : $value));
      $returned[$key] = $value;
    }
    $form_state->setRedirect('rpgc_dndbasic.generator', $returned);
  }

}
