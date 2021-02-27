/**
* @file
*/

(function ($, Drupal) {
  Drupal.AjaxCommands.prototype.rollStat = function (ajax, response, status) {
    console.log(response.message);
  }

  $('.option-to-clear').on('click touchup', function(e) {
    console.log('clear');
    $('input[type!="hidden"], input[type!="submit"], select').each( function(i) {
      $(this).val('');
    });
  });

})(jQuery, Drupal);
