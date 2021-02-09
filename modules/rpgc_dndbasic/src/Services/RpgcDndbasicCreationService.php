<?php

namespace Drupal\rpgc_dndbasic\Services;

use Drupal\rpgc\Services\RpgcUtilityServiceInterface;

/**
 * Class RpgcDndbasicCreationService.
 */
class RpgcDndbasicCreationService implements RpgcDndbasicCreationServiceInterface {

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
   * Get system config from system yaml file.
   */
  public function getSystemConfig() {
    return $this->rpgcUtility->readMyYaml('rpgc_dndbasic');
  }

  /**
   * Roll a stat according to the default dice settings.
   *
   * @param array $defaultdice
   *   The default dice settings.
   *
   * @return array
   *   The results of rollDice() along with the description addendum.
   */
  public function rollStat(array $defaultdice) {
    $lowdie = [];
    $rollem = $this->rpgcUtility->rollDice($defaultdice['dietype'], $defaultdice['numthrown'], $defaultdice['numcounted']);
    $diff = $defaultdice['numthrown'] - $defaultdice['numcounted'];
    for ($i = 0; $i < $diff; $i++) {
      $lowdie[] = array_pop($rollem['dicethrown']);
    }
    rsort($lowdie);

    $rolled_message = '<br/>[rolled: ';
    $rolled_message .= implode(', ', $rollem['dicethrown']);
    if (count($lowdie) > 0) {
      $rolled_message .= ' (' . implode(', ', $lowdie) . ')]';
    }
    $rollo = $rollem;
    $rollo['message'] = $rolled_message;
    return $rollo;
  }

  /**
   * Disqualify unsuitable classes.
   *
   * @param array $classes
   *   The list of character classes taken from the system yaml file.
   * @param array $stats
   *   The stats rolled up previously.
   */
  public function disqualifyClasses(array &$classes, array $stats) {
    foreach ($classes as $key => $value) {
      if (!empty($value['minimumrequirements'])) {
        foreach ($value['minimumrequirements'] as $mrkey => $mrvalue) {
          // Check if the rolled stats match or exceed this min req.
          if ($stats[$mrkey] < $mrvalue) {
            unset($classes[$key]);
            break;
          }
        }
      }
    }
  }

  /**
   * Get and sort a list of stats 13 or above.
   */
  public function getPrimeReqs($stats) {
    $primereqs = [];
    foreach ($stats as $key => $value) {
      $modifier = $this->statModifiers($value);
      if ($modifier > 0) {
        $primereqs[$key] = [
          'stat' => $value,
          'weight' => $modifier,
        ];
      }
    }
    uasort($primereqs, 'cmp');

    return $primereqs;
  }

  /**
   * Assign weights to classes according to prime requisites and stats.
   *
   * @param array $classes
   *   The remaining classes after having been disqualified previously.
   * @param array $primereqs
   *   All rolled stats that have scored 13 or above.
   */
  public function assignWeightsToClasses(array &$classes, array $primereqs) {
    foreach ($classes as $key => $value) {
      $classes[$key]['weight'] = 0;
      if (!empty($value['primerequisites'])) {
        foreach ($value['primerequisites'] as $prkey => $prvalue) {
          if (!empty($primereqs[$prvalue])) {
            $classes[$key]['weight'] += $primereqs[$prvalue]['weight'];
          }
        }
      }
    }
    uasort($classes, 'cmp');
  }

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
  public function narrowDownTheContenders(array $classes) {
    $contenders = [];
    $weightiest = $classes[array_key_first($classes)]['weight'];
    foreach ($classes as $key => $value) {
      if ($value['weight'] >= ($weightiest - 2) && $value['weight'] > 0) {
        $contenders[$key] = $classes[$key];
        for ($i = 0; $i < $value['weight']; $i++) {
          $contenders['contender_keys'][] = $key;
        }
      }
    }
    return $contenders;
  }

  /**
   * Get the class description addendum made up of contenders.
   *
   * @param array $contenders
   *   The list of contenders.
   *
   * @return string
   *   The class description addendum.
   */
  public function getClassDescriptionAddendum(array $contenders) {
    $desc = '<br/>Potential classes:<br/>';
    if (count($contenders) === 0) {
      $desc .= 'this is a pretty weedy character without enough prime requisites';
    }
    else {
      foreach ($contenders as $key => $value) {
        $desc .= $value['label'] . ' (' . $value['weight'] . ')<br/>';
      }
    }
    return $desc;
  }

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
  public function hitPoints(array $chosen_class, int $con, array $defaultdice) {
    $hitpoints = $this->rpgcUtility->rollDice($chosen_class['hitdice'], 1, 1, $this->statModifiers($con));
    if (!empty($chosen_class['minimumhitpoints'])) {
      $minhp = $chosen_class['minimumhitpoints'];
    }
    else {
      $minhp = $defaultdice['minimumhitpoints'];
    }
    if ($hitpoints['sum'] < $minhp) {
      $hitpoints['sum'] = $minhp;
    }
    return $hitpoints;
  }

  /**
   * Compare function to sort stats by weight.
   */
  // protected function cmp($a, $b) {
  //   if ($a['weight'] == $b['weight']) {
  //     return 0;
  //   }
  //   return ($a['weight'] > $b['weight']) ? -1 : 1;
  // }

  /**
   * Return modifiers based on stats.
   */
  protected function statModifiers($stat) {
    switch ($stat) {
      case 3:
        $return = -3;
        break;

      case 4:
      case 5:
        $return = -2;
        break;

      case 6:
      case 7:
      case 8:
        $return = -1;
        break;

      case 13:
      case 14:
      case 15:
        $return = 1;
        break;

      case 16:
      case 17:
        $return = 2;
        break;

      case 18:
        $return = 3;
        break;

      default:
        $return = 0;
        break;
    }
    return $return;
  }

}
