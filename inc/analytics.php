<?php
/**
 * WoopraAnalytics Class for Woopra
 *
 * This class contains all functions and code related to 
 * view the stats within WordPress itself.
 *
 * @since 1.4.1
 * @package admin
 * @subpackage analytics
 */
class WoopraAnalytics extends WoopraAdmin {

	/**
	 * Store the API key
	 * @var string
	 */
	var $api_key;
	
	/**
	 * @var string
	 */
	var $key;
	
	/**
	 * @var string
	 */
	var $date_from;
	
	/**
	 * @var string
	 */
	var $date_to;
	
	/**
	 * @var int
	 */
	var $limit = 50;
	
	/**
	 * @var int
	 */
	var $offset = 0;
	
	
	/**
	 * @var
	 */
	var $entries;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraAnalytics () {
		$this->__construct();
	}
	
	/**
	 * Woopra Analytics
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		WoopraAdmin::__construct();
		Woopra::__construct();
				
		//	Load the API key into this Class
		$this->api_key = $this->get_option('api_key');
		
		//	Actions
		
	}
	
	// Display a notice telling the user to fill in their Woopra details
	function analytics_warn() {
		echo '<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
	}


	/**
	 * Woopra Analytics Header Output
	 * @return 
	 */
	function woopra_analytics_head() {
		echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/js/analytics.js?1\"></script>\r\n";
		echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/js/swfobject.js\"></script>\r\n";
		echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/js/datepicker.js\"></script>\r\n";
		echo "<link rel='stylesheet' href='". get_option('siteurl') ."/wp-content/plugins/woopra/css/woopra_analytics.css' type='text/css' />\r\n";
		echo "<link rel='stylesheet' href='". get_option('siteurl') ."/wp-content/plugins/woopra/css/datepicker.css' type='text/css' />\r\n";
	}
	
	/**
	 * Display the Analytics
	 * @since 1.4.1
	 * @return none
	 */
	function main() { 
		
		// Prase the URL
		if (isset($_GET['wkey']))
		
		$this->woopra_analytics_head(); // do no matter what!
		?>
		
		<div class="wrap">
		<?php screen_icon(); ?>
			<h2><?php _e( 'Woopra Analytics', 'woopra' ); ?></h2>	
		
		<?php
		
		if (empty($this->api_key)) {
			$this->analytics_warn();
		} else {
			/** HTML CODE START **/
			/** HTML CODE END **/
		}
		
		?>
		
		</div>
		
		<?php
		
		$this->generate_data();
	
	}
	
	/**
	 * 
	 * @return 
	 */
	function generate_data() {
		
		
		$start_date = $this->woopra_convert_date($this->date_from);
		$end_date = $this->woopra_convert_date($this->date_to);
		
		/** LAST LINES **/
		if ($this->process_xml($this->key, $start_date, $end_date, $this->limit, $this->offset))
			$this->render_results($this->key);	
	}
	
	/**
	 * Process XML Request
	 * @return 
	 * @param object $key
	 * @param object $start_date
	 * @param object $end_date
	 * @param object $limit
	 * @param object $offset
	 */
	function process_xml($key, $start_date, $end_date, $limit, $offset) {
		$xml = new WoopraXML;
		$xml->hostname = $this->woopra_host();
		$xml->api_key = $this->api_key;

		$this->entries = null;
		if ($xml->init()) {
			if ($xml->set_xml($key, $start_date, $end_date, $limit, $offset))
				if ($xml->process_data())
					$this->entries = $xml->data;
		}
		
		if ($xml->connection_error != null || $xml->error_msg != null || !$xml->init()) {
			echo '<div class="error"><p>'. __("The Woopra Plugin was not able to request your analytics data from the Woopra Engines.", 'woopra') . '<br/><small>'. sprintf( __("Your hosting provider is not allowing the Woopra Plugin to fetch data from the Woopra Servers.<br/>%s<br/><a href='http://www.woopra.com/forums/'>Report this error onto the forums!</a>", 'woopra'), $xml->connection_error . $xml->error_msg) . '</small></p></div>';
			return false;
		}
		
		$xml->clear_data();
		return true;
	}
	
	/** PRIVATE FUNCTIONS **/
	
	/**
	 * 
	 * @return 
	 */
	private function woopra_host() {
		$site = get_option('siteurl');
		preg_match('@^(?:http://)?([^/]+)@i', $site, $matches);
		$host = $matches[1];
		return preg_replace('!^www\.!i', '', $host);
	}
	
	function woopra_convert_date($value) {
		
	}
	
}

?>