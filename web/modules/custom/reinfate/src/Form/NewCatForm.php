<?php

namespace Drupal\reinfate\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
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
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#title_display' => 'after',
      '#description' => $this->t("Only latin characters and -, _"),
      '#placeholder' => $this->t("Your email"),
      '#required' => TRUE,
      '#attributes' => [
        'novalidate' => 'novalidate',
      ],
      '#ajax' => [
        'callback' => '::validateEmail',
        'event' => 'keyup',
        'progress' => [
          'type' => 'none',
          'message' => $this->t('Verifying entry...'),
        ],
      ],
    ];
    $form['cat_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Cat picture'),
      '#title_display' => 'none',
      '#description' => $this->t("Picture of your cat in png, jpg or jpeg format"),
      '#upload_location' => 'public://cat_images',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
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
   * Validating for email field.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    // @todo FIX
    $regex = '/[^\w_\-@\.]+/';
    $response = new AjaxResponse();
    if (preg_match($regex, $form_state->getValue('email'))) {
      $response->addCommand(new MessageCommand(
        $this->t("Only latin characters and -, _ are allowed"), '.reinfate-NewCatForm-messages', ['type' => 'error'], TRUE
      ));
      $response->addCommand(new InvokeCommand('.reinfate-newcatform .form-email', 'addClass', ['error']));
    }
    else {
      $response->addCommand(new InvokeCommand('.reinfate-newcatform  .form-email', 'removeClass', ['error']));
    }
    return $response;
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_data = $form_state->getValue(['cat_image']);
    $file = File::load($file_data[0]);
    $file->setPermanent();
    $file->save();
    $this->messenger->addMessage($this->t("Cat submitted"));
  }

  /**
   * Ajax submitting.
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('.reinfate-newcatform input', 'removeClass', ['error']));
    if ($form_state->getErrors()) {
      foreach ($form_state->getErrors() as $field => $err) {
        $response->addCommand(new MessageCommand($err, '.reinfate-NewCatForm-messages', ['type' => 'error'], FALSE));
        $selector = strtr('.reinfate-newcatform .form-item-@field input', ['@field' => $field]);
        $selector = strtr($selector, ['_' => '-']);
        $response->addCommand(new InvokeCommand($selector, 'addClass', ['error']));
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
