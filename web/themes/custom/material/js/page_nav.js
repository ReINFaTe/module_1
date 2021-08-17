(function ($) {
  Drupal.behaviors.pageNavigationBehavior = {
    attach: function (context, settings) {
      $(context).find('.menu-button').once('page-menu-loaded').click(function () {
        $('.layout-container').toggleClass('page-nav-min');
      });
    }
  };
})(jQuery);
