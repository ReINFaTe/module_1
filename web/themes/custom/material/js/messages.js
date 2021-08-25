(function ($) {
  Drupal.behaviors.messagesBehavior = {
    attach: function (context, settings) {
      $('.form-messages .messages', context).each(
        function () {
          $(this).once('displayed').toggleClass('active');
          setTimeout(() => {
            $(this).slideUp(1000);
          }, 5000);
          setTimeout(() => {
            $(this).remove()
          }, 6000);
        }
      )
    }
  };
  Drupal.behaviors.messagesClickBehavior = {
    attach: function (context, settings) {
      $('.form-messages .messages', context).once('clickBehavior').click(function () {
        $(this).slideUp(1000);
        setTimeout(() => {
          $(this).remove()
        }, 1000);
      })
    }
  };

})(jQuery);
