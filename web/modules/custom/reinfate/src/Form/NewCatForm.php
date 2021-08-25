<?php

namespace Drupal\reinfate\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for submitting cats.
 */
class NewCatForm extends FormBase {

  /**
   * Drupal\Core\Messenger\Messenger definition.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Drupal\Core\StringTranslation\TranslationManager definition.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $t;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): NewCatForm {
    $instance = parent::create($container);
    $instance->t = $container->get('string_translation');
    $instance->messenger = $container->get('messenger');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'reinfate_NewCatForm';
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
      // '#pattern' => '^(?!\s*$)[0-9A-Za-zА-Яа-яіІїЇ`\' ]{2,32}$',
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add cat"),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::submitAjax',
      ],
    ];
    $form['messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'reinfate-NewCatForm-messages form-messages',
      ],
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
    $this->messenger->addMessage($this->t("Cat submitted"));
  }

  /**
   * Ajax submitting.
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getErrors()) {
      foreach ($form_state->getErrors() as $err) {
        $response->addCommand(new MessageCommand($err, '.reinfate-NewCatForm-messages', ['type' => 'error'], FALSE));
      }
      $form_state->clearErrors();
    }
    else {
      $response->addCommand(new MessageCommand('Your cat submitted.', '.reinfate-NewCatForm-messages'));
    }
    $this->messenger->deleteAll();

    return $response;
  }

}
