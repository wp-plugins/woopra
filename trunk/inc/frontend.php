<?php
/**
 * WoopraFrontend Class for Woopra
 * This class contains all functions and actions required for Woopra to track Wordpress events and outputs the frontend code.
 */

class WoopraFrontend extends Woopra {

	var $user;
	
	var $config;
	
	function __construct() {		
		
		//Construct parent class
		Woopra::__construct();
		
		//Load configuration
		$this->config = array();
		$this->woopra_config();
		
		// Load PHP SDK
		$this->woopra = new WoopraTracker($this->config);
		
		// Load Event Processing
		$this->events = new WoopraEvents();
		
		
		// If there is no cookie, set one before the headers are sent
		add_action('init', array(&$this->woopra, 'set_woopra_cookie'));
		
		//Detect Wordpress user
		$this->user = array();
		add_action('init', array(&$this, 'woopra_detect'));
		
		//If event tracking is turned on, process events
		if ($this->get_option('process_event')) {
			add_action('init', array(&$this, 'register_events'));
		}
		
		if ($this->get_option('other_events')) {
			add_action("woopra_track", array(&$this->woopra, "track"), 10, 3);
		}
		
		//Register front-end tracking
		add_action('init', array(&$this, 'set_tracker'));
		
	}
	
	/**
	 * Registers events
	 * @return none
	 */
	 function register_events() {
	 	$all_events = $this->events->default_events;
	 	$event_status = $this->get_option('woopra_event');
	 	foreach ($all_events as $event_name => $data) {
	 		if (($event_status[$data['action']] == 1)) {
		 		switch($data['action']) {
		 			case "the_search_query":
		 				if (isset($_GET["s"])) {
							$this->woopra->track("search", array("User searched" => $_GET["s"]), true);
						}
		 			break;
		 			case "comment_post":
		 				add_action('comment_post', array(&$this, 'track_comment'));
		 			break;
		 		}
	 		}
		}
	 }
	 
	 /**
	 * Tracks a comment
	 * @return none
	 */
	 function track_comment($args) {
	 	$comment_details = array();
	 	$comment = get_comment($args);
	 	$comment_details["author"] = $comment->comment_author;
	 	$comment_details["content"] = $comment->comment_content;
	 	$this->woopra->track("comment", $comment_details, true);
	 }
	
	/**
	 * Loads Wordpress User & identifies it
	 * @return none
	 */
	function woopra_detect() {
		
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$this->user['name'] = $current_user->display_name;
			$this->user['email'] = $current_user->user_email;
			if (current_user_can('manage_options')) {
				$this->user['admin'] = true;
			}
		}
		//	Identify with woopra
		if ($this->get_option('auto_tagging')) {
			$this->woopra->identify($this->user);
		}
	}
	
	function track() {
		if ($this->get_option('track_author') && is_single()) {
			$page_data = array();
	        wp_reset_query();
	        global $post;
	        $myvar = get_the_category($post->ID);
	        $myvar = $myvar[0]->cat_name;
			$page_data["author"] = js_escape(get_the_author_meta("display_name",$post->post_author));
			$page_data["category"] = isset($myvar) ? js_escape($myvar) : "Uncategorized";
			$this->woopra->track("pv", $page_data)->woopra_code();
		} else {
			$this->woopra->track()->woopra_code();
		}
	}
	
	/**
	 * Outputs JS tracker
	 * @return none
	 */
	function set_tracker() {
		global $post;
		if (current_user_can('manage_options') && ! $this->get_option('ignore_admin')) {
			if($this->get_option('track_admin')) {
				add_action('admin_footer', array(&$this, 'track'), 10);
			} else {
				add_action('wp_head', array(&$this, 'track'), 10);
			}
		} elseif(!current_user_can('manage_options')) {
			add_action('wp_head', array(&$this, 'track'), 10);
		}
	}
	
	
		
	/**
	* Loads Woopra configuration
	* @return none
	*/
	function woopra_config() {
		
		if ($this->get_option('use_trackas') && $this->get_option('trackas')) {
			$this->config["domain"] = $this->get_option('trackas');
		}
		if ($this->get_option('use_timeout')) {
			$this->config["idle_timeout"] = $this->get_option('timeout')*1000;
		}
		$this->config["hide_campaign"] = $this->get_option('hide_campaign') ? "true" : "false";
	}
	
}
?>
