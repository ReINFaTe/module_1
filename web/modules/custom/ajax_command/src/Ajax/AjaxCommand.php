<?php

namespace Drupal\ajax_command\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Command to execute ajax request.
 */
class AjaxCommand implements CommandInterface {
  /**
   * Url to which client should make ajax request.
   *
   * @var string
   */
  private string $uri;

  /**
   * Constructs an SlideDownCommand object.
   */
  public function __construct(string $uri) {
    $this->uri = $uri;
  }

  /**
   * Implements Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {
    return [
      'command' => 'AjaxCommand',
      'method' => NULL,
      'uri' => $this->uri,
    ];
  }

}
