(function($) {

	$(window).load(function() {
		$('.da-gallery').each(function() {
			// Slimbox bindings
			var thumbs = $('a:has(img.thumb)', this);
			if (thumbs.length) {
				thumbs.slimbox({counterText: 'Deviation {x} of {y}', loop: true});
			}
		});

		// Caption Sliding
		$('.da-gallery li').each(function() {
			var caption = $('.entry-title, .entry-meta', this).css('display', 'block');
			$('a:has(img.thumb)',this).hover(function() {
				caption.animate({'bottom': '-=70px'}, 'fast')
			}, function() {                          
				caption.animate({'bottom': '+=70px'}, 'slow')
			});
		});
	});
})(jQuery);
