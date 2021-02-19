<?php

namespace Drupal\rpgc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\Entity;

/**
 * Class AllMyPcsController.
 */
class AllMyPcsController extends ControllerBase {

  /**
   * List all PCs belonging to the nominated user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user object passed in the parameter.
   *
   * @return array
   *   Return list of PCs as a render array.
   */
  public function listAllPcs(AccountInterface $user = NULL) {
    // dump($user);
    // Get IDs of all rpgc_entity belonging to this user.
    $query = \Drupal::entityQuery('rpgc_entity')
      ->condition('user_id', $user->id());
    $results = $query->execute();
    // dump($results);
    // dump($user->id());
    // dump($query->__toString());
    // exit;

    // Load multiple entities.
    if (count($results)) {
      $entities = Entity::loadMultiple($results);
    }
    dump($entities);

    // Construct table render array (with pager).
    $build = [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: `listAllPcs` with parameter(s): $user'),
    ];
    return $build;
  }

  /**
   * Redirect a user to their own list of characters.
   *
   * This controller assumes that it is only invoked for authenticated users.
   * This is enforced for the 'user.page' route with the '_user_is_logged_in'
   * requirement.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the profile of the currently logged in user.
   */
  public function redirectToListAllPcs() {
    return $this->redirect('rpgc.all_pcs_list', ['user' => $this->currentUser()->id()]);
  }

}
