<?php
/**
 * WoopraFrontend Class for Woopra
 *
 * This class contains all functions and actions required for Woopra to work on the frontend of WordPress
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage frontend
 */
class WoopraFrontend extends Woopra {

	/**
	 * Holder for the event class.
	 * @since 1.4.1
	 * @var object
	 */
	var $event;

	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraFrontend () {
		$this->__construct();
	}
	
	/**
	 * Frontend Contructor Class
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		Woopra::__construct();
		
		//	Frontend Actions
		add_action(	'wp',						array(&$this, 'woopra_detect')					);
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget'), 			10	);
		
		add_action(	'admin_head',				array(&$this, 'woopra_detect')					);
					
		if ($this->get_option('track_admin'))
			add_action( 'admin_footer',				array(&$this, 'woopra_widget'),			10	);
		
		//	Process Events
		$this->event = new WoopraEvents('frontend');
	}
	
	/**
	 * What is Woopra Status?
	 * @since 1.4.1
	 * @return boolean
	 */
	function woopra_status() {
		if ($this->get_option('run_status') == 'on')
			return true;
		else
			return false;
	}
	
	/**
	 * Should we be tracking admins?
	 * @since 1.4.1
	 * @return boolean
	 */
	function woopra_admin() {
		if ($this->get_option('ignore_admin'))
			if ($this->woopra_visitor['admin'])
				return true;
			else
				return false;
		else
			return false;
	}
	
	/**
	 * Create the Javascript Code at the Bottom
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_widget() {
		
		if (!$this->woopra_status())
			return;
		
		if ($this->woopra_admin())
			return;
		
		/*** JAVASCRIPT CODE -- DO NOT MODFIY ***/
		
		echo "<!-- Woopra Analytics Code -->\n";
		echo "<script type=\"text/javascript\"> var _wh = ((document.location.protocol=='https:') ? \"https://sec1.woopra.com\" : \"http://static.woopra.com\");document.write(unescape(\"%3Cscript src='\" + _wh + \"/js/woopra.js' type='text/javascript'%3E%3C/script%3E\")); </script>\r\n";
		
		if ($this->get_option('auto_tagging') && !empty($this->woopra_visitor['name'])) {
			$woopra_tracker .= "woopraTracker.addVisitorProperty('name','" . js_escape($this->woopra_visitor['name']) . "');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('email','" . js_escape($this->woopra_visitor['email']) . "');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('avatar','". urlencode("http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png") . "');\r\n";
		}
		if ($this->get_option('use_timeout')) {
			$woopra_tracker .= "woopraTracker.setidletimeout = ".($this->get_option('timeout')*1000).";\r\n";
		}
		
		echo "<script type=\"text/javascript\">\r\n";
		echo $woopra_tracker;
		echo "</script>\r\n";
		
		if ( $this->event->current_event->vaild ) {
			echo "<script type=\"text/javascript\">\r\n";
			echo "var we = new WoopraEvent();\r\n";
			$this->event->print_javascript_events();
			echo "we.fire();";
			echo "</script>\r\n";
		}
		echo "<!-- End of Woopra Analytics Code -->";
		/*** JAVASCRIPT CODE -- DO NOT MODFIY ***/
		
	}

	/**
	 * How Woopra Detects Vistors
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_detect() {
		$current_user = wp_get_current_user();
		
		// Wait? The user is logged in. Get that data instead.
		if (is_user_logged_in()) {
			$this->woopra_visitor['name'] = $current_user->display_name;
			$this->woopra_visitor['email'] = $current_user->user_email;
		}
		
		if ($current_user->user_level == 10)
			$this->woopra_visitor['admin'] = true;
		
	}
	
}