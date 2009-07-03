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
 * @author Elie El Khoury <elie@woopra.com> and Shane Froebel <shane@bugssite.org>
 * @version 1.4.1
 * @copyright 2007-2009
 * @package woopra
 */

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
	 * Options array containing all options for this plugin
	 * @since 1.4.1
	 * @var string
	 */
	var $options;

	/**
	 * Current vistor information on the site are stored in this varabile.
	 * @var string 
	 * @since 1.4.1
	 */
	var $woopra_vistor;

	/**
	 * Compatability for PHP 4.
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
	 * Get an option from the array.
	 * @return 
	 * @param object $option
	 */
	function get_option($option) {
		if (isset($this->options[$option]))
			return $this->options[$option];
		else
			return false;
	}

	/** THIS IS CUSTOM CODE THAT CAN BE DELETED LATER ON **/

	/** DEBUG VAR **/
	var $debug = true;
	/**
	 * Debug code. Has to be turned on for it to work.
	 * @return none
	 * @see $this->debug
	 */
	function debug($string) {
		if ($this->debug)
			echo $string . "<br/>";
	}
}

/**
 * Instantiate the WoopraFrontend or WoopraAdmin Class
 * If we are in the admin load the admin view else load the frontend code.
 */
if (is_admin()) {
	require_once( dirname(__FILE__) . '/inc/admin.php' );
	$WoopraAdmin = new WoopraAdmin();
}
//	Always Run the Front End Code
require_once( dirname(__FILE__) . '/inc/frontend.php' );
$WoopraFrontend = new WoopraFrontend();

?>