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
	 * @var
	 */
	var $default_events;
	
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

		add_action('wp', 			array(&$this, 'session_start') );
		
		return $WoopraEvent_Global;
		
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
				'name'		=>	__('Comments'),
				'label'		=>	__('Show comments as they are posted.'),
				'function'	=>	'get_comment',
				'object'	=>	'comment_content',
				'value'		=>	'',
				'action'	=>	'comment_post',
			),
			array(
				'name'		=>	__('Search'),
				'label'		=>	__('Show users search queries.'),
				'function'	=>	'',
				'object'	=>	'',
				'value'		=>	'',
				'filter'	=>	'the_search_query',
			),
		);
		
		return $default_events;
	}
	
	/**
	 * Process Post Event
	 * @since 1.4.1
	 * @return none
	 * @param object $event
	 * @param object $args
	 */
	function process_event($event, &$args) {
		if (!isset($_SESSION))
			session_start();
		
		$_SESSION['woopra']['events'][$event] = $args;
	}

	/**
	 * Start the session.
	 * 
	 * Only needed for 'post' events.
	 * 
	 * @since 1.4.1
	 * @return 
	 */
	function session_start() {
		if (!isset($_SESSION))
 			session_start();
		
		$this->current_event = $_SESSION['woopra']['events'];
	}

	
	/**
	 * What is the javascript we needed to generate?
	 * @return 
	 * @param object $event
	 */
	function print_javascript_events() {
		$this->default_events = $this->register_events();
		if (is_array($this->current_event)) {
			echo "\n";
			foreach ($this->current_event as $event_name => $event_value) {
				echo "woopra_event['" . $this->event_display($event_name) . "'] = '" . js_escape($this->event_value($event_name, $event_value)) . "';\r\n";
			}
			unset($_SESSION['woopra']['events']);
		}
	}
	
	/**
	 * Return the event name for the Woopra App.
	 * @return 
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
	 * @return 
	 * @param object $event_name
	 * @param object $event_value
	 */
	function event_value($event_name, $event_value) {
		foreach ($this->default_events as $_event_name => $event_datablock) {
			$_type = (isset($event_datablock['action']) ? $event_datablock['action'] : $event_datablock['filter']);
			if ($_type == $event_name) {
				if (isset($event_datablock['function']) && $event_datablock['function'] != null)
					return $this->event_function($event_datablock, $event_value);
					
				if (isset($event_datablock['value']) && $event_datablock['value'] != null)
					return $event_datablock['value'];
				
				return $event_value;
			}
		}
	}
	
	/**
	 * If the event requires a function, process it. 
	 * 
	 * Note: If the function returns an object, the $func['object'] var is used.
	 * 
	 * @return 
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

/**
 * Woopra Frontend Events Class
 * @since 1.4.1
 * @package frontend_events
 * @subpackage events
 */
class WoopraEvents_Frontend extends WoopraEvents {
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @return none
	 */
	function WoopraEvents_Frontend() {
		$this->__construct();
	}

	/**
	 * Frontend Class Constructer
	 * @return none
	 * @constructor
	 */
	function __construct() {
		$Woopra = new Woopra;
		$this->default_events = $this->register_events();
		$all_events = $this->default_events;

		$event_status = $Woopra->get_option('woopra_event');		

		foreach ($all_events as $event_name => $data) {
			if ($data['action']) {
				if ($event_status[(isset($data['setting']) ? $data['setting'] : $data['action'])])
					add_action( $data['action'], 			array(&$this, 'process_events') );
			}
			if ($data['filter']) {
				if ($event_status[(isset($data['setting']) ? $data['setting'] : $data['filter'])])
					add_filter( $data['filter'], 			array(&$this, 'process_filter_events') );
			}
		}
	}
	
	/**
	 * The handler for processing events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function process_events(&$args) {
		$current_event = current_filter();
		return $this->process_event($current_event, $args);
	}
	
	/**
	 * The handler for processing filter events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function process_filter_events(&$args) {
		$current_event = current_filter();
		$this->process_event($current_event, $args);
		return $args;	//	we have to return a filter...
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
	 * @return none
	 * @constructor
	 */
	function __construct() {
		$Woopra = new Woopra;
		$this->default_events = $this->register_events();
		$all_events = $this->default_events;

		$event_status = $Woopra->get_option('woopra_event');		

		foreach ($all_events as $event_name => $data) {
			if ($data['action']) {
				if ($event_status[(isset($data['setting']) ? $data['setting'] : $data['action'])])
					add_action( $data['action'], 			array(&$this, 'process_events') );
			}
			if ($data['filter']) {
				if ($event_status[(isset($data['setting']) ? $data['setting'] : $data['filter'])])
					add_filter( $data['filter'], 			array(&$this, 'process_filter_events') );
			}

		}
		
	}
	
	/**
	 * The handler for processing events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function admin_process_events(&$args) {
		$current_event = current_filter();
		return $this->process_event($current_event, $args);
	}
	
	/**
	 * The handler for processing filter events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function process_filter_events(&$args) {
		$current_event = current_filter();
		$this->process_event($current_event, $args);
		return $args;	//	we have to return a filter...
	}
	
}

?>
