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
if ($action_name == "the_search_query") $action_name = "get_search_query";
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
		if (empty($args) || $args == NULL) return $args;
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
		if ($this->get_option('process_event'))
			$this->event->current_event = $_SESSION['woopra']['events'];
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
	
		//Check options and set booleans (much cleaner)
		
		$auto_tagging = false;
		$use_timeout = false;
		$event_array = false;
		//$author_category = false;

		if ($this->get_option('auto_tagging') && !empty($this->woopra_visitor['name'])) {
			$auto_tagging = true;	
			$escaped_name = js_escape($this->woopra_visitor['name']);
			$escaped_email = js_escape($this->woopra_visitor['email']);
		        $escaped_avatar = urlencode("http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png"); 		
		}

		if ($this->get_option('use_timeout')) {
			$use_timeout = true;
			$set_timeout = $this->get_option('timeout')*1000;
		}
		if ( is_array($this->event->current_event) ) {
                        $event_array = true;
			$i=0;
			foreach ($this->event->current_event as $event_name => $event_value) {	
				$mydef = "";			
			switch($event_name) {
				case "get_search_query":
					$mydef = "search";
				break;
				case "comment_post":
					$mydef = "comment";				
				break;
				default:
					$mydef = $event_name;
			}
                        $my_event[$i]['name'] = js_escape($mydef);
			$my_event[$i]['other'] = $this->event->print_javascript_events($i);
                        $i++;
                        }
                }
		$taset = false;	
		if ($this->get_option('track_author')) {
                      	wp_reset_query();
                        if (is_single()) {
			        global $post;
                                $myvar = get_the_category($post->ID);
                                $myvar = $myvar[0]->cat_name;
				$the_author = js_escape(get_the_author_meta("display_name",$post->post_author));
				$the_category = js_escape($myvar);
				$taset = true;
			}
		}

		echo "<script type=\"text/javascript\">\r\n";
		$build_actions = "var woo_actions = [";
		$builded = false;
		if ($event_array) {
			foreach($my_event as $event_name => $event_value) {
			$build_actions .= "{type:'event',name:'".$event_value['name']."',".$event_value['other'][0].":'".$event_value['other'][1]."'},";
			}
			$builded = true;
		}
		if ($taset)
		{
		$build_actions .= "{type:'pageview',title:document.title,url:window.location.pathname,author:'$the_author',category:'$the_category'}";
		$builded = true;		
		}
		if (substr($build_actions,-1) == ",") $build_actions = substr($build_actions,0,-1)."];\r\n"; else $build_actions.="];\r\n";
		
		$build_visitor = "";
	 	if ($auto_tagging) {
		$build_visitor = "var woo_visitor={name:'$escaped_name',email:'$escaped_email',avatar:'$escaped_avatar'};\r\n";	
		}
		
		$custom_settings = "";
		if ($use_timeout) {
		$custom_settings = "var woo_settings={idle_timeout:'$set_timeout'};\r\n";
		}
	
		if ($builded) echo $build_actions;
		echo $build_visitor.$custom_settings;	
	
		$toout = "(function(){\r\nvar wsc=document.createElement('script');\r\nwsc.type='text/javascript';\r\nwsc.src=document.location.protocol+'//static.woopra.com/js/woopra.js';";
		$toout.="\r\nwsc.async=true;\r\nvar ssc = document.getElementsByTagName('script')[0];\r\nssc.parentNode.insertBefore(wsc, ssc);})();\r\n</script>\r\n";
		echo $toout;
		 /* else {
		echo "<script type=\"text/javascript\" src=\"http://static.woopra.com/js/woopra.v2.js\"></script>\r\n";
		
		if ($auto_tagging) {
			$woopra_tracker .= "woopraTracker.addVisitorProperty('name','$escaped_name');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('email','$escaped_email');\r\n";
			$woopra_tracker .= "woopraTracker.addVisitorProperty('avatar','$escaped_avatar');\r\n";
		}

		if ($use_timeout) {
			$woopra_tracker .= "woopraTracker.setIdleTimeout($set_timeout);\r\n";
		}

		if ($taset)
			$woopra_tracker .="woopraTracker.track(window.location.pathname, document.title, {author: '$the_author', category: '$the_category'});\r\n";		
		echo "<script type=\"text/javascript\">\r\n";
		echo $woopra_tracker; 
                if (!$taset) echo "woopraTracker.track();\r\n";
		echo "</script>\r\n";
		
		if ($event_array) {
			$i=0;
			echo "<script type=\"text/javascript\">\r\n";
			foreach ($my_event as $event_name => $event_value) {
			echo "var we$i = new WoopraEvent(\"".$event_value['name']."\");\r\n";
			//$this->event->print_javascript_events($i);
			echo "we$i.addProperty(\"" . $event_value['other'][0] . "\",\"" . $event_value['other'][1]  . "\");\r\n";
			echo "we$i.fire();\r\n";
			$i++;
			}
			echo "</script>\r\n";
		}
	}*/
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
		
		if (current_user_can('manage_options'))
			$this->woopra_visitor['admin'] = true;
		
	}
	
}
?>
