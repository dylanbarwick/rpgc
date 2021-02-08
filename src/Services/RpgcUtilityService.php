<?php

namespace Drupal\rpgc\Services;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Serialization\SerializationInterface;

/**
 * Class RpgcUtilityService.
 */
class RpgcUtilityService implements RpgcUtilityServiceInterface {

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializationYaml;

  /**
   * Constructs a new RpgcUtilityService object.
   */
  public function __construct(ModuleHandlerInterface $module_handler, SerializationInterface $serialization_yaml) {
    $this->moduleHandler = $module_handler;
    $this->serializationYaml = $serialization_yaml;
  }

  /**
   * Returns the content of one specified yaml file or a list of system names.
   *
   * @param string $whichModule
   *   The machine name of the module calling the service.
   *
   * @return array
   *   The contents of the yaml system file as a php array or an error message.
   */
  public function readMyYaml($whichModule = NULL) {
    // Get the module path.
    $path = $this->getPaths($whichModule);
    $this_system = [];

    // If this is a query for a specific system.
    if ($whichModule) {
      // Pull out info from the `config/system` directory.
      if ($handle = opendir($path['system_location'])) {

        while (FALSE !== ($entry = readdir($handle))) {
          if ($entry != "." && $entry != "..") {
            // Split the file name by `.` and check the extension.
            $filename = explode('.', $entry);
            if ($filename[1] == 'yml') {
              // $systemname = explode('--', $filename[0]);
              if ($this_system = $this->getSystem($whichModule)) {
                break;
              }
            }
          }
        }
        closedir($handle);
        return $this_system;
      }
    }
    else {
      return ['error' => 'There is no system info file called `rpgc-system--' . RPGC_DNDBASIC_GAME_SYSTEM . '.yml` in this module.'];
    }
  }

  /**
   * Helper function to generate a random number.
   *
   * @param int $dietype
   *   The type of die to be cast, by default d6.
   * @param int $numthrown
   *   The number of dice to be thrown.
   * @param int $numcounted
   *   The number of dice counted.
   * @param int $addition
   *   The addition (if any).
   *
   * @return array
   *   Dice thrown (sorted), dice selected and sum.
   */
  public function rollDice($dietype = 6, $numthrown = 1, $numcounted = 1, $addition = 0) {
    $dicethrown = [];
    $dicereturned = [];
    $dicediscarded = [];
    $numtopop = $numthrown - $numcounted;
    $numtopop < 0 ? $numtopop = 0 : $numtopop;
    for ($i = 0; $i < $numthrown; $i++) {
      $dicethrown[] = rand(1, $dietype);
    }

    rsort($dicethrown);
    $dicereturned['dicethrown'] = $dicethrown;
    for ($i = 0; $i < $numtopop; $i++) {
      $dicediscarded[] = array_pop($dicethrown);
    }
    $dicereturned['dicediscarded'] = $dicediscarded;
    $dicereturned['sum'] = array_sum($dicethrown);

    return $dicereturned;
  }

  /**
   * Helper function to retrieve path for module and the formats directory.
   *
   * @param string $which_module
   *   The machine name of the module in question.
   *
   * @return array
   *   Two element array - module_path and system_location.
   */
  public function getPaths($which_module = NULL) {
    $return = FALSE;
    $module_handler = $this->moduleHandler;
    if ($module_path = $module_handler->getModule($which_module)->getPath()) {
      $return['module_path'] = $module_path;
      $return['system_location'] = $module_path . '/config/system';
    }
    return $return;
  }

  /**
   * Helper function to retrieve the contents of a specific file.
   *
   * @param string $which_module
   *   The machine name of the module in question.
   *
   * @return array
   *   The contents of the yaml file parsed into a php array.
   */
  public function getSystem($which_module = NULL) {
    $return = FALSE;
    // Get the module path.
    $path = $this->getPaths($which_module);
    $filename = $path['system_location'] . '/rpgc-system--' . RPGC_DNDBASIC_GAME_SYSTEM . '.yml';

    // If the file exists.
    if (file_exists($filename)) {
      $return = $this->serializationYaml->decode(file_get_contents($filename));
    }

    return $return;
  }

}
