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
			$this->register_events();
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
		 			case "search_query":
		 				if (isset($_GET["s"])) {
							$this->woopra->track("search", array("query" => $_GET["s"]), true);
						}
		 			break;
		 			case "comment_post":
		 				add_action('comment_post', array(&$this, 'track_comment'), 10, 1);
		 			break;
		 			case "signup":
		 				add_action('user_register', array(&$this, 'track_signup'), 10, 1);
		 			break;
		 		}
	 		}
		}
	 }

	 /**
	 * Tracks a signup
	 * @return none
	 */
	 function track_signup($user_id) {
	 	$user = get_user_by('id', $user_id);
	 	if ( !($user instanceof WP_User) ) {
			return;
		}
		$user_details = array();
		if ($user->has_prop("user_firstname")) {
			$user_details["first_name"] = $user->user_firstname;
		}
		if ($user->has_prop("user_lastname")) {
			$user_details["last_name"] = $user->user_lastname;
		}
		if ($user->has_prop("user_firstname") && $user->has_prop("user_lastname")) {
			$user_details["full_name"] = $user->user_firstname . ' ' . $user->user_lastname;
		}
		if ($user->has_prop("user_email")) {
			$user_details["email"] = $user->user_email;
		}
		if ($user->has_prop("user_login")) {
			$user_details["username"] = $user->user_login;
		}
		$this->woopra->track('signup', $user_details, true);
	}
	 
	 /**
	 * Tracks a comment
	 * @return none
	 */
	 function track_comment($comment_id) {
	 	$comment_details = array();
	 	$comment = get_comment($comment_id);
	 	$comment_details["author"] = $comment->comment_author;
	 	$comment_details["author_email"] = $comment->comment_author_email;
	 	if ($comment->comment_author_url) {
	 		$comment_details["author_website"] = $comment->comment_author_url;
	 	}
	 	$comment_details["content"] = $comment->comment_content;
	 	if (!is_user_logged_in() && $this->get_option('auto_tagging')) {
	 		$user_details = array();
	 		$user_details["name"] = $comment->comment_author;
	 		$user_details["email"] = $comment->comment_author_email;
			$this->woopra->identify($user_details);
		}
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
			//	Identify with woopra
			if ($this->get_option('auto_tagging')) {
				$this->woopra->identify($this->user);
			}
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
			$this->woopra->track("pv", $page_data)->js_code();
		} else {
			$this->woopra->track()->js_code();
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
		
		if ($this->get_option('trackas')) {
			$this->config["domain"] = $this->get_option('trackas');
		}
		if ($this->get_option('use_timeout')) {
			$this->config["idle_timeout"] = $this->get_option('timeout')*1000;
		}
		$this->config["hide_campaign"] = $this->get_option('hide_campaign') == 1 ? true : false;
		$this->config["app"] = "wordpress";
	}
	
}
?>
