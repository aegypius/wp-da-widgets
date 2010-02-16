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
		jQuery.each(['thumb-path', 'thumb-size-x', 'thumb-size-y', 'thumb-format'], function(k, v) {
			if (enabled) {
				jQuery('label[for=' + v + ']').removeClass('disabled');
			} else {
				jQuery('label[for=' + v + ']').addClass('disabled');
			}
			jQuery('#' + v ).attr('disabled', !enabled);
		});
	});

	function Tabpanel () {
		this.__construct.apply(this, arguments);
	};
	Tabpanel.prototype = {
		cache: [],
		__construct: function(selector) {
			jQuery(selector).addClass('tabs');
			this.cache = jQuery(selector + ' > ul > li > a');
			this.bindEvents();
			jQuery(this.cache[0]).trigger('click');
		},
		bindEvents: function() {
			var links = this.cache;
			jQuery.each(links, function(idx, tab) {
				jQuery(tab).click(function(event) {
					jQuery.each(links, function(idx, e) {
						jQuery(jQuery(e).attr('href')).hide()
						jQuery(e).parent().removeClass('active')
					});
					jQuery(jQuery(this).attr('href')).show();
					jQuery(this).parent().addClass('active');
					return false;
				});
			});
		}
	};

	var t = new Tabpanel('#da-widgets-settings');
});