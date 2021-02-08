<?php

namespace Drupal\rpgc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class RpgcSystemPickerController.
 */
class RpgcSystemPickerController extends ControllerBase {

  /**
   * Drupal\Core\DependencyInjection\ContainerBuilder definition.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  protected $serviceContainer;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $bundlesInfo = $container->get('entity_type.bundle.info');
    return new static(
      $bundlesInfo,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($bundlesInfo, ModuleHandlerInterface $module_handler) {
    $this->bundlesInfo = $bundlesInfo;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Selector.
   *
   * @return array
   *   Return render array of list of links to RPGC bundles.
   */
  public function selector() {
    $bundles = $this->bundlesInfo->getBundleInfo('rpgc_entity');
    $links = [];
    foreach ($bundles as $key => $value) {
      if ($this->moduleHandler->moduleExists($key)) {
        $link = Link::createFromRoute($value['label'], 'entity.rpgc_entity.add_form', ['rpgc_entity_type' => $key]);
        $thislink = $link->toRenderable();
        $links[$key] = $thislink;
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $links,
    ];
  }

}
