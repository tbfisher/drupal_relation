<?php

/**
 * @file
 * Contains \Drupal\relation\Form\RelationDeleteConfirm.
 */

namespace Drupal\relation\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for relation deletion.
 */
class RelationDeleteConfirm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('url_generator')
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
      'route_name' => 'relation.view',
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
