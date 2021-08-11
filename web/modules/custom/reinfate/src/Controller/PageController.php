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

    $build['content'] = [
      '#theme' => 'reinfate-page',
      '#title' => $this->t('Hello!'),
      '#text' => $this->t('You can add here a photo of your cat.'),
    ];

    return $build;
  }

}
