<?php

namespace Drupal\reinfate\Controller;

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
    $this->getCats();
    $build['content'] = [
      '#theme' => 'reinfate-page',
      '#title' => $this->t('Hello!'),
      '#text' => $this->t('You can add here a photo of your cat.'),
      '#form' => $form,
      '#cats' => $cats,
    ];

    return $build;
  }

  /**
   * Selects reinfate table from database.
   */
  public function getCats(): array {
    $result = $this->database->query("SELECT cat_name, email, cat_picture, created FROM {reinfate}");
    $result = $result->fetchAll();
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
      ];
      array_push($cats, $cat_render);
    }
    return $cats;
  }

}
