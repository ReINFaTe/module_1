(function ($, window, Drupal, drupalSettings) {
  'use strict';
  Drupal.AjaxCommands.prototype.AjaxCommand = function(ajax, response, status){
    let uri = response.uri;
    let command = new Drupal.Ajax(false, false, {
      url: uri,
    });
    command.execute();
  }
})(jQuery, this, Drupal, drupalSettings);
