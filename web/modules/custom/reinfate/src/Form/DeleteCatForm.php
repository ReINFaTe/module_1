<?php

namespace Drupal\reinfate\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for deletting cats.
 */
class DeleteCatForm extends ConfirmFormBase {

  /**
   * Drupal\Core\Messenger\Messenger definition.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Drupal\Core\Database\ definition.
   *
   * @var \Drupal\Core\Database\
   */
  protected $database;

  /**
   * The id of the cat to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instanse = parent::create($container);
    $instanse->database = $container->get('database');
    $instanse->messenger = $container->get('messenger');
    return $instanse;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion() {
    return 'Are you sure?';
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return new Url('reinfate.cats');
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'reinfate_delete_cat_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cat = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_picture'])
      ->condition('id', $this->id)
      ->execute()
      ->fetch();
    File::load($cat->cat_picture)->delete();
    $this->database->delete('reinfate')
      ->condition('id', $this->id)
      ->execute();
    $this->messenger->addStatus('You successfully deleted cat record');
    $form_state->setRedirect('reinfate.cats');
  }

}
