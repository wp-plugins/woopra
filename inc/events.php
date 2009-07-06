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


class WoopraEvents_Frontend extends WoopraFrontend {
	
	function WoopraEvents_Frontend () {
		$this->__construct();
	}

	function __construct() {		
		$all_events = $this->register_events();
		foreach ($all_events as $event_name) {
			add_action( $event_name, 			array(&$this, 'process_events') );
		}
	} 
	
	function register_events() {
		
		$events = array (
			'wp_head',
			'wp_footer',
			'comment_post',
		
		);
		
		return $events;
	}
	
	function process_events(&$args) {
		
		echo $args;
		
	}
	
}


?>