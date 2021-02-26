<?php

namespace Drupal\rpgc_dndbasic\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\rpgc\Services\RpgcUtilityServiceInterface;

/**
 * Class AjaxServices.
 */
class AjaxServices implements CommandInterface {

  /**
   * Drupal\rpgc\Services\RpgcUtilityServiceInterface definition.
   *
   * @var \Drupal\rpgc\Services\RpgcUtilityServiceInterface
   */
  protected $rpgcUtility;

  /**
   * Constructs a new RpgcDndbasicCreationService object.
   */
  public function __construct(RpgcUtilityServiceInterface $rpgc_utility) {
    $this->rpgcUtility = $rpgc_utility;
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
   * @param array $parameters
   *   Values for dicetype, numthrown etc.
   *
   * @return ajax
   *   Command function.
   */
  public function ajaxRollStat(array $parameters) {
    $return = $this->rpgcUtility->rollStat($parameters);
    return [
      'command' => 'rollStat',
      'message' => $return,
    ];
  }

}
