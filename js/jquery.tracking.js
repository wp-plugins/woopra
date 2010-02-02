/*
* jquery-woopra-analytics plugin
*
* A jQuery plugin that makes it easier to implement Woopra tracking for your site.
* I've ported this from Christian Hellsten's excellent jQuery Google Analytics plugin.
*
* His plugin can be found at http://github.com/christianhellsten/jquery-google-analytics.
*
* Adds the following methods to jQuery:
*   - $.trackWoopra() - Adds Woopra tracking on the page from which it's called.
*   - $.woopraEvent() - Tracks an event using the given parameters.
*   - $('a').trackEvent() - Adds event tracking to element(s).
*
* See here for more: http://www.woopra.com/docs/customization/
*
*
* Copyright (c) 2009 Pranshu Arya
*
* Version 1.1
**
* Licensed under the MIT license:
* http://www.opensource.org/licenses/mit-license.php
*
* Credits:
*   - http://woopra.com
*   - http://github.com/christianhellsten/jquery-google-analytics
*   
* Modifed by Shane <shane@bugssite.org> to work for the WordPress Woopra Plugin
* 
*/

(function($) {

  /**
   * Enables Woopra tracking on the page from which it's called. 
   *
   * Usage:
   *
   *  <script type="text/javascript">
   *    $.trackWoopra();
   *  </script>
   *
   *  -or-
   *
   *  <script type="text/javascript">
   *    $.trackWoopra({domain : 'http://www.mydomain.com', url : 'http://www.myurl.com', title : 'My page', cookie : 'exclude_cookie'});
   *  </script>
   * 
   * domain parameter is optional - use it to specify root domain (to track sub-domains)
   * 
   * url and title parameters are optional (but must be passed in together),
   * in case you want to give pages custom names in Woopra
   *
   * use the cookie parameter to exclude visitors, including yourself, based on a cookie
   *
   */
  $.trackWoopra = function() {
		var script;
		var src  = 'http://static.woopra.com/js/woopra.v2.js';

		function _woopra_track() {
			if ( woopraTracker != undefined ) {
				if ( woopraFrontL10n.subDomainTracking ) {
					woopraTracker.setDomain( woopraFrontL10n.rootDomain );
					debug('Woopra Root Domain: ' +  woopraFrontL10n.rootDomain);
				}
				woopraTracker.track();
				debug('Woopra is loaded.');
			} else { 
			   throw "woopraTracker is undefined.";
			}
		}
		
		_load_woopra = function() {
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
   * Tracks an event using the given parameters. 
   *
   * The woopraEvent method takes as few or as many property pairs as you provide:
   * http://www.woopra.com/forums/topic/how-do-i-title-a-custom-event)
   *
   * Defaults for title, event_name, and skip_internal are specified at the bottom
   *
   *  title - name of custom event
   *  var1, var2, var3, ...
   *  event_name - name of the event you want to track
   *  skip_internal - optional boolean value. If true then internal links are not tracked.
   *
   */
  $.woopraEvent = function(title, woopra_options) {
		if ( woopraTracker == 'undefined' ) {
			debug('FATAL: woopraTracker is not defined'); // blocked by whatever
		}
		else if(excludeCookie == 'noCookie') {
			var w_event = new WoopraEvent(title);
			
			// For each property pair passed to woopraEvent, add it to w_event
			$.each(woopra_options, function(i,val){
				w_event.addProperty(i,val);
			//	debug('w_event.addProperty(' + i + ', ' + val + ')');
			});
			w_event.fire();
		}
	};

  /**
   * Adds click tracking to elements. Usage:
   *
   *  $('a').trackEvent({title : 'title', var1 : 'value1', var2: 'value2', var3 : 'value3', ..., event_name: 'mouseover'});
   *
   *  title parameter is required, all others are optional
   *  add as many property pairs as you want (as shown above) on your webpage
   */
  $.fn.trackEvent = function(woopra_options) {

		// Add event handler to all matching elements
		return this.each(function() {
			var element = $(this);
			var parent = $(element).parent();
		  
			// Prevent an element from being tracked multiple times.
			if (element.hasClass('w_tracked')) {
				return false;
			} 
			else {
				element.addClass('w_tracked');
			}
			if(woopra_options){
				// Use default woopra_options, if necessary
				var woop_settings = $.extend({}, $.fn.trackEvent.defaults, woopra_options);

				// Merge custom woopra_options with defaults.
				var title = evaluate(element, woop_settings.title);
				var event_name = evaluate(element, woop_settings.event_name);
				
				// Iterate over the other property pairs in 'woopra_options'.  Leave them alone if they are
				// text, evaluate them if they are functions.
				var options = {};
				$.each(woopra_options, function(i,val){
					options[i] = evaluate(element,val);
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
				var skip = woop_settings.skip_internal && (element[0].hostname == location.hostname);
				// Get title and other property pair values for item that was clicked
				// and pass them to $.woopraEvent
				if(!skip) {
					var title = evaluate($(parent).children().eq(index), woop_settings.title);
					options = {}
					$.each(woopra_options, function(i,val){
						options[i] = evaluate($(parent).children().eq(index), val);
					});
					$.woopraEvent(title, options);
					// Display item that was tracked when event_name was triggered
					if(excludeCookie == 'noCookie'){
						debug('Tracked ' + message);
					}
					else {
						debug('Tracking ' + message + ' skipped due to exclude cookie');
					}
				} else {
					if(excludeCookie == 'noCookie'){
						debug('Skipped ' + message);
					}
					else {
						debug('Skipped ' + message + ' due to exclude cookie');
					}
				}
				return true;
			});
		});
		
		
		/**
		 * Checks whether a setting value is a string or a function.
		 * 
		 * If second parameter is a string: returns the value of the second parameter.
		 * If the second parameter is a function: passes the element to the function and returns function's return value.
		 */
		function evaluate(element, text_or_function) {
			if(typeof text_or_function == 'function') {
			text_or_function = text_or_function(element);
		}
		return text_or_function;
		};
	};

	/**
	* Prints to Firebug console, if available. To enable:
	*   $.fn.track.trackEvent.debug = true;
	*/
	function debug(message) {
		if (typeof console != 'undefined' && typeof console.debug != 'undefined' && $.fn.trackEvent.defaults.debug) {
		console.debug(message);
		}	
	};

	/**
	* Default (overridable) settings.
	*/
	$.fn.trackEvent.defaults = {
		title      : function(element) { return (element[0].hostname == location.hostname) ? 'internal':'external'; },
		skip_internal : false,
		event_name    : 'click',
		debug         : true
	};
})(jQuery);