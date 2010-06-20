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
	 * What are the current event's going on?
	 * @since 1.4.3
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
		
		if (!isset($_SESSION))
			@session_start();
		
		Woopra::__construct();
		
		// Load Event Processing
		$this->event = new WoopraEvents();
		
		//	Frontend Actions
		add_action(	'wp',						array(&$this, 'woopra_detect')					);

		add_action(	'admin_head',				array(&$this, 'woopra_detect'),				10	);

		//	Events Actions
		$this->add_events();
		if ($this->get_option('process_event'))
			$this->event->current_event = $_SESSION['woopra']['events'];
		
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget'), 			10	);
		if ($this->get_option('track_admin'))
			add_action( 'admin_footer',				array(&$this, 'woopra_widget'),			10	);
		
	}

	/**
	 * Add Actions to Filter List
	 * @since 1.4.3
	 * @return true if suscess full.
	 */
	function add_events() {
		
		$all_events = $this->event->default_events;
		$event_status = $this->get_option('woopra_event');		
		
		foreach ($all_events as $event_name => $data) {
			$action_name = isset($data['action']) ? $data['action'] : $data['filter'];
			$is_action = isset($data['action']) ? true : false;
			
			if ( ($event_status[$action_name] == 1) && ($is_action) ) {
				add_action( $action_name, array(&$this, 'process_events') );
					
				if ( !has_action( $action_name, array(&$this, 'process_events') ) )
					$this->fire_error( 'action_could_not_be_added' , array( 'message' => _('This action (<strong>%s</strong>) could not be added to the system. Please disable tracking of this event and report this error.'), 'values' => $action_name, 'debug' => true) );

			} elseif ( ($event_status[$action_name] == 1) && ($is_action == false) ) {
				add_filter( $action_name, array(&$this, 'process_filter_events') );
					
				if ( !has_filter( $action_name, array(&$this, 'process_filter_events') ) )
					$this->fire_error( 'action_could_not_be_added' , array( 'message' => _('This action (<strong>%s</strong>) could not be added to the system. Please disable tracking of this event and report this error.'), 'values' => $action_name, 'debug' => true) );
				
			}
		}
		
	}

	/**
	 * The handler for processing events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function process_events($args) {
		$current_event = current_filter();
		
		if ( !isset($current_event) )
			$this->fire_error( 'current_filter_no_name' , array( 'message' => _('There is no name with this event.'), 'debug' => true) );
		$this->check_error( 'current_filter_no_name' );
		
		return $this->add_event($current_event, $args);
	}

	/**
	 * The handler for processing filter events.
	 * @since 1.4.1
	 * @return boolean
	 * @param object $args
	 */
	function process_filter_events($args) {
		$current_event = current_filter();
		
		if ( !isset($current_event) )
			$this->fire_error( 'current_filter_no_name' , array( 'message' => _('There is no name with this event.'), 'debug' => true) );
		$this->check_error( 'current_filter_no_name' );
		
		$this->add_event($current_event, $args);
		return $args;
	}

	/**
	 * Process Event
	 * @since 1.4.1
	 * @return none
	 * @param object $event
	 * @param object $args
	 */
	function add_event($event, $args) {
		if (!isset($_SESSION))
			@session_start();

		$_SESSION['woopra']['events'][$event] = $args;
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
		echo "\r\n<!-- Woopra Analytics Code -->\r\n";
		echo "<script type=\"text/javascript\" src=\"//static.woopra.com/js/woopra.v2.js\"></script>\r\n";
		
		if ($this->get_option('auto_tagging') && !empty($this->woopra_visitor['name'])) {
			$woopra_tracker .= "woopraTracker.addVisitorProperty('name','" . js_escape($this->woopra_visitor['name']) . "');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('email','" . js_escape($this->woopra_visitor['email']) . "');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('avatar','". urlencode("http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png") . "');\r\n";
		}
		if ($this->get_option('use_timeout')) {
			$woopra_tracker .= "woopraTracker.setidletimeout(".($this->get_option('timeout')*1000).");\r\n";
		}
		
		echo "<script type=\"text/javascript\">\r\n";
		echo $woopra_tracker; 
                echo "woopraTracker.track();\r\n";
		echo "</script>\r\n";
		
		if ( is_array($this->event->current_event) ) {
			$i=0;
			echo "<script type=\"text/javascript\">\r\n";
			foreach ($this->event->current_event as $event_name => $event_value) {
			echo "var we$i = new WoopraEvent(\"".js_escape($event_name)."\");\r\n";
			$this->event->print_javascript_events($i);
			echo "we$i.fire();\r\n";
			$i++;
			}
			echo "</script>\r\n";
		}
		echo "<!-- End of Woopra Analytics Code -->\r\n\r\n";
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
