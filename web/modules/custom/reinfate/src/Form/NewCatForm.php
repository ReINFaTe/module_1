<?php

namespace Drupal\reinfate\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ajax_command\Ajax\AjaxCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for submitting cats.
 */
class NewCatForm extends FormBase {

  /**
   * Drupal\Core\Database\ definition.
   *
   * @var \Drupal\Core\Database\
   */
  protected $database;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): NewCatForm {
    $instance = parent::create($container);
    $instance->setStringTranslation($container->get('string_translation'));
    $instance->setMessenger($container->get('messenger'));
    $instance->database = $container->get('database');
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
    $form['cat_picture'] = [
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
    return $form;
  }

  /**
   * Validating for email field.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $regex = '/[^\w_\-@\.]+/';
    $response = new AjaxResponse();
    $form_selector = '.' . mb_strtolower(Html::cleanCssIdentifier($this->getFormId()));
    if (preg_match($regex, $form_state->getValue('email'))) {
      $response->addCommand(new MessageCommand(
        $this->t("Only latin characters and -, _ are allowed"), '.messages-overlay', ['type' => 'error'], TRUE
      ));
      $response->addCommand(new InvokeCommand($form_selector . ' .form-email', 'addClass', ['error']));
    }
    else {
      $response->addCommand(new InvokeCommand($form_selector . ' .form-email', 'removeClass', ['error']));
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
    $file_data = $form_state->getValue(['cat_picture'])[0];
    $file = File::load($file_data);
    $file->setPermanent();
    $file->save();
    $this->database
      ->insert('reinfate')
      ->fields([
        'cat_name' => $form_state->getValue('cat_name'),
        'email' => $form_state->getValue('email'),
        'cat_picture' => $form_state->getValue(['cat_picture'])[0],
        'created' => time(),
      ])
      ->execute();
    $this->messenger->addMessage($this->t("Cat submitted"));
    Cache::invalidateTags(['reinfate_cat_list']);
  }

  /**
   * Ajax submitting.
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $form_state->setRebuild(TRUE);
    $form_selector = '.' . mb_strtolower(Html::cleanCssIdentifier($this->getFormId()));
    $response->addCommand(new ReplaceCommand($form_selector, $form));
    if ($form_state->hasAnyErrors()) {
      foreach ($form_state->getErrors() as $err) {
        $response->addCommand(new MessageCommand($err, '.messages-overlay', ['type' => 'error'], FALSE));
      }
    }
    else {
      $response->addCommand(new MessageCommand('Your cat submitted.', '.messages-overlay'));
      $url = Url::fromRoute("reinfate.catsListAjax",
        ["method" => "ajax"], ['absolute' => FALSE])->toString();
      $response->addCommand(new AjaxCommand($url));
      $formUrl = Url::fromRoute("reinfate.catsFormAjax",
        ["method" => "ajax"], ['absolute' => FALSE])->toString();
      $response->addCommand(new AjaxCommand($formUrl));
    }

    $this->messenger->deleteAll();
    return $response;
  }

}
