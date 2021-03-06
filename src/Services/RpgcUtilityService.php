<?php

namespace Drupal\rpgc\Services;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Connection;

/**
 * Class RpgcUtilityService.
 */
class RpgcUtilityService implements RpgcUtilityServiceInterface {

  use MessengerTrait;
  use StringTranslationTrait;

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new RpgcUtilityService object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Component\Serialization\SerializationInterface $serialization_yaml
   *   For serialization.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   */
  public function __construct(
      ModuleHandlerInterface $module_handler,
      SerializationInterface $serialization_yaml,
      MessengerInterface $messenger,
      TranslationInterface $translation,
      Connection $connection) {
    $this->moduleHandler = $module_handler;
    $this->serializationYaml = $serialization_yaml;
    $this->messenger = $messenger;
    $this->setStringTranslation($translation);
    $this->connection = $connection;
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
              $systemname = explode('--', $filename[0]);
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
      return ['error' => 'There is no system info file called `rpgc-system--' . $systemname[1] . '.yml` in this module.'];
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
    $module_code = explode('_', $which_module);
    // Get the module path.
    $path = $this->getPaths($which_module);
    $filename = $path['system_location'] . '/rpgc-system--' . $module_code[1] . '.yml';

    // If the file exists.
    if (file_exists($filename)) {
      $return = $this->serializationYaml->decode(file_get_contents($filename));
    }

    return $return;
  }

  /**
   * Helper service to import name taxonomy terms from a yaml file.
   *
   * @param string $whichModule
   *   The machine name of the module to import names from.
   */
  public function importNames($whichModule = NULL) {

    $module_path = drupal_get_path('module', $whichModule);
    $module_code = explode('_', $whichModule);
    if (count($module_code) > 1) {
      $module_code = $module_code[1];
    }
    $termfile = $module_path . '/config/system/rpgc-names--' . $module_code . '.yml';

    // If the file exists.
    if (file_exists($termfile)) {
      $terms = $this->serializationYaml->decode(file_get_contents($termfile));
      $originator = $terms['originator'];
      foreach ($terms['genre'] as $gkey => $gvalue) {
        $genre = $gkey;
        foreach ($gvalue['names']['races'] as $rkey => $rvalue) {
          foreach ($rvalue as $nkey => $nvalue) {
            $thisterm = [
              'vid' => 'rpgc_names',
              'name' => $nvalue['name'],
              'field_rpgcn_genre' => [$gkey],
              'field_rpgcn_originator' => [$originator],
              'field_rpgcn_race' => [$rkey],
            ];
            // Make the values safe.
            if (!empty($nvalue['field_rpgcn_culture'])) {
              $thisterm['field_rpgcn_culture'] = $nvalue['field_rpgcn_culture'];
            }
            if (!empty($nvalue['field_rpgcn_firstlast'])) {
              $thisterm['field_rpgcn_firstlast'] = $nvalue['field_rpgcn_firstlast'];
            }
            if (!empty($nvalue['field_rpgcn_malefemale'])) {
              $thisterm['field_rpgcn_malefemale'] = $nvalue['field_rpgcn_malefemale'];
            }
            $term_to_go = Term::create($thisterm);
            $term_to_go->save();
          }
        }
      }
    }
    else {
      $this->messenger()->addMessage($this->t('Import failed because the file %filename could not be found.', [
        '%filename' => $termfile,
      ]), 'error');
    }
  }

  /**
   * Function to generate names.
   *
   * @param array $params
   *   An array of parameters (all arrays) that act as filters for the search.
   *   $firstlast, $race, $malefemale, $genre, $originator, $culture.
   *
   * @return string
   *   The name.
   */
  public function generateName(array $params) {
    $query = $this->connection->select('taxonomy_term_field_data', 't');
    $query->condition('vid', "rpgc_names");
    $query->addField('t', 'name');
    if (!empty($params['firstlast'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_firstlast', 'trf', 'trf.entity_id = t.tid');
      $query->condition('trf.field_rpgcn_firstlast_value', $params['firstlast'], 'IN');
    }
    if (!empty($params['race'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_race', 'trr', 'trr.entity_id = t.tid');
      $query->condition('trr.field_rpgcn_race_value', $params['race'], 'IN');
    }
    if (!empty($params['malefemale'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_malefemale', 'trm', 'trm.entity_id = t.tid');
      $query->condition('trm.field_rpgcn_malefemale_value', $params['malefemale'], 'IN');
    }
    if (!empty($params['genre'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_genre', 'trg', 'trg.entity_id = t.tid');
      $query->condition('trg.field_rpgcn_genre_value', $params['genre'], 'IN');
    }
    if (!empty($params['originator'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_originator', 'tro', 'tro.entity_id = t.tid');
      $query->condition('tro.field_rpgcn_originator_value', $params['originator'], 'IN');
    }
    if (!empty($params['culture'])) {
      $query->leftJoin('taxonomy_term__field_rpgcn_culture', 'trc', 'trc.entity_id = t.tid');
      $query->condition('trc.field_rpgcn_culture_value', $params['race'], 'IN');
    }

    $query->orderRandom();
    $query->range(0, 1);
    $result = $query->execute()->fetchAll();
    return $result[0]->name;
  }

}
