<?php

namespace Drupal\reinfate\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for reinfate routes.
 */
class PageController extends ControllerBase {

  /**
   * Drupal\Core\Database\ definition.
   *
   * @var \Drupal\Core\Database\
   */
  protected $database;

  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instanse = parent::create($container);
    $instanse->database = $container->get('database');
    $instanse->formBuilder = $container->get('form_builder');
    return $instanse;
  }

  /**
   * Builds the response.
   */
  public function build(): array {
    $form = $this->formBuilder->getForm('Drupal\reinfate\Form\NewCatForm');
    $cats = $this->getCats();
    $build['content'] = [
      '#theme' => 'reinfate-page',
      '#title' => $this->t('Hello!'),
      '#text' => $this->t('You can add here a photo of your cat.'),
      '#form' => $form,
      '#cats' => $cats,
//      '#links' => [
//        'edit' =>,no,
//        'delete' =>,
//      ],
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
    return $build;
  }

  /**
   * Ajax response for cat details.
   */
  public function catDetailsAjax($id) {
    $result = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_name', 'email', 'cat_picture', 'created'])
      ->condition('id', $id)
      ->execute();
    $cat = $result->fetch();
    $cat->cat_picture = [
      '#theme' => 'image_style',
      '#style_name' => 'wide',
      '#uri' => File::load($cat->cat_picture)->getFileUri(),
      '#attributes' => [
        'class' => 'cat-image',
        'alt' => 'cat',
      ],
    ];
    $cat_render = [
      '#theme' => 'reinfate-cat-full',
      '#cat_name' => $cat->cat_name,
      '#email' => $cat->email,
      '#cat_picture' => $cat->cat_picture,
      '#created' => $cat->created,
      '#catId' => $cat->id,
    ];
    $response = new AjaxResponse();
    $dialog_options = [
      'width' => 'auto',
      'height' => 'auto',
      'dialogClass' => 'cat-dialog',
      'modal' => 'true',
    ];
    $response->addCommand(new OpenDialogCommand('#cat-details', 'cat', $cat_render, $dialog_options));
    return $response;
  }

  /**
   * Ajax response for cats list.
   */
  public function catsListAjax() {
    $response = new AjaxResponse();
    $cats = $this->getCats();
    $response->addCommand(new ReplaceCommand('.cats-list', $cats));
    return $response;
  }

  /**
   * Ajax response for cats form.
   */
  public function catsFormAjax($method) {
    $response = new AjaxResponse();
    $form = $this->formBuilder->getForm('Drupal\reinfate\Form\NewCatForm');

    $response->addCommand(new ReplaceCommand('.reinfate-newcatform', $form));
    return $response;
  }

  /**
   * Selects reinfate table from database.
   */
  public function getCats(): array {
    $result = $this->database->select('reinfate', 'c')
      ->fields('c', ['id', 'cat_name', 'email', 'cat_picture', 'created'])
      ->orderBy('id', 'DESC')
    // ->extend(PagerSelectExtender::class)
    // ->limit(5)
      ->execute();
    $cats = [];
    foreach ($result as $cat) {
      $cat->cat_picture = [
        '#theme' => 'image_style',
        '#style_name' => 'medium',
        '#uri' => File::load($cat->cat_picture)->getFileUri(),
        '#attributes' => [
          'class' => 'cat-image',
          'alt' => 'cat',
        ],
      ];
      $cat_render = [
        '#theme' => 'reinfate-cat-block',
        '#cat_name' => $cat->cat_name,
        '#email' => $cat->email,
        '#cat_picture' => $cat->cat_picture,
        '#created' => $cat->created,
        '#catId' => $cat->id,
      ];
      array_push($cats, $cat_render);
    }
    return [
      '#theme' => 'reinfate-cats-list',
      '#cats' => $cats,
    ];
  }

}
