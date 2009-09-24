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
	 * Your Site API Key
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
	 * @param object $item
	 * @return none
	 */
	function analytics_warn($item) {
		$message[1]	=	'<div class="error"><p>' . sprintf(__( 'Your site (%s) must be part of the beta in order for this plugin to work correctly.', 'woopra' ), $this->woopra_host(), get_option('siteurl')) . "</p></div>\n";
		$message[2]	=	'<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
		echo $message[$item];
	}
	
	/**
	 * Checking site status.
	 * 
	 * This will check to see if the site based on get_option('siteurl') is vaild. If it
	 * is not, we will tell the user and stop functioning.
	 * 
	 * @since 1.4.2
	 * @return 
	 */
	function check_site_status() {
		
		$xml = new WoopraXML;
		
		$xml->hostname = trim($this->woopra_host());
	
		$entries = null;
		if ($xml->set_xml("status")) {
			if ($xml->process_data()) {
				$entries = $xml->data[0];
			}
		}
		
		$xml->clear_data();
		unset($xml);
		
		if ($entries['status'] == "true")
			return true;
		
		return false;
	}
	
	/**
	 * Return a pretty version of the hostname.
	 * @since 1.4.2
	 * @return string
	 */
	function woopra_host() {
		$site = get_option('siteurl');
		preg_match('@^(?:http://)?([^/]+)@i', $site, $matches);
		$host = $matches[1];
		return preg_replace('!^www\.!i', '', $host);
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
		
		if (!$this->check_site_status()) {
			$this->analytics_warn(1);
		} else if (empty($this->api_key)) {
			$this->analytics_warn(2);
		} else {
			/** HTML CODE START **/
		?>
<!-- Woopra Analytics Starts Here -->
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function() {
		//	Show Date Picker
		jQuery("#daterange").click(function() {
			jQuery("#datepickerdiv").toggle();
		});
		
		jQuery("#refreshdate").click(function() {
			refreshCurrent();
		});	
		
		jQuery("#woopra_from").datepicker({ dateFormat: 'yy-mm-dd' });
		jQuery("#woopra_to").datepicker({ dateFormat: 'yy-mm-dd' });
		
	});
//]]>
</script>
<div id="woopra_analytics_box">
	<div class="woopra_options">
	<a id="refreshdate" href="#" title="<?php _e('Refresh', 'woopra') ?>"><?php _e('Refresh', 'woopra') ?></a>
	&nbsp;-&nbsp;
	<a id="daterange" href="#" title="<?php _e('Click here to change the date range', 'woopra') ?>"><script type="text/javascript">document.write(getDateLinkText())</script></a>
	<!-- Date Picker -->
	<div id="datepickerdiv" style="visibiliy:hidden">
		<table>
			<tr>
				<td align="center">
					<?php _e('From', 'woopra') ?>: <input type="text" id="woopra_from" name="woopra_from" value="" maxlength="10" />
				</td>
				<td align="center">
					<?php _e('To', 'woopra') ?>: <input type="text" id="woopra_to" name="woopra_to" value="" maxlength="10" />
				</td>
			</tr>
		<tr>
			<td colspan="2" style="padding-top: 5px; text-align: right;">
				<input value="<?php _e('Cancel', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" onclick="return closeDatePicker();">
				<input value="<?php _e('Apply Date Range', 'woopra') ?>" name="approveit" class="button-secondary" type="submit" onclick="return applyDatePicker();">
			</td>
		</tr>
		</table>
	</div>
	<!-- Date Picker -->
	</div>
	<ul id="woopra-super-tabs">
		<!-- All Tabs -->
	</ul>
</div>
<!-- Woopra Javascript Code Starts Here -->
<script type="text/javascript">
//<![CDATA[

addSuperTab('<?php _e("Visitors", 'woopra') ?>','visitors');
addSuperTab('<?php _e("Systems", 'woopra') ?>','systems');
addSuperTab('<?php _e("Pages", 'woopra') ?>','pages');
addSuperTab('<?php _e("Referrers", 'woopra') ?>','referrers');
addSuperTab('<?php _e("Searches", 'woopra') ?>','searches');
addSuperTab('<?php _e("Tagged Vistors", 'woopra') ?>','tagvisitors');

addSubTab('<?php _e("Overview", 'woopra') ?>', 'overview', 'visitors', 'GLOBALS');
addSubTab('<?php _e("Countries", 'woopra') ?>', 'countries', 'visitors', 'COUNTRIES');
addSubTab('<?php _e("Bounce Rate", 'woopra') ?>', 'bounces', 'visitors', 'VISITBOUNCES');
addSubTab('<?php _e("Visit Durations", 'woopra') ?>', 'durations', 'visitors', 'VISITDURATIONS');

addSubTab('<?php _e("Browsers", 'woopra') ?>', 'browsers', 'systems', 'BROWSERS');
addSubTab('<?php _e("Platforms", 'woopra') ?>', 'platforms', 'systems', 'PLATFORMS');
addSubTab('<?php _e("Screen Resolutions", 'woopra') ?>', 'resolutions', 'systems', 'RESOLUTIONS');
addSubTab('<?php _e("Languages", 'woopra') ?>', 'languages', 'systems', 'LANGUAGES');

addSubTab('<?php _e("Pageviews", 'woopra') ?>', 'pageviews', 'pages', 'PAGEVIEWS');
addSubTab('<?php _e("Landing Pages", 'woopra') ?>', 'landing', 'pages', 'PAGELANDINGS');
addSubTab('<?php _e("Exit Pages", 'woopra') ?>', 'exit', 'pages', 'PAGEEXITS');
addSubTab('<?php _e("Outgoing Links", 'woopra') ?>', 'outgoing', 'pages', 'OUTGOINGLINKS');
addSubTab('<?php _e("Downloads", 'woopra') ?>', 'downloads', 'pages', 'DOWNLOADS');

addSubTab('<?php _e("Referrer Types", 'woopra') ?>', 'reftypes', 'referrers', 'REFERRERTYPES');
addSubTab('<?php _e("Regular Referrers", 'woopra') ?>', 'refdefault', 'referrers', 'REFERRERS&type=BACKLINK');
addSubTab('<?php _e("Search Engines", 'woopra') ?>', 'refsearch', 'referrers', 'REFERRERS&type=SEARCH');
addSubTab('<?php _e("Feed Readers", 'woopra') ?>', 'reffeeds', 'referrers', 'REFERRERS&type=FEEDS');
addSubTab('<?php _e("Emails", 'woopra') ?>', 'refmails', 'referrers', 'REFERRERS&type=EMAIL');
addSubTab('<?php _e("Social Bookmarks", 'woopra') ?>', 'refbookmarks', 'referrers', 'REFERRERS&type=SOCIALBOOKMARKS');
addSubTab('<?php _e("Social Networks", 'woopra') ?>', 'refnetworks', 'referrers', 'REFERRERS&type=SOCIALNETWORK');
addSubTab('<?php _e("Media", 'woopra') ?>', 'refmedia', 'referrers', 'REFERRERS&type=MEDIA');
addSubTab('<?php _e("News", 'woopra') ?>', 'refnews', 'referrers', 'REFERRERS&type=NEWS');

addSubTab('<?php _e("Search Queries", 'woopra') ?>', 'queries', 'searches', 'QUERIES');
addSubTab('<?php _e("Keywords", 'woopra') ?>', 'keywords', 'searches', 'KEYWORDS');

addSubTab('<?php _e("By Name", 'woopra') ?>', 'taggedvisitors_byname', 'tagvisitors', 'CUSTOMVISITORDATA&aggregate_by=name');

setCurrentSuperTab('visitors');
//]]>
</script>
<!-- Woopra Javascript Code Ends Here -->
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