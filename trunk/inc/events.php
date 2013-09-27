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
class WoopraEvents extends WoopraFrontend {
	
	/**
	 * Woopra's Built in Events.
	 * @since 1.4.1
	 * @var
	 */
	var $default_events;
	
	/**
	 * What are the current event's going on?
	 * @since 1.4.1
	 * @var object
	 */
	var $current_event;
	
	/**
	 * Are there events present?
	 * @var 1.4.3
	 */
	var $present_event;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @param object $area[optional]
	 * @return 
	 */
	function WoopraEvents() {
		$this->__construct();
	}
	
	/**
	 * Events Contructor Class
	 * @since 1.4.1
	 * @return 
	 * @constructor
	 */
	function __construct() {
		Woopra::__construct();
		
		// Register Events!
		$this->register_events();
	}

	/**
	 * Register Events
	 * @since 1.4.1
	 * @return 
	 */
	function register_events() {
		/*
		 * 
		 * These are all standard events that WordPress
		 * has that Woopra built-in it's system.
		 * 
		 * 
		 * VALID FIELDS:
		 * 
		 * name* - The name the Woopra App will see.
		 * label* - What the description of the event in WordPress admin panel
		 * function - If a function is required to get the event data.
		 * object - Depending if the function returns an object, this would be the object name to get.
		 * value - Simple value when processed.
		 * 
		 * action** - The action that this event triggers.
		 * filter** - The filter that this event triggers.
		 * 
		 * setting*** - If the 'action' or 'filter' have duplicities, they must have unique setting names.
		 * 
		 */
		$default_events = array(
			array(
				'name'		=>	__('comments', 'woopra'),
				'label'		=>	__('Show comments as they are posted.', 'woopra'),
				'function'	=>	'get_comment',
				'object'	=>	'comment_content',
				'value'		=>	__('User posted comment.', 'woopra'),
				'action'	=>	'comment_post',
			),
			array(
				'name'		=>	__('search', 'woopra'),
				'label'		=>	__('Show users search queries.', 'woopra'),
				'function'	=>	'get_search_query',
				'object'	=>	null,
				'value'		=>	__('Executed Search', 'woopra'),
				'action'	=>	'the_search_query',
			)
		);
		
		$this->default_events = $default_events;
	}
	

}

?>
