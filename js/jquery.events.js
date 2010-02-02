jQuery(document).ready(function() {
	
	//	Exists Function
	jQuery.contains = function (obj, string) {
		if ( obj.substr(obj.length-string.length,string.length) == string )
			return true;
		
		return false;
	}
	
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
		
		jQuery("a:notexternal > img").each( function(i) {
			jQuery(this).trackEvent({ 
				title : woopraEventsL10n.trackImageTitle,
				label : jQuery(this).parent("a").attr('title')
			});
		});
	}
	
	/*
	 * We are going to go through each form fist to see what we can track.
	 */
	jQuery("form").each( function(i) {
		debug('Woopra Event Tracking Form ('+jQuery(this).attr('id')+'): ' + jQuery(this).attr('action'));
		
		/*
		 * Code for comment tracking.
		 */
		if ( woopraEventsL10n.trackComments && jQuery.contains(jQuery(this).attr('action'), 'wp-comments-post.php') ) {
			debug('Woopra Tracking Comments.');
			jQuery("#" + jQuery(this).attr('id') + " input#submit").trackEvent({ 
				title : woopraEventsL10n.trackCommentsTitle,
				comment : jQuery("#" + jQuery(this).attr('id') + " textarea").val()
			});
		}
		
	});
		
	
	/*
	 * Alias for jQuery.woopraDebug
	 */
	function debug(message) {
		jQuery.woopraDebug(message);
	}
	
	
});