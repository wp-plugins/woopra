<?php
/**
 * WoopraChart Class for Woopra
 *
 * This class contains all functions and actions required
 * for Woopra to work on the backend of WordPress.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage chart
 */
class WoopraChart {
	
	/**
	 * Title of the chart
	 * @since 1.4.1
	 * @var string
	 */
	var $title = null;
	
	/**
	 * Type of chart we are going to draw.
	 * @since 1.4.1
	 * @var string
	 */
	var $type = null;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraChart() {
		$this->__construct();
	}
	
	/**
	 * Chart Contructor Class
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		
	}
	
}