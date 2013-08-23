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
		
		add_action( 'wp_head', 					array(&$this, 'woopra_widget'), 			10	);
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
	
		//Check options and set booleans (much cleaner)
		
		$auto_tagging = false;
		$use_timeout = false;
		$event_array = false;
        $use_trackas = false;
		//$author_category = false;
        
		if ($this->get_option('auto_tagging') && !empty($this->woopra_visitor['name'])) {
			$auto_tagging = true;
			$visitor_name = $this->woopra_visitor['name'];
			$visitor_email = $this->woopra_visitor['email'];
		    $visitor_avatar = "http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png"; 		
		}
        
        if ($this->get_option('use_trackas') && $this->get_option('trackas')) {
			$use_trackas = true;
			$trackas = $this->get_option('trackas');
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
		


		// Setup tracker config
		$woopra_config = array();
        
        $trackas_settings = "";
        if ($use_trackas) {
			$woopra_config['domain'] = $trackas;
		}
        
		$custom_settings = "";
		if ($use_timeout) {
			$woopra_config['idle_timeout'] = $set_timeout;
		}
		
		$woopra_js_config = "woopra.config(".json_encode($woopra_config).");";
		
		// Setup visitor data
		$woopra_js_identify = '';
	 	if ($auto_tagging) {
			$woopra_identity = array();
			$woopra_identity['name'] = $visitor_name;
			$woopra_identity['email'] = $visitor_email;
			$woopra_identity['avatar'] = $visitor_avatar;
			$woopra_js_identify = "woopra.identify(".json_encode($woopra_identity).");";
		}
		
		// Setup events data
		
		$woopra_js_pv = '';
		if ($taset) {
			$woopra_js_pv .= "woopra.track('pv', {";
			$woopra_js_pv .= "title: document.title, url: window.location.pathname, author: ".json_encode($the_author).", category: ".json_encode($the_category);
			$woopra_js_pv .= "})";
		} else {
			$woopra_js_pv .= "woopra.track();";
		}
		
		$woopra_events = array();
		
		if ($event_array) {
			foreach($my_event as $event_name => $event_value) {
			
				$woopra_event_name = '';
				$woopra_event = array();
			
                if($event_name == 'comment' && $event_value['other'][0] == 'comment_id') {
                	$woopra_event_name = $event_value['name'];
                    $commentDetails = $this->event->get_comment_details($event_value['other'][1]);
                    $woopra_event['content'] = $commentDetails->comment_content;
                    $woopra_event['commentauthor'] = $commentDetails->comment_author;
                }
                else {
                	
	                $woopra_event_name = $event_value['name'];
	                $woopra_event[$event_value['other'][0]] = $event_value['other'][1]; 
                }
				
				$woopra_events[$woopra_event_name] = $woopra_event;
			}
		}
		
		$woopra_js_events = '';
		foreach ($woopra_events as $woopra_event_name => &$woopra_event_data) {
			$woopra_js_events .= "woopra.track(".json_encode($woopra_event_name).", ".json_encode($woopra_event_data).");\r\n	";
		}
		
		
		?>
		
		<!-- Woopra code starts here -->
		<script>
		(function(){
		var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e)
		})("woopra");
		
		<?php echo $woopra_js_config; ?>
		
		<?php echo $woopra_js_identify; ?>
		
		<?php echo $woopra_js_pv; ?>
		
		<?php echo $woopra_js_events; ?>
		
		</script>
		<!-- Woopra code ends here -->
		
		<?php
		
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
