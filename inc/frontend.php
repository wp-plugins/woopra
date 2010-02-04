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
	 * Hold the FrontEnd Localized Data String
	 * @since 1.5.0
	 * @var array
	 */
	var $local = array();
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 * @constructor
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
		add_action( 'init',						array(&$this, 'init')							);
		
		add_action(	'wp',						array(&$this, 'detect')							);
		add_action(	'admin_head',				array(&$this, 'detect'),					10	);		
		
		add_action( 'wp_footer', 				array(&$this, 'widget'), 					10	);
		if ($this->get_option('track_admin'))
			add_action( 'admin_footer',				array(&$this, 'widget'),				10	);
		
	}
	
	/**
	 * Initialize Frontend
	 * @since 1.5.0
	 * @return none
	 */
	function init() {
		
		//	Check to see if we should be running
		if ( !$this->get_status() || $this->get_admin() )
			return;
		
		//	Do not run if we are in admin and we are not going to track the data.
		if ( is_admin() && !$this->get_option('track_admin') )
			return;
		
		/**
		 * WordPress Woopra Event Tracking
		 */
		if ( $this->get_option('track_events') ) {
			//	Set jQuery Events Options
			if ( $this->enabled_event('image') )
				$this->create_localize( array('trackImage' => 'true', 'trackImageTitle' => __('Image Viewed')),	'woopra-events'	);
			
			if ( $this->enabled_event('comments') )
				$this->create_localize( array('trackComments' => 'true', 'trackCommentsTitle' => __('Comment Posted')),	'woopra-events'	);		
				
			if ( is_array($this->local['woopra-events']) )
				wp_enqueue_script( 'woopra-events',	$this->plugin_url() . '/js/jquery.events.js',		array('jquery', 'woopra-tracking'), '20100201', true );
			
			wp_localize_script( 'woopra-events', 'woopraEventsL10n', $this->local['woopra-events'] );
		}		
		
		/**
		 * Tracking User Information
		 */
		wp_enqueue_script( 'woopra-tracking',	$this->plugin_url() . '/js/jquery.tracking.js',		array('jquery'), '20100201', true );
		//	Set jQuery Tracking Options
		if ( $this->get_option('use_subdomain') )
			$this->create_localize(	array('rootDomain'		=>	$this->get_option('root_domain') ),		'woopra-tracking'	);
		if ( $this->get_option('use_timeout') )
			$this->create_localize( array('setTimeoutValue'	=>	($this->get_option('timeout')*1000)	),	'woopra-tracking'	);
		
		wp_localize_script( 'woopra-tracking', 'woopraFrontL10n', $this->local['woopra-tracking'] );
		

	}
	
	/**
	 * Create the localized array string.
	 * @since 1.5.0
	 * @param $array
	 * @return none
	 */
	function create_localize($array, $script) {
		$_woopra_localize = $array;
		if ( is_array($this->local[$script]) )
			$this->local[$script] = array_merge($_woopra_localize, $this->local[$script] );
		else
			$this->local[$script] = $_woopra_localize;
	}
	
	/**
	 * Get Event Options
	 * @since 1.5.0
	 * @return none
	 * @param object $event
	 */
	function enabled_event($event) {
		
		//	Currently enabling all event tracking.
		return true;
		
		if ( !empty($this->options['events'][$event]) )
			return $this->options['events'][$event];
		else
			return false;
	}
	
	/**
	 * What is Woopra Status?
	 * @since 1.4.1
	 * @return boolean
	 */
	function get_status() {
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
	function get_admin() {
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
	function widget() {
		
		//	Check to see if we should be running
		if ( !$this->get_status() || $this->get_admin() )
			return;
		
		//	Do not run if we are in admin and we are not going to track the data.
		if ( is_admin() && !$this->get_option('track_admin') )
			return;
		
		/*** JQUERY CODE -- DO NOT MODFIY ***/
		echo "\r\n<!-- Woopra Analytics Code -->\r\n";
		echo "<script type=\"text/javascript\">\r\n";
		echo "jQuery.trackWoopra({ name : '" . js_escape($this->woopra_visitor['name']) . "', email : '" . js_escape($this->woopra_visitor['email']) . "', avatar : '" . urlencode("http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png") . "' } );\r\n";
		echo "</script>\r\n";
		echo "<!-- End of Woopra Analytics Code -->\r\n\r\n";
		/*** JQUERY CODE -- DO NOT MODFIY ***/
		
	}
	
	/**
	 * How Woopra Detects Vistors
	 * @since 1.4.1
	 * @return none
	 */
	function detect() {
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
?>