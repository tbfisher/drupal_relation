<?php

/**
 * @file
 * Contains \Drupal\relation_ui\Form\RelationDeleteConfirm.
 */

namespace Drupal\relation_ui\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for relation deletion.
 */
class RelationDeleteConfirm extends ContentEntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new RelationDeleteConfirm object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete relation @id?', array('@id' => $this->entity->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'relation_ui.view',
      'route_parameters' => array('relation' => $this->entity->id()),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    drupal_set_message(t('Relation @id has been deleted.', array('@id' => $this->entity->id())));
    $form_state['redirect'] = '<front>';
  }

}
