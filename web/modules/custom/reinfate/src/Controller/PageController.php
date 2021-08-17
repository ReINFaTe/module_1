<?php

namespace Drupal\reinfate\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for reinfate routes.
 */
class PageController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build(): array {
    $form = \Drupal::formBuilder()->getForm('Drupal\reinfate\Form\AddCatForm');
    $build['content'] = [
      '#theme' => 'reinfate-page',
      '#title' => $this->t('Hello!'),
      '#text' => $this->t('You can add here a photo of your cat.'),
      '#form' => $form,
    ];

    return $build;
  }

}
