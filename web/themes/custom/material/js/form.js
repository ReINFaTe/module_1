(function ($) {
  Drupal.behaviors.formErrorBehavior = {
    attach: function (context, settings) {
      $('.form-item input', context).once('form-error').each(function (){
        console.log($(this))
        if($(this).hasClass('error')){
          $(this).removeClass('error');
          $(this).addClass('erroridk');
        }
      })
    }
  };
})(jQuery);
