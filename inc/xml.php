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
	 * @since 1.4.1
	 * @var object
	 */
	var $parser = null;

	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $url = null;

	/**
	 * @since 1.4.1
	 * @var array
	 */
	var $data = null;
	
	/**
	 * @since 1.4.1
	 * @var int
	 */
	var $counter = null;
	
	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $current_tag = null;
	
	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $connection_error = null;
	
	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $error_msg = null;
	
	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $founddata = false;
	
	/**
	 * @since 1.4.1
	 * @var string
	 */
	var $hostname = null;
	
	/**
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
	 * @return boolen
	 */
	function init() {
		if (!$this->api_key)
			return false;
		return true;
	}
	
	/**
	 * 
	 * @return 
	 * @param object $key
	 * @param object $start_date
	 * @param object $end_date
	 * @param object $limit
	 * @param object $offset
	 */
	function set_xml($key, $start_date, $end_date, $limit, $offset) {
		$this->url = "http://".$this->hostname.".woopra-ns.com/api/output_format=xml&website=".$this->hostname."&api_key=".$this->api_key."&query=".$key."&start_day=".$start_date."&end_day=".$end_date."&limit=".$limit."&offset=".$offset;
		return true;
	}
	
	/**
	 * @since 1.4.1
	 * @return none
	 */
	function clear_data() {
		$this->data = null;
    	$this->counter = 0;
	}
	
	/**
	 * 
	 * @return 
	 */
    function process_data() {
        $this->parser = xml_parser_create("UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'start_xml', 'end_xml');
        xml_set_character_data_handler($this->parser, 'char_xml');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
				
        if (!($fp = @fopen($this->url, 'rb'))) {
            return false;
        }

        while (($data = fread($fp, 8192))) {
            if (!xml_parse($this->parser, $data, feof($fp))) {
            	return false;
            }
        }
        
        if ($this->founddata)
        	return true;
        else 
        	return false;    
    }
	

	
	/** PRIVATE FUNCTIONS **/
	
	function start_xml($parser, $name, $attribs) {
		if (($name == "entry") && (!$this->founddata))
			$this->founddata = true;
			
		$this->current_tag = $name;
    }
	
    function end_xml($parser, $name) {
		if ($name == "entry")
			$this->counter++;
    }
	
    function char_xml($parser, $data) {
		if ($this->founddata)
			$this->data[$this->counter][$this->current_tag] = $data;
	}
	
}

?>