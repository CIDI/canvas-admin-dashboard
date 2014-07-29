/*jslint browser: true, sloppy: false, eqeq: false, vars: false, maxerr: 50, indent: 2, plusplus: true */
/*global $, jQuery */

(function ($) {
  'use strict';

  $(function () {
    $('form').on('submit', function (e) {
      e.preventDefault();

      return false;
    });

    $('.control-accountState').on('click', 'button', function (e) {
      var form = $(this).closest('form'),
        buttonData = {};

      buttonData[$(this).attr('name')] = $(this).val();
      buttonData['term'] = $('[name=term]', form).val();

      $.ajax({
        url: form.attr('action'),
        type: 'post',
        dataType: 'json',
        context: $(this).closest('.btn-group'),
        data: buttonData,
        success: function (data) {
          console.log(data);
          // remove active class from all buttons
          $('.btn', this).removeClass('active');

          // add the active class back on the button that matches the state
          // reported by the server
          $('[name=' + data.state + ']', this).addClass('active');

          var li = $(this).closest('li');
          var depth = li.attr('class').replace(/.* depth-(\d+)/, '$1');
          $(this).closest('li').nextUntil('.depth-'+depth).find('.btn').removeClass('active').filter('[name=' + data.state + ']').addClass('active');
        }
      });

      e.preventDefault();
      return false;
    });
  });
}(jQuery));