/*
* jQuery Woopra Analytics Plugin (jquery.tracking.js)
*
* A jQuery plugin that makes it easier to implement Woopra tracking for your site
* and allows you to add your own Woopra Tracking Events to the system to follow.
*
* Adds the following methods to jQuery:
*   - $.trackWoopra() - Adds Woopra tracking on the page from which it's called.
*   - $.woopraEvent() - Tracks an event using the given parameters.
*   - $('a').trackEvent() - Adds event tracking to element(s).
*
* See here for more: http://www.woopra.com/docs/customization/
*
* Copyright (c) 2009 Pranshu Arya
* Modified by Shane <shane@bugssite.org> to work for WordPress
* 
* Version 1.0
**
* Licensed under the MIT license:
* http://www.opensource.org/licenses/mit-license.php
*
* Credits:
*   - http://woopra.com
*   - http://github.com/christianhellsten/jquery-google-analytics
*   - http://pranshuarya.com/jaal/Development/jquery-woopra-plugin.html
*/
(function($) {

	
	/**
	 * Debug Information
	 */
	$.woopraDebug = function (message) {
		if (typeof console != 'undefined' && typeof console.debug != 'undefined' && $.fn.trackEvent.defaults.debug) {
			console.debug(message);
		}
	};	
	
	/**
	 * 
	 */
	$.trackWoopra = function (woopra_data) {
		var script;
		var src  = 'http://static.woopra.com/js/woopra.v2.js';
		
		function _woopra_track() {
			if ( woopraTracker != undefined ) {
				if ( typeof woopraFrontL10n != "undefined" ) {
					if ( woopraFrontL10n.rootDomain != null ) {
						woopraTracker.setDomain( woopraFrontL10n.rootDomain );
						debug('Woopra Root Domain: ' +  woopraFrontL10n.rootDomain);
					}
					if ( woopraFrontL10n.setTimeoutValue > 0 ) {
						woopraTracker.setIdleTimeout( woopraFrontL10n.setTimeoutValue );
						debug('Woopra Idle Timeout: ' +  woopraFrontL10n.setTimeoutValue + 'ms');
					}
				}
				//	Only run when we have data.
				if ( woopra_data.name != null ) {
					woopraTracker.addVisitorProperty( 'name' , woopra_data.name );
					woopraTracker.addVisitorProperty( 'email' , woopra_data.email );
					woopraTracker.addVisitorProperty( 'avatar', woopra_data.avatar );
				}
				woopraTracker.track();
				debug('Woopra is loaded.');
			} else { 
			   throw "woopraTracker is undefined.";
			}
		}
		
		_load_woopra = function () {
			$.ajax({
				type: "GET",
				url: src,
				success: function() {
					_woopra_track();
				},
				dataType: "script",
				cache: true // We want the cached version.
			});
		}
		
		 _load_woopra();
	}
	
	/**
	 * Process a Woopra Event Manually
	 */
	$.woopraEvent = function(title, woopra_options) {
		if ( woopraTracker == 'undefined' ) {
			debug('FATAL: woopraTracker is not defined'); // blocked by whatever
		} else {
			var w_event = new WoopraEvent(title);
			// For each property pair passed to woopraEvent, add it to w_event
			$.each(woopra_options, function(i, val){
				w_event.addProperty(i, val);
			});
			w_event.fire();
		}
	};
	
	/**
	 * Track Event jQuery Hook
	 * 
	 * Example:
	 * 
	 * 	Trigger event: 	When user clicks on a link.
	 * 	Code:			jQuery("a").trackEvent({ title: 'Click on link', label: 'A element.' });
	 * 
	 * This event is only fired by default, when the event is a 'click'. This can be
	 * changed by passing a different 'event_name'.
	 * 
	 * Example:
	 * 	
	 * 	Trigger event: 	When user is over a link.	
	 * 	Code:			jQuery("a").trackEvent({ title: 'Hovering over event.', label: 'A element hover.', event_name: 'mouseover' });
	 * 
	 */
	$.fn.trackEvent = function(woopra_options) {
		
		return this.each( function () {
			var element = $(this);
			var parent = $(element).parent();
			
			// Prevent an element from being tracked multiple times.
			if ( element.hasClass('w_tracked') ) {
				return false;
			} else {
				element.addClass('w_tracked');
			}
			if (woopra_options) {
				// Use default woopra_options, if necessary
				var woopra_settings = $.extend({}, $.fn.trackEvent.defaults, woopra_options);

				// Merge custom woopra_options with defaults.
				var title = evaluate(element, woopra_settings.title);
				var event_name = evaluate(element, woopra_settings.event_name);
				
				// Iterate over the other property pairs in 'woopra_options'.  Leave them alone if they are
				// text, evaluate them if they are functions.
				var options = {};
				$.each(woopra_options, function(i, val){
					options[i] = evaluate(element, val);
				});  
			}
				
			var message = '';
			$.each(options, function(i, val){
				message += i + ' : ' + val + ', ';
			});
			
			// Display list of all items that are being monitored and will be tracked
			// when event_name is triggered
			debug('Monitoring ' + message);
			
			// Bind the event to this element.
			element.bind(event_name + '.track', function() {
				// Get index of item that was clicked
				var index = $(parent).find('> *').index(this);
				// Should we skip internal links? REFACTOR
				var skip = woopra_settings.skip_internal && (element[0].hostname == location.hostname);
				// Get title and other property pair values for item that was clicked
				// and pass them to $.woopraEvent
				if( !skip ) {
					var title = evaluate($(parent).children().eq(index), woopra_settings.title);
					options = {}
					$.each(woopra_options, function(i, val){
						options[i] = evaluate($(parent).children().eq(index), val);
					});
					$.woopraEvent(title, options);
					debug('Tracked ' + message);
				} else {
					debug('Skipped ' + message);
				}
				return true;
			});
		});
		
		/**
		 * 
		 */
		function evaluate(element, text_or_function) {
			if ( typeof text_or_function == 'function' ) {
				text_or_function = text_or_function(element);
			}
			return text_or_function;
		};
		
	};
	
	/*
	 * Alias for $.woopraDebug
	 */
	function debug(message) {
		$.woopraDebug(message);
	}
	
	/**
	 * Default Settings
	 */
	$.fn.trackEvent.defaults = {
		title			: function(element) { return (element[0].hostname == location.hostname) ? 'internal' : 'external'; },
		skip_internal	: false,
		event_name		: 'click',
		debug			: true
	};
	
})(jQuery);