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

	/**
	 * Parser object for XML-PHP
	 * @since 1.4.1
	 * @var object
	 */
	var $parser = null;

	/**
	 * URL
	 * @since 1.4.1
	 * @var string
	 */
	var $url = null;

	/**
	 * Data from XML
	 * @since 1.4.1
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Counter at which tag.
	 * @since 1.4.1
	 * @var int
	 */
	var $counter = 0;
	
	/**
	 * Current TAG.
	 * @since 1.4.1
	 * @var string
	 */
	var $current_tag = null;
	
	/**
	 * What is the connection error?
	 * @since 1.4.1
	 * @var string
	 */
	var $connection_error = null;
	
	/**
	 * Any error messages?
	 * @since 1.4.1
	 * @var string
	 */
	var $error_msg = null;
	
	/**
	 * Bydays Found
	 * @since 1.4.1
	 * @var boolean
	 */
	var $byday_found = false;
	
	/**
	 * Hours Found
	 * @since 1.4.1
	 * @var boolean
	 */
	var $byhours_found = false;
	
	/**
	 * Has data been found?
	 * @since 1.4.1
	 * @var string
	 */
	var $founddata = false;
	
	/**
	 * Hostname of our site.
	 * @since 1.4.1
	 * @var string
	 */
	var $hostname = null;
	
	/**
	 * API Key
	 * @since 1.4.1
	 * @var string
	 */
	var $api_key = null;

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
	
	}
	
	/**
	 * Initialization of the Process Check
	 * @since 1.4.1
	 * @return boolean
	 */
	function init() {
		if (!$this->api_key)
			return false;
		return true;
	}
	
	/**
	 * Set the XML File Location
	 * @return boolean
	 * @param object $key
	 * @param object $date_format
	 * @param object $start_date
	 * @param object $end_date
	 * @param object $limit
	 * @param object $offset
	 */
	function set_xml($key, $date_format, $start_date, $end_date, $limit, $offset) {
		// This is now set to the temp. location of version two of the API.
		$this->url = "http://".$this->hostname.".woopra-ns.com/apiv2/website=".$this->hostname."&api_key=".$this->api_key."&key=".$key."&date_format=".$date_format."&start_day=".$start_date."&end_day=".$end_date."&limit=".$limit."&offset=".$offset;
		return true;
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
	
	/**
	 * Process the XML File
	 * @since 1.4.1
	 * @return boolean
	 */
    function process_data() {
        $this->parser = xml_parser_create("UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'start_xml', 'end_xml');
        xml_set_character_data_handler($this->parser, 'char_xml');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
				
        if (!($fp = @fopen($this->url, 'rb'))) {
            $this->connection_error = sprintf(__("%s: Cannot open {$this->url}", 'woopra'), 'WoopraXML::parse(148)');
            return $this->error();
        }

        while (($data = fread($fp, 8192))) {
            if (!xml_parse($this->parser, $data, feof($fp))) {
                $this->error_msg = sprintf(__('%s: XML error at line %d column %d', 'woopra'), 'WoopraXML::parse(154)', xml_get_current_line_number($this->parser), xml_get_current_column_number($this->parser));
                return $this->error();
            }
        }
		
        if ($this->founddata) {
        	return true;
        } else {
        	$this->error_msg = sprintf(__("%s: No data entries.", 'woopra'), 'WoopraXML::parse(162)');
        	return $this->error();
        }     
    }
	
	/**
	 * Resturn False
	 * @since 1.4.1
	 * @return boolean
	 */
	function error() {
		return false;
	}
	
	/** PRIVATE FUNCTIONS - Version 2.1 of the API **/
	
	/**
	 * Set the START Element Header
	 * @return  none
	 * @param object $parser
	 * @param object $name
	 * @param object $attribs
	 * @uses xml_set_element_handler
	 */
	function start_xml($parser, $name, $attribs) {
		//	Response Check
		if (($name == "response") && (!$this->founddata))
			if ($attribs['success'] == "true") 
				$this->founddata = true;
		
		//	By Day
		if ($name == "byday")
			$this->byday_found = true;
	
		if (($name == "day") && ($this->byday_found))
			$this->data[$this->counter]['days'][] = $attribs;
		
		//	Hours
		if ($name == "hours")
			$this->byhours_found = true;
		
		if (($name == "hour") && ($this->byhours_found))
			$this->data[$this->counter]['hours'][] = $attribs;
		
		//	Create Index ID
		$this->data[$this->counter]['index'] = $this->counter;
		
		$this->current_tag = $name;
    }
	
	/**
	 * Set the END Element Header
	 * @return 
	 * @param object $parser
	 * @param object $name
	 * @uses xml_set_element_handler
	 */
    function end_xml($parser, $name) {
		if ($name == "item")
			$this->counter++;
    }
	
	/**
	 * Process the XML element
	 * @return 
	 * @param object $parser
	 * @param object $data
	 * @uses xml_set_character_data_handler
	 */
    function char_xml($parser, $data) {
		if ($this->founddata)
			$this->data[$this->counter][$this->current_tag] = $data;			
	}
}

?>
