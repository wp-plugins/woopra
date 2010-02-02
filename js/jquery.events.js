jQuery(document).ready(function() {
		
	if ( woopraEventsL10n.trackImage ) {
		/**
		 * When we click on an 'link' that is surrording
		 * an image (i.e. <a href=""><img src="example.jpg" /></a>) we should
		 * go ahead and mark the 'a'.
		 * 
		 * Internal Only.
		 * 
		 */
		jQuery.expr[':'].notexternal = function(obj) {
		    return (obj.hostname == location.hostname);
		};
		
		jQuery("a:notexternal > img").each(function(i) {
			jQuery(this).trackEvent({ 
				title : woopraEventsL10n.trackImageTitle,
				label : jQuery(this).parent("a").attr('title')
			});
		});
	}
	
});