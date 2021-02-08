<?php

namespace Drupal\rpgc\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a RPGC Entity revision.
 *
 * @ingroup rpgc
 */
class RPGCEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The RPGC Entity revision.
   *
   * @var \Drupal\rpgc\Entity\RPGCEntityInterface
   */
  protected $revision;

  /**
   * The RPGC Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $rPGCEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->rPGCEntityStorage = $container->get('entity_type.manager')->getStorage('rpgc_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rpgc_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.rpgc_entity.version_history', ['rpgc_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $rpgc_entity_revision = NULL) {
    $this->revision = $this->RPGCEntityStorage->loadRevision($rpgc_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->RPGCEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('RPGC Entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of RPGC Entity %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.rpgc_entity.canonical',
       ['rpgc_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {rpgc_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.rpgc_entity.version_history',
         ['rpgc_entity' => $this->revision->id()]
      );
    }
  }

}
