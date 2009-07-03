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
	 * @var
	 */
	var $api_key;
	
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
	
	// Display a notice telling the user to fill in their bit.ly details
	function analytics_warn() {
		echo '<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
	}


	/**
	 * Woopra Analytics Header Output
	 * @return 
	 */
	function woopra_analytics_head() {
		echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/js/woopra_analytics.js?1\"></script>\r\n";
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
	}
	
}

?>