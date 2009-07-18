<?php
/**
 * Woopra is the world’s most comprehensive, information rich, easy to use, real-time Web
 * tracking and analysis application. And it’s free!
 * 
 * Woopra delivers the richest library of visitor statistics in the industry, and does it
 * within an unmatched user interface designed to be aesthetically pleasing as well as highly
 * intuitive. But Woopra is more than simply statistics.
 * 
 * Our client application is built as a framework for expansion, complete with an open API,
 * plugin capability, and a wide range of additional feature / functionality currently being
 * readied for deployment. We invite you to sign up and experience the power of Woopra first hand!
 * 
 * 
 * 
 * Open Flash Charts 2 is copyrighted and created by John Glazebrook <http://teethgrinder.co.uk>
 * It is licenced as LGPL. You can view the terms of the object helper files and the flash file
 * itself here: http://teethgrinder.co.uk/open-flash-chart-2/
 * 
 *
 * @author Elie El Khoury <elie@woopra.com> and Shane Froebel <shane@bugssite.org>
 * @version 1.4.1
 * @copyright 2007-2009
 * @package woopra
 */

/**
 * Define the Woopra Plugin Version
 * @since 1.4.1
 * @return none
 */
DEFINE ('WOOPRA_VERSION', '1.4.1');		// MAKE SURE THIS MATCHES THE VERSION ABOVE!!!!

/*

**************************************************************************

Plugin Name:  Woopra
Plugin URI:   http://wordpress.org/extend/plugins/woopra/
Version:      1.4.1
Description:  This plugin adds Woopra's real-time analytics to any WordPress installation.  Simply sign up at Woopra.com, then activate the plugin!
Author:       <a href="http://www.ekhoury.com">Elie El Khoury</a>, <a href="http://bugssite.org">Shane Froebel</a>
Author URI:   http://www.woopra.com/

**************************************************************************/

class Woopra {

	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $version = WOOPRA_VERSION;

	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $options;

	/**
	 * @since 1.4.1
	 * @var string 
	 */
	var $woopra_vistor;

	/** DEBUG VAR **/
	var $debug = false;

	/**
	 * Compatability for PHP 4.
	 * @since 1.4.1
	 * @return none
	 */
	function Woopra() {
		$this->__construct();
	}

	/**
	 * Main Contructor Class
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		//	Load Options
		$this->options = get_option('woopra');		
	}
	
	/**
	 * Get the full URL to the plugin
	 * @since 1.4.1
	 * @return string
	 */
	function plugin_url() {
		$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
		return $plugin_url;
	}
	
	/**
	 * Get an option from the array.
	 * @since 1.4.1
	 * @return none
	 * @param object $option
	 */
	function get_option($option) {
		if (isset($this->options[$option]))
			return $this->options[$option];
		else
			return false;
	}

	/** THIS IS CUSTOM CODE THAT CAN BE DELETED LATER ON **/

	/**
	 * Debug code. Has to be turned on for it to work.
	 * @return none
	 * @see $this->debug
	 */
	function debug($string, $exit = false) {
		if ($this->debug)
			echo $string . "<br/>";
		if ($exit)
			exit;
	}
}

/**
 * Start the WoopraFrontend or WoopraAdmin Class
 * If we are in the admin load the admin view. Always run the frontend code since
 * we add the ability to track administrators in the admin section.
 */
require_once( dirname(__FILE__) . '/inc/frontend.php' 		);
require_once( dirname(__FILE__) . '/inc/events.php' 		);
if (is_admin()) {
	require_once( dirname(__FILE__) . '/inc/admin.php' 		);
	require_once( dirname(__FILE__) . '/inc/analytics.php' 	);
	require_once( dirname(__FILE__) . '/inc/chart.php' 		);
	require_once( dirname(__FILE__) . '/inc/render.php' 	);
	$WoopraAdmin = new WoopraAdmin();
}
//	Always Run the Front End Code
$WoopraFrontend = new WoopraFrontend();

?>
