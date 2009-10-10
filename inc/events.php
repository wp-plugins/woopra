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
				'name'		=>	__('Comments', 'woopra'),
				'label'		=>	__('Show comments as they are posted.', 'woopra'),
				'function'	=>	'get_comment',
				'object'	=>	'comment_content',
				'value'		=>	__('User posted comment.', 'woopra'),
				'action'	=>	'comment_post',
			),
			array(
				'name'		=>	__('Search', 'woopra'),
				'label'		=>	__('Show users search queries.', 'woopra'),
				'function'	=>	'get_search_query',
				'object'	=>	null,
				'value'		=>	__('Executed Search', 'woopra'),
				'filter'	=>	'the_search_query',
			)
		);
		
		$this->default_events = $default_events;
	}
	
	/**
	 * What is the javascript we needed to generate?
	 * @since 1.4.1
	 * @return none
	 * @param object $event
	 */
	function print_javascript_events() {	
		if (is_array($this->current_event)) {
			foreach ($this->current_event as $event_name => $event_value) {
				if (!is_null($event_value) || is_object($event_value) || !empty($event_value))
					echo "we.addProperty('" . js_escape($this->event_display($event_name)) . "','" . js_escape($this->event_value($event_name, $event_value)) . "');\r\n";
			}
			unset($_SESSION['woopra']['events'], $this->current_event);
		}
	}
	
	/**
	 * Return the event name for the Woopra App.
	 * @since 1.4.1
	 * @return none
	 * @param object $event_name
	 */
	function event_display($event_name) {
		foreach ($this->default_events as $_event_name => $event_datablock) {
			if ((isset($event_datablock['action']) ? $event_datablock['action'] : $event_datablock['filter']) == $event_name) {
				return $event_datablock['name'];
			}
		}
	}
	
	/**
	 * Return the event value to show in the even name.
	 * @since 1.4.1
	 * @return mixed
	 * @param object $event_name
	 * @param object $event_value
	 */
	function event_value($event_name, $event_value) {
		foreach ($this->default_events as $_event_name => $event_datablock) {
			
			$_type = (isset($event_datablock['action']) ? $event_datablock['action'] : $event_datablock['filter']);
			
			if ($_type == $event_name) {
				
				if ( isset($event_datablock['function']) == true )
					$_return = $this->event_function($event_datablock, $event_value);
				
				if ( isset($_return) == true )
					return $_return;			
				
				if ( isset($event_datablock['value']) == true )
					$_return = $event_datablock['value'];
				
				if ( isset($_return) == true )
					return $_return;
				
				return $event_value;
			}
			
		}
	}
	
	/**
	 * If the event requires a function, process it. 
	 * 
	 * Note: If the function returns an object, the $func['object'] var is used.
	 * 
	 * @since 1.4.1
	 * @return mixed
	 * @param object $func
	 * @param object $args
	 */
	function event_function($func, $args) {
		if (function_exists($func['function'])) {
			$func_args = array(); 
			if (!is_array($args)) {
				$args_array = preg_split("%,%", $args); 
				foreach ($args_array as $arg_array) 
					array_push($func_args, $arg_array);
			} else {
				$func_args = $args;
			}
			$value = call_user_func_array($func['function'], $func_args);
			if (is_object($value))
				return $value->{$func['object']};
			else
				return $value;
		}
	}

}

?>
