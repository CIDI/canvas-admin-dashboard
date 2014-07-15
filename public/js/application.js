var BASE_URL = "";

(function($){
  
  $(function() {
  		BASE_URL = $('body').data('base-url');
  		
		$('.control-accountState button').on('click', function(){
			var state = $(this).val();
			var parentListElement = $(this).closest('li');
			
			$.ajax({
				url: BASE_URL + 'accounts/state/' + state,
				data: {
					id: parentListElement.attr('rel')
				},
				method: 'POST',
				success: function(data){
					parentListElement.removeClass('include exclude').addClass(state);
					$('li', parentListElement).removeClass('include exclude').addClass(state);
				},
				error: function(jqXHR, code, text) {
					console.log(arguments);
				}
			});
		});			
  });
  
}(jQuery));
