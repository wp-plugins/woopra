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
class WoopraEvents {
	
	/**
	 * 
	 * @return 
	 * @param object $area[optional]
	 */
	function WoopraEvents($area = 'frontend') {
		$this->__construct($area);
	}
	
	/**
	 * 
	 * @return 
	 * @param object $area[optional]
	 */
	function __construct($area = 'frontend') {
		if ($area == 'frontend')
			$WoopraEvent_Global = new WoopraEvents_Frontend();
		if ($area == 'admin')
			$WoopraEvent_Global = new WoopraEvents_Admin();
		
		return $WoopraEvent_Global;
		
	}
	
	function register_events() {
		$events = array(
			'comment_post',
		);
		return $events;
	}
	
	function process_event($event, &$args) {
				
	}
	
}
 
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
		$this->process_event($current_event, $args);	
	}
	
}

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
		WoopraEvents::__construct();
	}

	
}

?>