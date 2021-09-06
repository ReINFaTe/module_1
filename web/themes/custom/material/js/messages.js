(function ($) {
  Drupal.behaviors.messagesBehavior = {
    attach: function (context, settings) {
      setTimeout(() => {
        $('.messages-overlay .messages').each(
          function () {
            $(this).once('displayed').addClass('active');
            setTimeout(() => {
              $(this).slideUp(1000);
            }, 5000);
            setTimeout(() => {
              $(this).remove()
            }, 6000);
          }
        )
      }, 100)

    }
  };
  Drupal.behaviors.messagesClickBehavior = {
    attach: function (context, settings) {
      setTimeout(() => {
        $('.messages-overlay .messages').once('clickBehavior').click(function () {
          $(this).slideUp(1000);
          setTimeout(() => {
            $(this).remove()
          }, 1000);
        })
      }, 100)
    }
  };

})(jQuery);
