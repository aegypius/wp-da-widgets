jQuery(document).ready(function() {
	jQuery('#cache-enabled').click(function() {
		var enabled = this.checked;
		jQuery.each(['cache-path', 'cache-duration'], function(k, v) {
			if (enabled) {
				jQuery('label[for=' + v + ']').removeClass('disabled');
			} else {
				jQuery('label[for=' + v + ']').addClass('disabled');
			}
			jQuery('#' + v ).attr('disabled', !enabled);
		});
	});
	jQuery('#thumb-enabled').click(function() {
		var enabled = this.checked;
		jQuery.each(['thumb-size-x', 'thumb-size-y'], function(k, v) {
			if (enabled) {
				jQuery('label[for=' + v + ']').removeClass('disabled');
			} else {
				jQuery('label[for=' + v + ']').addClass('disabled');
			}
			jQuery('#' + v ).attr('disabled', !enabled);
		});

	});
});