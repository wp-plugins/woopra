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
	 * @since 1.4.1
	 * @var
	 */
	var $default_events;
	
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

		add_action('wp', 	array(&$this, 'session_start') );
		
		return $WoopraEvent_Global;
		
	}
	
	/**
	 * Register Events
	 * @since 1.4.1
	 * @return 
	 */
	function register_events() {
		/*
		 * These are all standard events that WordPress has that Woopra
		 * built-in it's system.
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
				'name'		=>	__('Login'),
				'label'		=>	__('Show that the user has just logged in.'),
				'function'	=>	'',
				'object'	=>	'',
				'value'		=>	__('Logged On'),
				'action'	=>	'wp_login',
				'adminonly'	=>	1,
			),
			
		);
		
		return $default_events;
	}
	
	/**
	 * Process the event.
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
	 * @since 1.4.1
	 * @return 
	 */
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
		$this->default_events = $this->register_events();
		foreach ($event as $event_name => $event_value) {
			echo "woopra_event['" . $this->event_display($event_name['action']) . "'] = '" . js_escape($this->event_value($event_name, $event_value)) . "';\r\n";
		}
		unset($_SESSION['woopra']['events']);
	}
	
	/**
	 * 
	 * @return 
	 * @param object $data
	 */
	function event_display($data) {
		foreach ($this->default_events as $event_name => $event_datablock) {
			if ($event_name == $data)
				return $event_datablock['name'];
		}
	}
		
	/**
	 * Process the event's value.
	 * 
	 * @return 
	 * @param object $event
	 * @param object $data
	 */
	function event_value($event, $args) {
		foreach ($this->default_events as $event_name => $event_datablock) {
			if ($event_name == $event) {
				if ((isset($event_datablock['function'])) && (function_exists($event_datablock['function']))) {
					$func_args = array(); 
					if (! is_array($args)) {
						$args_array = preg_split("%,%", $args); 
						foreach ($args_array as $arg_array) 
							array_push($func_args, $arg_array);
					} else {
						$func_args = $args;
					}
					$value = call_user_func_array($event_datablock['function'], $func_args);	//	 More Complex
					if (is_object($value))
						return $value->{$event_datablock['object']};
					else
						return $value;
				} else {
					return $event_datablock['value'];	// Simple Value Used
				}
			}
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
		$Woopra = new Woopra;
		$this->default_events = $this->register_events();
		$all_events = $this->default_events;
		foreach ($all_events as $event_name => $data) {			
			add_action( $data['action'], 			array(&$this, 'process_events') );
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
		$Woopra = new Woopra;
		$this->default_events = $this->register_events();
		$all_events = $this->default_events;
		foreach ($all_events as $event_name => $data) {	
			add_action( $data['action'], 			array(&$this, 'admin_process_events') );
		}
	}
	
	/**
	 * The handler for processing events.
	 * @since 1.4.1
	 * @return 
	 * @param object $args
	 */
	function admin_process_events(&$args) {
		$current_event = current_filter();
		echo $current_event . ' / ' . $args;
		return $this->process_event($current_event, $args);
	}
	
}

?>
