/**
 * Basic stylesheet for the RHD Social Icons plugin by Roundhouse Designs
 **/
 
(function($){
	
	$(document).ready(function(){
		rhdPinScrollHandler();
	});
	
	
	function rhdPinScrollHandler(){
		$(window).on('resize', function(){
			setFrameDimensions();
		});
	}
	
	
	function setFrameDimensions(){
		$(".rhd-latest-pins .rhd-pin").each(function(){
			var w = $(this).width();
			$(this).height(w);
		});
	}
	
})(jQuery);