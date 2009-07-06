<?php
/**
 * WoopraEvents_Frontend and WoopraEvents_Admin Class for Woopra
 *
 * This class contains all event related code including the API for other plugins to use.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage events
 */
 
/**
 * Main Woopra Events Class
 * @since 1.4.1
 * @package events
 * @subpackage woopra
 */
class WoopraEvents {
	
	/**
	 * @since 1.4.1
	 * @var object
	 */
	var $current_event;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @return 
	 * @param object $area[optional]
	 */
	function WoopraEvents($area = 'frontend') {
		$this->__construct($area);
	}
	
	/**
	 * Events Contructor Class
	 * @since 1.4.1
	 * @return 
	 * @param object $area[optional]
	 * @constructor
	 */
	function __construct($area = 'frontend') {
		if ($area == 'frontend')
			$WoopraEvent_Global = new WoopraEvents_Frontend();
		if ($area == 'admin')
			$WoopraEvent_Global = new WoopraEvents_Admin();
		
		add_action('template_redirect', 	array(&$this, 'session_start') );
		
		return $WoopraEvent_Global;
		
	}
	
	function register_events() {
		$events = array(
			'comment_post',
		);
		return $events;
	}
	
	function process_event($event, &$args) {
		if (!isset($_SESSION))
			session_start();
		
		$_SESSION['woopra']['events'][$event] = $args;
	}
	
	function session_start() {
		if (!isset($_SESSION))
 			session_start();
		
		if (isset($_SESSION['woopra']['events']))
			$this->current_event = $_SESSION['woopra']['events'];
		
	}
	
	/**
	 * What is the javascript we needed to generate?
	 * @return 
	 * @param object $event
	 */
	function print_javascript($event) {
		foreach ($event as $event_name => $event_value) {
			echo "woopra_event['$event_name'] = '" . js_escape($event_value) . "';\r\n";
		}
	}
	
}

/**
 * Woopra Frontend Events Class
 * @since 1.4.1
 * @package frontend_events
 * @subpackage events
 */
class WoopraEvents_Frontend extends WoopraEvents {
	
	/**
	 * 
	 * @return 
	 */
	function WoopraEvents_Frontend() {
		$this->__construct();
	}

	/**
	 * 
	 * @return 
	 */
	function __construct() {		
		$all_events = $this->register_events();
		foreach ($all_events as $event_name) {
			add_action( $event_name, 			array(&$this, 'process_events') );
		}
	}
	
	/**
	 * The handler for processing events.
	 * @since 1.4.1
	 * @return 
	 * @param object $args
	 */
	function process_events(&$args) {
		$current_event = current_filter();
		return $this->process_event($current_event, $args);
	}
	
}

/**
 * Woopra Admin Events Class
 * @since 1.4.1
 * @package admin_events
 * @subpackage events
 */
class WoopraEvents_Admin extends WoopraEvents {
	
	/**
	 * 
	 * @return 
	 */
	function WoopraEvents_Admin() {
		$this->__construct();
	}

	/**
	 * 
	 * @return 
	 */
	function __construct() {
	
	}

	
}

?>