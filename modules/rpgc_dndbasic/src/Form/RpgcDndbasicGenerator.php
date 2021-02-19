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
    // Retrieve all GET variables from URL.
    $request = $this->getRequest()->query->all();

    // Set the services object.
    $rpgc_create = $this->rpgcDndbasicCreation;

    // Read the system config.
    $systemconfig = $rpgc_create->getSystemConfig();

    // Remove any invalid request vars that could break the layout.
    $validvars = [];
    $validvargroups = [
      'classes',
      'defaultdicedetails',
      'alignment',
      'sex',
    ];
    foreach ($validvargroups as $vvgkey => $vvgvalue) {
      foreach ($systemconfig[$vvgvalue] as $key => $value) {
        $validvars[] = $key;
      }
    }

    $validvars[] = 'minlevel';
    $validvars[] = 'maxlevel';
    $validvars[] = 'num_npcs';
    // Feed the request variables to makeValid() to remove anything dodgy.
    $this->makeValid($validvars, $request);

    // If there are request variables, ie we're looking at a generated list,
    // close the form elements.
    $open_details = TRUE;
    if (count($request)) {
      $open_details = FALSE;
    }

    $form['classes'] = [
      '#type' => 'details',
      '#title' => $this->t('Character classes'),
      '#description' => $this->t('If none of these is selected the system will choose from all of them.'),
      '#open' => $open_details,
    ];
    foreach ($systemconfig['classes'] as $key => $value) {
      $default_value = 0;
      if (!empty($request[$key])) {
        if ($request[$key] !== 0) {
          $default_value = 1;
        }
      }
      $form['classes'][$key] = [
        '#type' => 'checkbox',
        '#title' => $value['label'],
        '#weight' => '0',
        '#default_value' => $default_value,
      ];
    }

    $form['defaultdicedetails'] = [
      '#type' => 'details',
      '#title' => $this->t('Dice defaults'),
      '#open' => $open_details,
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
      $default = $default < 0 ? 0 : $default;
      $form['defaultdicedetails'][$key] = [
        '#type' => 'number',
        '#title' => $dice_text[$key]['title'],
        '#default_value' => $default,
        '#description' => $dice_text[$key]['description'],
        '#weight' => '0',
        '#min' => 0,
      ];
    }

    $form['misc'] = [
      '#type' => 'details',
      '#title' => $this->t('Miscelaneous'),
      '#open' => $open_details,
    ];

    $form['misc']['align'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Alignment'),
      '#description' => $this->t('If none of these is selected the system will choose from all of them.'),
    ];

    $form['misc']['sex'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sex'),
      '#description' => $this->t('If neither of these is selected the system will choose from both of them.'),
    ];

    $form['misc']['level'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Level'),
      '#description' => $this->t('The maximum level should not be lower than the minimum level. Obvs.'),
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

    $level_options = [];
    for ($i = 1; $i <= 12; $i++) {
      $level_options[$i] = $i;
    }

    $default_value = 1;
    if (!empty($request['minlevel'])) {
      $default_value = $request['minlevel'];
    }
    $form['misc']['level']['minlevel'] = [
      '#type' => 'select',
      '#title' => $this->t('The minimum level for the NPCs'),
      '#options' => $level_options,
      '#default_value' => $default_value,
    ];

    $default_value = 1;
    if (!empty($request['maxlevel'])) {
      $default_value = $request['maxlevel'];
    }
    $form['misc']['level']['maxlevel'] = [
      '#type' => 'select',
      '#title' => $this->t('The maximum level for the NPCs'),
      '#options' => $level_options,
      '#default_value' => $default_value,
    ];

    $default_num_npcs = 1;
    if (!empty($request['num_npcs'])) {
      $default_num_npcs = $request['num_npcs'];
    }
    $form['num_npcs'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of NPCs'),
      '#default_value' => $default_num_npcs,
      '#description' => $this->t('Specify the number of NPCs to generate.'),
      '#weight' => 10,
      '#min' => 1,
      '#max' => 144,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#weight' => 12,
    ];

    if (count($request)) {
      // Request NPC generator function x times.
      for ($i = 0; $i < $default_num_npcs; $i++) {
        if ($npc = $rpgc_create->generatePc($request)) {
          $full_stat = $npc['details']['full_stat'];
          unset($npc['details']);
          $rows[] = $npc;
        }
      }

      // Apologise for not returning as many NPCs as requested.
      if (count($rows) < $default_num_npcs) {
        $caption = $this->t('You requested :num_pcs_req but I can only offer :num_pcs_deliv . Sadly, not everyone made the grade and so the number of peasants, groundlings and assorted rabble in the world has increased.', [':num_pcs_req' => $default_num_npcs, ':num_pcs_deliv' => count($rows)]);
      }
      else {
        $caption = $this->t('Here are the NPCs you requested.');
      }

      $form['generated'] = [
        '#theme' => 'table',
        '#header' => [
          'Name',
          'Class',
          'Sex',
          'Level',
          'HP',
          'Align',
          'STR',
          'INT',
          'WIS',
          'DEX',
          'CON',
          'CHA',
        ],
        '#caption' => $caption,
        '#rows' => $rows,
        '#attributes' => [
          'class' => [
            'npc-table',
          ],
        ],
        '#prefix' => '<hr/>',
        '#weight' => 12,
      ];
    }
    $form['#attached']['library'][] = 'rpgc_dndbasic/rpgc_dndbasic-library';

    return $form;
  }

  /**
   * Helper function to check if GET vars are valid.
   *
   * @param array $validvars
   *   A list of valid variables as specified by the system yml file.
   * @param array $request
   *   All GET variables, passed by ref so any dodgy stuff can be removed here.
   */
  public function makeValid(array $validvars, array &$request) {
    foreach ($request as $key => $value) {
      if (!in_array($key, $validvars)) {
        unset($request[$key]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }

    // Make sure minlevel is <= maxlevel.
    if ($form_state->getValue('minlevel') > $form_state->getValue('maxlevel')) {
      $form_state->setErrorByName('minlevel', $this->t('The minimum level is too high.'));
      $form_state->setErrorByName('maxlevel', $this->t('The maximum level is too low.'));
    }

    // Make sure all number fields are positive numbers.
    $values = $form_state->getValues();
    $positive_only = [
      'dietype',
      'numthrown',
      'numcounted',
      'addition',
    ];
    foreach ($positive_only as $value) {
      if ((int) $values[$value] < 0) {
        $form_state->setErrorByName($value, $this->t('No negative numbers for dice fields.'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Return options as GET variables.
    foreach ($form_state->cleanValues()->getValues() as $key => $value) {
      $returned[$key] = $value;
    }
    $form_state->setRedirect('rpgc_dndbasic.generator', $returned);
  }

}
