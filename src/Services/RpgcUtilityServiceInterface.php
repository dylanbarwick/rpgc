<?php

namespace Drupal\rpgc\Services;

/**
 * Interface RpgcUtilityServiceInterface.
 */
interface RpgcUtilityServiceInterface {

  /**
   * Returns the content of one specified yaml file or a list of system names.
   *
   * @param string $whichModule
   *   The machine name of the module calling the service.
   *
   * @return array
   *   The contents of the yaml system file as a php array or an error message.
   */
  public function readMyYaml($whichModule = NULL);

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
   * @return int
   *   The result.
   */
  public function rollDice($dietype = 6, $numthrown = 1, $numcounted = 1, $addition = 0);

  /**
   * Helper function to retrieve path for module and the formats directory.
   *
   * @return array
   *   Two element array - module_path and system_location.
   */
  public function getPaths();

  /**
   * Helper function to retrieve the contents of a specific file.
   *
   * @param string $whichSystem
   *   The machine name of the system in question.
   *
   * @return array
   *   The contents of the yaml file parsed into a php array.
   */
  public function getSystem($whichSystem = NULL);

}
