<?php
/**
 * WoopraRender Class for Woopra
 *
 * This class contains all event related code for rendering the data.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage render
 */
require_once( 'xml.php' );
class WoopraRender {

	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraRender () {
		$this->__construct();
	}

	/**
	 * Woopra Render
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
				
	}

}

$WoopraRender = new WoopraRender;
$WoopraRender->generate_data();

?>