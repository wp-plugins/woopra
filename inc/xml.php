<?php
/**
 * WoopraXML Class for Woopra
 *
 * This class contains all event related code for acessing the XML data.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage xml
 */
class WoopraXML {
	
	/** GLOBALS NEED TO BE SET BEFORE QUERY **/
	
	/**
	 * API Key
	 * @since 1.4.1
	 * @var string
	 */
	var $api_key = null;
	
	/**
	 * API Page
	 * @since 1.5.0
	 * @var string
	 */
	var $api_page = null;
	
	/**
	 * Hostname of our site.
	 * @since 1.4.1
	 * @var string
	 */
	var $hostname = null;
	
	/** XML PROCESSING **/	
	
	/**
	 * Parser object for XML-PHP
	 * @since 1.4.1
	 * @var object
	 */
	var $parser = null;
	
	/**
	 * Area where we are going to be placing the data. 
	 * @since 1.4.2
	 * @var string
	 */
	var $area = null;
	
	/** DATA **/
	
	/**
	 * Holding Args
	 * @since 1.5.0
	 * @var array
	 */
	var $args = null;
	
	/**
	 * Data from XML
	 * @since 1.4.1
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Which index are we at?
	 * @since 1.4.1
	 * @var int
	 */
	var $counter = 0;
	
	/**
	 * Current tag that we are storing..
	 * @since 1.4.1
	 * @var string
	 */
	var $current_tag = null;
	
	/**
	 * Index created
	 * @since 1.4.1
	 * @var boolean
	 */
	var $index_created = false;
	
	/**
	 * Has data been found?
	 * @since 1.4.1
	 * @var string
	 */
	var $found_data = false;
	
	/**
	 * Should we be recording?
	 * @since 1.5.0
	 * @var string
	 */
	var $record = false;
	
	/**
	 * Are we fining by hour childern?
	 * @since 1.5.0
	 * @var boolean
	 */
	var $hour_Childern = false;
	
	/**
	 * Are we fining by day childern?
	 * @since 1.5.0
	 * @var boolean
	 */
	var $day_Childern = false;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraXML() {
		$this->__construct();
	}
	
	/**
	 * Woopra XML
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		//	Nothing to do here...
	}
	
	/**
	 * Initialization of the Process Check
	 * @since 1.4.1
	 * @return boolean
	 */
	function init() {
		if (!$this->api_key)	//	Check to see if site key has not been set.
			return new WP_Error('apikey-empty', sprintf( __('%s: Sorry. The API Key was not filled out correctly.'), 'WoopraXML::process_data(146)' ) );
		
		if (!$this->api_page)	//	Check to see if api page has not been set.
			return new WP_Error('apipage-empty', sprintf( __('%s: Sorry. The API Page was not filled out correctly.'), 'WoopraXML::process_data(149)' ) );
			
		return true;
	}
	
	/**
	 * Set the XML File Location
	 * 
	 * @since 1.4.2
	 * 
	 * @param object $area
	 * @param object $xml_data
	 * @return 
	 */
	function set_xml($area, $xml_data) {
		//	Where are we processing the data?
		$this->area = $area;
		switch ($area) {
			case 'render': {
				$_xml_data = array ('website' => $this->hostname, 'apiKey' => $this->api_key);
				$this->args = array_merge($_xml_data, $xml_data);
				break;
			}
		}
		return true;
	}
	
