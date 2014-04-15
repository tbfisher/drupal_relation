<?php

/**
 * @file
 * Contains \Drupal\relation\Form\RelationTypeDeleteConfirm.
 */

namespace Drupal\relation\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for relation type deletion.
 */
class RelationTypeDeleteConfirm extends EntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new RelationTypeDeleteConfirm object.
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
    return t('Are you sure you want to delete the relation type %type?', array('%type' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'relation.list',
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
  public function buildForm(array $form, array &$form_state) {
    $num_relations = $this->database->query("SELECT COUNT(*) FROM {relation} WHERE relation_type = :type", array(':type' => $this->entity->id()))->fetchField();
    if ($num_relations) {
      $form['#title'] = $this->getQuestion();
      $caption = '<p>' . t('%type is used by @count relations on your site. You may not remove %type until you have removed all existing relations.', array('@count' => $num_relations, '%type' => $this->entity->label())) . '</p>';
      $form['description'] = array('#markup' => $caption);
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The relation type %name has been deleted.', $t_args));
    watchdog('relation', 'Deleted relation type %name.', $t_args, WATCHDOG_NOTICE);
    $form_state['redirect_route']['route_name'] = 'relation.type_list';
  }

}
