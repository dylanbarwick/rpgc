<?php

namespace Drupal\rpgc_dndbasic\Services;

/**
 * Interface RpgcDndbasicCreationServiceInterface.
 */
interface RpgcDndbasicCreationServiceInterface {

  /**
   * Get system config from system yaml file.
   */
  public function getSystemConfig();

  /**
   * Roll a stat according to the default dice settings.
   *
   * @param array $defaultdice
   *   The default dice settings.
   *
   * @return array
   *   The results of rollDice() along with the description addendum.
   */
  public function rollStat(array $defaultdice);

  /**
   * Disqualify unsuitable classes.
   *
   * @param array $classes
   *   The list of character classes taken from the system yaml file.
   * @param array $stats
   *   The stats rolled up previously.
   */
  public function disqualifyClasses(array &$classes, array $stats);

  /**
   * Get and sort a list of stats 13 or above.
   */
  public function getPrimeReqs($stats);

  /**
   * Assign weights to classes according to prime requisites and stats.
   *
   * @param array $classes
   *   The remaining classes after having been disqualified previously.
   * @param array $primereqs
   *   All rolled stats that have scored 13 or above.
   */
  public function assignWeightsToClasses(array &$classes, array $primereqs);

  /**
   * Populate a new array of classes according to weight.
   *
   * The classes are sorted by weight so we work our way through and only
   * select the classes that are the heaviest (or one below that) and are
   * not 0.
   *
   * @param array $classes
   *   The classes array with weights.
   *
   * @return array
   *   The shortlist of contenders.
   */
  public function narrowDownTheContenders(array $classes);

  /**
   * Get the class description addendum made up of contenders.
   *
   * @param array $contenders
   *   The list of contenders.
   *
   * @return string
   *   The class description addendum.
   */
  public function getClassDescriptionAddendum(array $contenders);

  /**
   * Roll hit points.
   *
   * @param array $chosen_class
   *   Full details for the chosen character class.
   * @param int $con
   *   The constitution stat score.
   * @param array $defaultdice
   *   The default dice details from the system yaml file.
   *
   * @return array
   *   A rollDice() result called $hitpoints.
   */
  public function hitPoints(array $chosen_class, int $con, array $defaultdice);

  /**
   * Generate an NPC.
   *
   * @param array $params
   *   The parameters set by the generator config form.
   *
   * @return array
   *   An array formatted to suit a table-shaped render array.
   */
  public function generatePc(array $params);

}
