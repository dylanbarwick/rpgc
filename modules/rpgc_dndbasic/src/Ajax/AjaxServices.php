<?php

namespace Drupal\rpgc_dndbasic\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AjaxServices.
 */
class AjaxServices implements CommandInterface {

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
    // $return = $this->rpgcUtility->rollStat($parameters);
    // $return = 'test-roll';
    $output = $whichstat;
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#ajax-example-destination-div', $output));

    // See ajax_example_advanced.inc for more details on the available
    // commands and how to use them.
    // $page = array('#type' => 'ajax', '#commands' => $commands);
    // ajax_deliver($response);
    return $response;
  }

  /**
   * Generate name.
   *
   * @return ajax
   *   Command function.
   */
  public function ajaxGenerateName() {
    $return = $this->rpgcUtility->generateName($parameters);
    return [
      'command' => 'rollStat',
      'message' => $return,
    ];
  }

}
