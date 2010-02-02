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
	 * Display a notice telling the user to fill in their Woopra details
	 * @since 1.4.1
	 * @param mixed $item
	 * @param string $custom
	 * @return none
	 */
	function analytics_warn($item, $custom = '') {
		if ($item == false) {
			echo '<div class="error"><p>' . $custom[0] . "</p></div>\n";
			return;
		} else {
			$message[0]	= '<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
		}
		
		echo $message[$item];
	}
	
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
	function __construct($_woopra_tests) {
		WoopraAdmin::__construct();
		Woopra::__construct();
					
		//	Load the API key into this Class
		$api_key = $this->get_option('api_key');
		
		?>
		<div class="wrap">
		<?php screen_icon(); ?>
			<h2><?php _e( 'Woopra Analytics', 'woopra' ); ?></h2>	
		<?php
		
		if (empty($api_key)) {
			$this->analytics_warn(0);
		} else if ( is_wp_error($_woopra_tests) ) {
			$this->analytics_warn(false, $_woopra_tests->get_error_messages('soap-needed'));
		} else {
			/** HTML CODE START **/
		?>
<!-- Woopra Analytics Starts Here -->
<div id="woopra-analytics-box">
	<div class="woopra_options">
	<a id="woopra-refreshdate" href="#" title="<?php _e('Refresh', 'woopra') ?>"><?php _e('Refresh', 'woopra') ?></a>
	&nbsp;-&nbsp;
	<a id="woopra-daterange" href="#" title="<?php _e('Click here to change the date range', 'woopra') ?>"></a>
	<!-- Date Picker -->
	<div id="woopra-datepickerdiv" style="visibiliy: hidden">
		<table>
			<tr>
				<td align="center">
					<?php _e('From', 'woopra') ?>: <input type="text" id="woopra-from" name="woopra_from" value="" maxlength="10" />
				</td>
				<td align="center">
					<?php _e('To', 'woopra') ?>: <input type="text" id="woopra-to" name="woopra_to" value="" maxlength="10" />
				</td>
			</tr>
		<tr>
			<td colspan="2" style="padding-top: 5px; text-align: right;">
				<input id="woopra-closepicker" value="<?php _e('Cancel', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" />
				<input id="woopra-applydaterange" value="<?php _e('Apply Date Range', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" />
			</td>
		</tr>
		</table>
	</div>
	<!-- Date Picker -->
	</div>
	<ul id="woopra-super-tabs"></ul>
</div>
<!-- Woopra Analytics Ends Here -->
			<?php
			/** HTML CODE END **/
		}
		
		?>
		</div>
		<?php
	}
	
}
?>