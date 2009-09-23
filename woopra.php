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
 * Other credits:
 * 
 * ViperBond007, DD32, and sivel are to credit by helping this plugin envole into a very
 * pretty PHP class format. 
 * 
 * Open Flash Charts 2 is copyrighted and created by John Glazebrook <http://teethgrinder.co.uk>
 * It is licenced as LGPL. You can view the terms of the object helper files and the flash file
 * itself here: http://teethgrinder.co.uk/open-flash-chart-2/
 * 
 *
 * @author Elie El Khoury <elie@woopra.com> and Shane Froebel <shane@woopra.com>
 * @version 1.4.3
 * @copyright 2007-2009
 * @package woopra
 */

/**
 * Define the Woopra Plugin Version
 * @since 1.4.1
 * @return none
 */
DEFINE ('WOOPRA_VERSION', '1.4.3');		// MAKE SURE THIS MATCHES THE VERSION ABOVE!!!!

/*

**************************************************************************

Plugin Name:  Woopra
Plugin URI:   http://wordpress.org/extend/plugins/woopra/
Version:      1.4.3
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

	/**
	 * @var
	 */
	var $error;

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
		$this->error = new WP_Error();
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
	
	/**
	 * 
	 * @param object $code [optional]
	 * @param object $args [optional]
	 * @return 
	 */
	function fire_error($code = '', $args = '') {
		$defaults = array(
			'code' => 'generic_error', 'message' => _('Woopra: An unknown error occured.'), 
			'values' => null, 'debug' => 0
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$this->error->add($code, sprintf( _($message), $values), $debug );
	}
	
	/**
	 * 
	 * @return 
	 */
	function check_error($code) {
		if ( (is_wp_error($this->error) && (count($this->error->get_error_messages()) > 0)) ) {
			foreach ($this->error->get_error_messages() as $message) {
				$output .= $message . "<br/>";
			}
			$this->display_error($output, $this->error->error_data[$code]);
		}
	}
	
	/**
	 * 
	 * @param object $output
	 * @param object $hide_debug [optional]
	 * @return 
	 */
	function display_error($output, $show_debug) {
		
		ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean(); 
		
		$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1); 
		$trace = preg_replace ('/^#(\d+)/me', '\'<br/><strong>#</strong>\' . ($1 - 1)', $trace);
		
		if ($show_debug)
			$trace_output = '<br />Please report the following as well on the forums: <br/> <small>' . $trace . '</small>';
		
		wp_die($output . $trace_output);
		
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
	require_once( dirname(__FILE__) . '/inc/xml.php' 		);
	require_once( dirname(__FILE__) . '/inc/analytics.php' 	);
	require_once( dirname(__FILE__) . '/inc/chart.php' 		);
	require_once( dirname(__FILE__) . '/inc/render.php' 	);
	$WoopraAdmin = new WoopraAdmin();
}
//	Always Run the Front End Code
$WoopraFrontend = new WoopraFrontend();

?>
