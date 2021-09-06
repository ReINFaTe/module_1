<?php

namespace Drupal\reinfate\Form;

use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Form for edditing cats.
 */
class EditCatForm extends NewCatForm {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'reinfate_EditCatForm';
  }

  /**
   * Cat to edit if any.
   *
   * @var object
   */
  protected $cat;

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    $result = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_name', 'email', 'cat_picture', 'created'])
      ->condition('id', $id)
      ->execute();
    $cat = $result->fetch();
    $this->cat = $cat;
    $form = parent::buildForm($form, $form_state);
    $form['#submit'] = ["::editSubmitForm"];
    $form['cat_name']['#default_value'] = $cat->cat_name;
    $form['email']['#default_value'] = $cat->email;
    $form['cat_picture']['#default_value'][] = $cat->cat_picture;
    $form['submit']['#value'] = $this->t('Edit cat');
    return $form;
  }

  /**
   * Submit edit version of the cat.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $updated = [
      'cat_name' => $form_state->getValue('cat_name'),
      'email' => $form_state->getValue('email'),
      'cat_picture' => $form_state->getValue('cat_picture')[0],
    ];
    $this->database
      ->update('reinfate')
      ->condition('id', $this->cat->id)
      ->fields($updated)
      ->execute();
    if ($updated['cat_picture'] != $this->cat->cat_picture) {
      File::load($this->cat->cat_picture)->delete();
    }
  }

  /**
   * Ajax submitting.
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = parent::submitAjax($form, $form_state);
    if (!$form_state->hasAnyErrors()) {
      $response->addCommand(new CloseModalDialogCommand());
    }
    return $response;
  }

}
