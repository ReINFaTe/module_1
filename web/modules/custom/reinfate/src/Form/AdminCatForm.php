<?php

namespace Drupal\reinfate\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for storing oleg pidar.
 */
class AdminCatForm extends ConfirmFormBase {

  /**
   * The submitted data needing to be confirmed.
   *
   * @var array
   */
  protected $data = [];

  /**
   * Drupal\Core\Database\ definition.
   *
   * @var \Drupal\Core\Database\
   */
  protected $database;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instanse = parent::create($container);
    $instanse->setMessenger($container->get('messenger'));
    $instanse->database = $container->get('database');
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
    return new Url('reinfate.adminMenu');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_admin_cats';
  }

  /**
   * Function for creating module.
   */
  public function getCats() : array {
    $result = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_name', 'email', 'cat_picture', 'created'])
      ->orderBy('id', 'DESC')
      ->execute();
    $cats = $result->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($cats as &$cat) {
      $cat['cat_picture'] = [
        'data' => [
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => File::load($cat['cat_picture'])->getFileUri(),
          '#attributes' => [
            'class' => 'cat-image',
            'alt' => 'cat',
          ],
        ],
      ];
      $cat['created'] = date('d-m-Y H:i:s', $cat['created']);
      $cat['edit'] = [
        'data' => [
          '#type' => 'link',
          '#title' => $this->t('Edit'),
          '#url' => Url::fromRoute('reinfate.catEdit', ['id' => $cat['id']]),
        ],
      ];
      $cat['delete'] = [
        'data' => [
          '#type' => 'link',
          '#title' => $this->t('Delete'),
          '#url' => Url::fromRoute('reinfate.catDelete', ['id' => $cat['id']]),
        ],
      ];
    }
    return $cats;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($this->data) {
      return parent::buildForm($form, $form_state);
    }
    $header = [
      'cat_name' => $this->t('Name'),
      'email' => $this->t('Email'),
      'cat_picture' => $this->t('image'),
      'created' => $this->t('Date'),
      'edit' => $this->t('Edit'),
      'delete' => $this->t('Delete'),
    ];
    $rows = $this->getCats();
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $rows,
      '#title' => t('Cats list'),
      '#empty' => t('No records found'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$this->data) {
      $form_state->setRebuild();
      $this->data = $form_state->getValues();
      return;
    }
    $selectQuery = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_picture'])
      ->condition('id', $this->data['table'], 'IN')
      ->execute();
    foreach ($selectQuery as $cat) {
      File::load($cat->cat_picture)->delete();
    }
    $this->database->delete('reinfate')
      ->condition('id', $this->data['table'], 'IN')
      ->execute();
    $this->messenger->addMessage('Deleted cat');
  }

}
