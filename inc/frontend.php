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
		Woopra::__construct();
		
		//	Frontend Actions
		add_action(	'template_redirect',		array(&$this, 'woopra_detect')					);
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget'), 			10	);
		if ($this->get_option('woopra_track_admin'))
			add_action( 'admin_footer',				array(&$this, 'woopra_widget'), 			10	);
		
		//	Process Events
		$WoopraEvents = new WoopraEvents_Frontend;
		//	@todo This is where we should be getting the events that are 'processed' and storing them
		//	in an array so we can add them to the javascript code widget.
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
	 * Create the Javascript Code at the Bottom
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_widget() {
		
		if (!$this->woopra_status())
			return;
			
		echo "<!-- Woopra Analytics Code -->\n";
		echo "<script type=\"text/javascript\">\r\n";
		echo "var woopra_visitor = new Array();\r\n";
		echo "var woopra_event = new Array();\r\n";

		if ($this->get_option('auto_tagging') || !empty($this->woopra_visitor['name'])) {
			echo "woopra_visitor['name'] = '" . js_escape($this->woopra_visitor['name']) . "';\r\n";
			echo "woopra_visitor['email'] = '" . js_escape($this->woopra_visitor['email']) . "';\r\n";
			echo "woopra_visitor['avatar'] = 'http://www.gravatar.com/avatar.php?gravatar_id=" . md5(strtolower($this->woopra_visitor['email'])) . "&size=60&default=http%3A%2F%2Fstatic.woopra.com%2Fimages%2Favatar.png';\r\n";
		}
		
		// @todo Add Event Related Code
		
		echo "</script>\r\n";
		echo "<script src=\"http://static.woopra.com/js/woopra.js\" type=\"text/javascript\"></script>";
		echo "\n<!-- End of Woopra Analytics Code -->";
	}

	/**
	 * How Woopra Detects Vistors
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_detect() {
		global $userdata, $current_user;	//	Needed if the user is logged in.
		
		//	Check to see if the user has a cookie.. if so... get it!
		$author = str_replace("\"","\\\"",$_COOKIE['comment_author_'.COOKIEHASH]);
		$email = str_replace("\"","\\\"",$_COOKIE['comment_author_email_'.COOKIEHASH]);
		if (!empty($author)) {
			$this->woopra_visitor['name'] = $author;
			$this->woopra_visitor['email'] = $email;
		}
	
		// Wait? The user is logged in. Get that data instead.
		if (is_user_logged_in()) {
			get_currentuserinfo();
			$this->woopra_visitor['name'] = $current_user->display_name;
			$this->woopra_visitor['email'] = $current_user->user_email;
		}
	}
	
}