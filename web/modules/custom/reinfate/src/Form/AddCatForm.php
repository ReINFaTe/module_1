<?php

namespace Drupal\reinfate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for submitting cats.
 */
class AddCatForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'reinfate_addCatForm';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    $form['cat_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Cat name"),
      '#title_display' => 'after',
      '#description' => $this->t("should be in the range of 2 and 32 symbols"),
      '#placeholder' => $this->t("Your cat's name"),
      '#required' => TRUE,
      '#pattern' => '^(?!\s*$)[0-9A-Za-zА-Яа-яіІїЇ`\' ]{2,32}$',
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add cat"),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cat_name = $form_state->getValue('cat_name');
    if (strlen($cat_name) < 2 || strlen($cat_name) > 32) {
      $form_state->setErrorByName('cat_name',
        $this->t("Cat name should be in the range of 2 and 32 symbols")
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement submitForm() method.
  }

}
