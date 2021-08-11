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
      '#type' => 'item',
      '#markup' => $this->t('Hello! You can add here a photo of your cat.'),
    ];

    return $build;
  }

}
