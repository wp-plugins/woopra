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
	 * @since 1.4.1
	 * @var string
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
		
	}
	
	/**
	 * Display a notice telling the user to fill in their Woopra details
	 * @since 1.4.1
	 * @return none
	 */
	function analytics_warn() {
		echo '<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
	}
	
	/**
	 * Display the Analytics
	 * @since 1.4.1
	 * @return none
	 */
	function main() { ?>
		
		<div class="wrap">
		<?php screen_icon(); ?>
			<h2><?php _e( 'Woopra Analytics', 'woopra' ); ?></h2>	
		
		<?php
		
		if (empty($this->api_key)) {
			$this->analytics_warn();
		} else {
			/** HTML CODE START **/
			?>
<!-- Woopra Analytics Starts Here -->
<div id="woopra_analytics_global">
	<div id="woopra_analytics_box">
		<div class="woptions">
		<a href="#" onclick="return refreshCurrent();"><?php _e('Refresh', 'woopra') ?></a>
		&nbsp;-&nbsp;
		<a id="daterangelink" href="#" onclick="return showDatePicker();" title="<?php _e('Click here to change the date range', 'woopra') ?>"><script type="text/javascript">document.write(getDateLinkText())</script></a>
			<div id="datepickerdiv">
				<table><tr>
				<td align="center"><?php _e('From', 'woopra') ?>: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-from" name="dp-from" value="" maxlength="10" /></td>
				<td align="center"><?php _e('To', 'woopra') ?>: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-to" name="dp-to" value="" maxlength="10" /></td>
				</tr>
				<tr>
				<td colspan="2" style="padding-top: 5px; text-align: right;">
				<input value="<?php _e('Cancel', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" onclick="return closeDatePicker();">
				<input value="<?php _e('Apply Date Range', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" onclick="return applyDatePicker();">
				</td>
				</tr>
				</table>
			</div>
		</div>

		<ul id="woopra-super-tabs">
		</ul>
	</div>
	<!-- Woopra Javascript Code Starts Here -->
	<script type="text/javascript">
	
	addSuperTab('<?php _e("Visitors", 'woopra') ?>','visitors');
	addSuperTab('<?php _e("Systems", 'woopra') ?>','systems');
	addSuperTab('<?php _e("Pages", 'woopra') ?>','pages');
	addSuperTab('<?php _e("Referrers", 'woopra') ?>','referrers');
	addSuperTab('<?php _e("Searches", 'woopra') ?>','searches');
	
	addSubTab('<?php _e("Overview", 'woopra') ?>', 'overview', 'visitors', 'GET_GLOBALS');
	addSubTab('<?php _e("Countries", 'woopra') ?>', 'countries', 'visitors', 'GET_COUNTRIES');
	addSubTab('<?php _e("Tagged Visitors", 'woopra') ?>', 'taggedvisitors', 'visitors', 'GET_SPECIALVISITORS');
	addSubTab('<?php _e("Bounce Rate", 'woopra') ?>', 'bounces', 'visitors', 'GET_VISITBOUNCES');
	addSubTab('<?php _e("Visit Durations", 'woopra') ?>', 'durations', 'visitors', 'GET_VISITDURATIONS');
	
	addSubTab('<?php _e("Browsers", 'woopra') ?>', 'browsers', 'systems', 'GET_BROWSERS');
	addSubTab('<?php _e("Platforms", 'woopra') ?>', 'platforms', 'systems', 'GET_PLATFORMS');
	addSubTab('<?php _e("Screen Resolutions", 'woopra') ?>', 'resolutions', 'systems', 'GET_RESOLUTIONS');
	addSubTab('<?php _e("Languages", 'woopra') ?>', 'languages', 'systems', 'GET_LANGUAGES');
	
	addSubTab('<?php _e("Pageviews", 'woopra') ?>', 'pageviews', 'pages', 'GET_PAGEVIEWS');
	addSubTab('<?php _e("Landing Pages", 'woopra') ?>', 'landing', 'pages', 'GET_PAGELANDINGS');
	addSubTab('<?php _e("Exit Pages", 'woopra') ?>', 'exit', 'pages', 'GET_PAGEEXITS');
	addSubTab('<?php _e("Outgoing Links", 'woopra') ?>', 'outgoing', 'pages', 'GET_OUTGOINGLINKS');
	addSubTab('<?php _e("Downloads", 'woopra') ?>', 'downloads', 'pages', 'GET_DOWNLOADS');
	
	addSubTab('<?php _e("Referrer Types", 'woopra') ?>', 'reftypes', 'referrers', 'GET_REFERRERTYPES');
	addSubTab('<?php _e("Regular Referrers", 'woopra') ?>', 'refdefault', 'referrers', 'GET_REFERRERS&type=DEFAULT');
	addSubTab('<?php _e("Search Engines", 'woopra') ?>', 'refsearch', 'referrers', 'GET_REFERRERS&type=SEARCHENGINES');
	addSubTab('<?php _e("Feed Readers", 'woopra') ?>', 'reffeeds', 'referrers', 'GET_REFERRERS&type=FEEDS');
	addSubTab('<?php _e("Emails", 'woopra') ?>', 'refmails', 'referrers', 'GET_REFERRERS&type=MAILS');
	addSubTab('<?php _e("Social Bookmarks", 'woopra') ?>', 'refbookmarks', 'referrers', 'GET_REFERRERS&type=SOCIALBOOKMARKS');
	addSubTab('<?php _e("Social Networks", 'woopra') ?>', 'refnetworks', 'referrers', 'GET_REFERRERS&type=SOCIALNETWORKS');
	addSubTab('<?php _e("Media", 'woopra') ?>', 'refmedia', 'referrers', 'GET_REFERRERS&type=MEDIA');
	addSubTab('<?php _e("News", 'woopra') ?>', 'refnews', 'referrers', 'GET_REFERRERS&type=NEWS');
	
	addSubTab('<?php _e("Search Queries", 'woopra') ?>', 'queries', 'searches', 'GET_QUERIES');
	addSubTab('<?php _e("Keywords", 'woopra') ?>', 'keywords', 'searches', 'GET_KEYWORDS');
	
	setCurrentSuperTab('visitors');
	</script>
	<!-- Woopra Javascript Code Ends Here -->
	<div id="woopra_footer">
		<?php printf( __('Powered by <a href="%1$s">Woopra Analytics</a>', 'woopra'), 'http://woopra.com'); ?>
	</div>
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