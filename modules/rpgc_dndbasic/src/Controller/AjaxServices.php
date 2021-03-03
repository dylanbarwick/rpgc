<?php

namespace Drupal\rpgc_dndbasic\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\rpgc_dndbasic\Services\RpgcDndbasicCreationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Class AjaxServices.
 */
class AjaxServices extends ControllerBase {

  /**
   * The repository for our specialized queries.
   *
   * @var \Drupal\rpgc_dndbasic\Services\RpgcDndbasicCreationService
   */
  protected $rpgcDndbasicCreationService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static($container->get('rpgc_dndbasic.creation'));
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }

  /**
   * Construct a new controller.
   *
   * @param \Drupal\rpgc_dndbasic\Services\RpgcDndbasicCreationService $rpgc_create
   *   The creation service.
   */
  public function __construct(RpgcDndbasicCreationService $rpgc_create) {
    $this->rpgc_create = $rpgc_create;
  }

  /**
   * Render custom ajax command.
   *
   * @return ajax
   *   Command function.
   */
  public function render() {
    return [
      'command' => 'rollDice',
      'message' => 'My Awesome Message',
    ];
  }

  /**
   * Roll dice and return array of info.
   *
   * @return ajax
   *   Command function.
   */
  public function ajaxRollStat($nojs = 'nojs', $whichstat = NULL) {
    $rpgc_create = $this->rpgc_create;
    $rollo = $rpgc_create->rollStat();
    $whichfield = 'edit-field-' . str_replace('_', '-', $whichstat) . '-0-value';
    $selector = 'input[data-drupal-selector=' . $whichfield . ']';
    $method = 'val';
    $arguments = [$rollo['sum']];
    $output = $whichstat;
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand($selector, $method, $arguments));
    $response->addCommand(new ReplaceCommand('#' . $whichfield . '--description .rolly-readout', $rollo['message']));

    return $response;
  }

  /**
   * Generate name.
   *
   * @return ajax
   *   Command function.
   */
  public function ajaxGenerateName($race = 'human', $malefemale = 'male') {
    $rpgc_create = $this->rpgc_create;
    $params = [
      'firstlast' => ['first'],
      'race' => $race,
      'malefemale' => $malefemale,
      'genre' => ['fantasy'],
      'originator' => ['rpgc_dndbasic'],
    ];
    $name = $rpgc_create->generateName($params);
    $params['firstlast'] = ['last'];
    $name .= ' ' . $rpgc_create->generateName($params);
    $selector = 'input[data-drupal-selector="edit-name-0-value"]';
    $method = 'val';
    $arguments = [$name];
    $output = $whichstat;
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand($selector, $method, $arguments));

    return $response;
  }

}
