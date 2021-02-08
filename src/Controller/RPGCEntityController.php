<?php

namespace Drupal\rpgc\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\rpgc\Entity\RPGCEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RPGCEntityController.
 *
 *  Returns responses for RPGC Entity routes.
 */
class RPGCEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a RPGC Entity revision.
   *
   * @param int $rpgc_entity_revision
   *   The RPGC Entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($rpgc_entity_revision) {
    $rpgc_entity = $this->entityTypeManager()->getStorage('rpgc_entity')
      ->loadRevision($rpgc_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('rpgc_entity');

    return $view_builder->view($rpgc_entity);
  }

  /**
   * Page title callback for a RPGC Entity revision.
   *
   * @param int $rpgc_entity_revision
   *   The RPGC Entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($rpgc_entity_revision) {
    $rpgc_entity = $this->entityTypeManager()->getStorage('rpgc_entity')
      ->loadRevision($rpgc_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $rpgc_entity->label(),
      '%date' => $this->dateFormatter->format($rpgc_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a RPGC Entity.
   *
   * @param \Drupal\rpgc\Entity\RPGCEntityInterface $rpgc_entity
   *   A RPGC Entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(RPGCEntityInterface $rpgc_entity) {
    $account = $this->currentUser();
    $rpgc_entity_storage = $this->entityTypeManager()->getStorage('rpgc_entity');

    $langcode = $rpgc_entity->language()->getId();
    $langname = $rpgc_entity->language()->getName();
    $languages = $rpgc_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $rpgc_entity->label()]) : $this->t('Revisions for %title', ['%title' => $rpgc_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all rpgc entity revisions") || $account->hasPermission('administer rpgc entity entities')));
    $delete_permission = (($account->hasPermission("delete all rpgc entity revisions") || $account->hasPermission('administer rpgc entity entities')));

    $rows = [];

    $vids = $rpgc_entity_storage->revisionIds($rpgc_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\rpgc\RPGCEntityInterface $revision */
      $revision = $rpgc_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $rpgc_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.rpgc_entity.revision', [
            'rpgc_entity' => $rpgc_entity->id(),
            'rpgc_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $rpgc_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.rpgc_entity.translation_revert', [
                'rpgc_entity' => $rpgc_entity->id(),
                'rpgc_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.rpgc_entity.revision_revert', [
                'rpgc_entity' => $rpgc_entity->id(),
                'rpgc_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.rpgc_entity.revision_delete', [
                'rpgc_entity' => $rpgc_entity->id(),
                'rpgc_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['rpgc_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
