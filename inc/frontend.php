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
	 * Hold the FrontEnd Localized Data String
	 * @since 1.5.0
	 * @var array
	 */
	var $local = array();
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 * @constructor
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
		add_action(	'wp',						array(&$this, 'woopra_detect')					);
		add_action(	'admin_head',				array(&$this, 'woopra_detect'),				10	);		
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget'), 			10	);
		add_action( 'init',						array(&$this, 'init')							);
		if ($this->get_option('track_admin'))
			add_action( 'admin_footer',				array(&$this, 'woopra_widget'),			10	);
		
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
	
	function init() {
		wp_enqueue_script( 'woopra-tracking',	$this->plugin_url() . '/js/jquery.tracking.js',		array('jquery'), '20100201', true );
		
		//	Set jQuery Options
		if ( $this->get_option('use_subdomain') )
			$this->create_localize( array('rootDomain'		=>	$this->get_option('sub_domain')		)		);
		
		if ( $this->get_option('use_timeout') )
			$this->create_localize( array('setTimeoutValue'	=>	($this->get_option('timeout')*1000)	)		);
		
		//	For showing in the Woopra Desktop Application
		$this->create_localize( array('name'		=>	__('Name'))		);
		$this->create_localize( array('email'		=>	__('email')) 	);
		
		//	Output jQuery Options
		wp_localize_script( 'woopra-tracking', 'woopraFrontL10n', $this->local );
	}
	
	/**
	 * Create the localized array string.
	 * @since 1.5.0
	 * @param $array
	 * @return none
	 */
	function create_localize($array) {
		$this->local = array_merge($array, $this->local );
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

		/*** JQUERY CODE -- DO NOT MODFIY ***/
		echo "\r\n<!-- Woopra Analytics Code -->\r\n";
		echo "<script type=\"text/javascript\">\r\n";
		echo "jQuery.trackWoopra({ name : '" . js_escape($this->woopra_visitor['name']) . "', email : '" . js_escape($this->woopra_visitor['email']) . "', avatar : '" . urlencode("http://www.gravatar.com/avatar/" . md5(strtolower($this->woopra_visitor['email'])) . "&amp;size=60&amp;default=http://static.woopra.com/images/avatar.png") . "' } );\r\n";
		echo "</script>\r\n";
		echo "<!-- End of Woopra Analytics Code -->\r\n\r\n";
		/*** JQUERY CODE -- DO NOT MODFIY ***/
		
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