	/**
	 * Process the XML File
	 * 
	 * @uses IXR_Woopra
	 * 
	 * @since 1.4.1
	 * @return boolean
	 */
	function process_data() {
		/**
    	 * This is going to have a check in the activation to see if the server can handle this.
    	 * Some server disable this function and it either must be enabled or allowed.
    	 */
		
		/**
		 * Process SOAP Call
		 * Server support native SOAP requests. Use this instead.
		 * 
		 * Woopra API: http://www.woopra.com/docs/api/
		 * Tester URL: http://api.woopra.com/v0/Analytics?Tester
		 * WSDL URL: http://api.woopra.com/v0/Analytics?WSDL
		 * 
		 */
		
		//	** SET THE CONNECTION **/
		$woopra_soap = new SoapClient('http://api.woopra.com/v0/Analytics?WSDL', array('trace' => 1));
		//	** POST THE RESPONSE **/
		if ( is_soap_fault( $woopra_soap->{$this->api_page}($this->args) ) ) {
			return new WP_Error('soap-error', sprintf( __('%s: Sorry. There was a SOAP connection error to the Woopra API. Please try again later.'), 'WoopraXML::process_data(205)' ) );
		}
		//	** STORE THE DATA **/
		$woopra_request_data = $woopra_soap->__getLastResponse();
		//	** REMOVE THE CONNECTION **/
		unset($woopra_soap);
		
		//	Set the XML Parser Object
		$this->parser = xml_parser_create("UTF-8");
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'start_xml', 'end_xml');
		xml_set_character_data_handler($this->parser, 'char_xml');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		
		//	** READ THE XML RETURN! **/
		if ( !xml_parse($this->parser, $woopra_request_data) ) {
			unset($woopra_request_data, $this->parser);
			return new WP_Error('xml-parse-error', sprintf(__('%s: XML error at line %d column %d', 'woopra'), 'WoopraXML::process_data(222)', xml_get_current_line_number($this->parser), xml_get_current_column_number($this->parser) ) );
		}
		//	** REMOVE THE DATA FROM THE SYSTEM TO FREE UP RESOURCES **/
		unset($woopra_request_data, $this->parser);
		
		if ( $this->found_data )
			return true;
		else
			return new WP_Error('no-data-entries', sprintf(__('%s: No data entries populated.'), 'WoopraXML::process_data(232)') );
		
	}
	
	/** PRIVATE FUNCTIONS - Version 3.0 of the API **/
	
	/**
	 * Set the START Element Header
	 * @since 1.4.1
	 * @param object $parser
	 * @param object $name
	 * @param object $attribs
	 * @uses xml_set_element_handler
	 * @return none
	 */
	function start_xml($parser, $name, $attribs) {
		
		if (($name == "return") && (!$this->record)) {
			$this->record = true;
		}
		
		if ( !$this->record )
			return;
		
		if ( ( !$this->index_created ) && ( $name == 'items' ) )
			$this->index_created = true;	//	Index is done!
		
		$_data_global_types = array("hourElements");
		$_data_other_types = array("dayElements");
		
		if ( in_array($name, $_data_global_types) )
			$this->hour_Childern = true;
		
		if ( in_array($name, $_data_other_types) )
			$this->day_Childern = true;
		
		$this->current_tag = $name;
		
	}
	
	/**
	 * Set the END Element Header
	 * @since 1.4.1
	 * @param object $parser
	 * @param object $name
	 * @uses xml_set_element_handler
	 * @return none
	 */
	function end_xml($parser, $name) {
		if ( !$this->record )
			return;
		
		$_data_global_types = array("hourElements");
		$_data_other_types = array("dayElements");
		
		if ( in_array($name, $_data_global_types) )
			$this->hour_Childern = false;
		
		if ( in_array($name, $_data_other_types) )
			$this->day_Childern = false;
		
		if ( $name == "items" )
			$this->counter++;
		
	}
	
	/**
	 * Process the XML element
	 * @since 1.4.1
	 * @param object $parser
	 * @param object $data
	 * @uses xml_set_character_data_handler
	 * @return none
	 */
	function char_xml($parser, $data) {
		global $_current_hour, $_current_day;
		
		if ( !$this->record )
			return;
		
		if ( $this->current_tag == 'success')
			if ( !$data )
				return;	//	@todo trigger error.
		
		//	Create Index Data
		$_data_ignore = array("return", "success");
		
		if ( !in_array($this->current_tag, $_data_ignore) ) {
			if ( ( !$this->index_created ) && ( $this->current_tag != 'items' ) ) {
				$this->data[$this->current_tag] = $data;
			} else {
				if ( $this->hour_Childern ) {
					if ( $this->current_tag == 'hourOfDay' )
						$_current_hour = $data;
					$this->data['data'][$this->counter]['hourElements'][$_current_hour][$this->current_tag] = $data;
				} else if ( $this->day_Childern ) {
					if ( $this->current_tag == 'day' )
						$_current_day = $data;
					$this->data['data'][$this->counter]['dayElements'][$_current_day][$this->current_tag] = $data;
				} else {
					$this->data['data'][$this->counter][$this->current_tag] = $data;
				}
			}
		}
		
		if ( !$this->found_data && count($this->data) )
			$this->found_data = true;
	}
	
	/**
	 * Clear the Data
	 * @since 1.4.1
	 * @return none
	 */
	function clear_data() {
		$this->data = null;
		$this->counter = 0;
	}
	
}

?>