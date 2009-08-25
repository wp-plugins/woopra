<?php


if (isset($_GET['wkey'])) {
	
	include 'woopra_functions.php';
	include 'woopra_renderer.php';
	
	$key = $_GET['wkey'];
	$key = str_replace("&amp;","&",$key);
	
	$date_from = $_GET['from'];
	$date_to = $_GET['to'];

	$entries = woopra_process_request($key,woopra_convert_date($date_from),woopra_convert_date($date_to),50,0);
	woopra_render_results($entries, $key);
	die();
}

function woopra_analytics_show_content() {


if (!woopra_check_analytics()) {
?>
	<p align="center"><?php _e('Please provide your API Key & Woopra Site ID in order to show your analytics!') ?></p>
<?php
	return;
}
?>

<!-- Woopra Analytics Starts Here -->

<div id="woopra_analytics_global">
	<div id="woopra_analytics_box">
		
		<div class="woptions">
		<a href="#" onclick="return refreshCurrent();"><?php _e('Refresh') ?></a>
		&nbsp;-&nbsp;
		<a id="daterangelink" href="#" onclick="return showDatePicker();" title="<?php _e('Click here to change the date range') ?>"><script type="text/javascript">document.write(getDateLinkText())</script></a>
			<div id="datepickerdiv">
				<table><tr>
				<td align="center"><?php _e('From') ?>: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-from" name="dp-from" value="" maxlength="10" /></td>
				<td align="center"><?php _e('To') ?>: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-to" name="dp-to" value="" maxlength="10" /></td>
				</tr>
				<tr>
				<td colspan="2" style="padding-top: 5px; text-align: right;">
				<input value="<?php _e('Cancel') ?>" name="approveit" class="button-secondary" type="submit" onclick="return closeDatePicker();">
				<input value="<?php _e('Apply Date Range') ?>" name="approveit" class="button-secondary" type="submit" onclick="return applyDatePicker();">
				</td>
				</tr>
				</table>
			</div>
		</div>

		<ul id="woopra-super-tabs">
		</ul>
		
		
	</div>
	
	<script type="text/javascript">
	
	woopra_website = '<?php echo get_option("siteurl"); ?>';
		
	addSuperTab('<?php _e("Visitors") ?>','visitors');
	addSuperTab('<?php _e("Systems") ?>','systems');
	addSuperTab('<?php _e("Pages") ?>','pages');
	addSuperTab('<?php _e("Referrers") ?>','referrers');
	addSuperTab('<?php _e("Searches") ?>','searches');
	
	addSubTab('<?php _e("Overview") ?>', 'overview', 'visitors', 'GET_GLOBALS');
	addSubTab('<?php _e("Countries") ?>', 'countries', 'visitors', 'GET_COUNTRIES');
	addSubTab('<?php _e("Tagged Visitors") ?>', 'taggedvisitors', 'visitors', 'GET_SPECIALVISITORS');
	addSubTab('<?php _e("Bounce Rate") ?>', 'bounces', 'visitors', 'GET_VISITBOUNCES');
	addSubTab('<?php _e("Visit Durations") ?>', 'durations', 'visitors', 'GET_VISITDURATIONS');
	
	addSubTab('<?php _e("Browsers") ?>', 'browsers', 'systems', 'GET_BROWSERS');
	addSubTab('<?php _e("Platforms") ?>', 'platforms', 'systems', 'GET_PLATFORMS');
	addSubTab('<?php _e("Screen Resolutions") ?>', 'resolutions', 'systems', 'GET_RESOLUTIONS');
	addSubTab('<?php _e("Languages") ?>', 'languages', 'systems', 'GET_LANGUAGES');
	
	addSubTab('<?php _e("Pageviews") ?>', 'pageviews', 'pages', 'GET_PAGEVIEWS');
	addSubTab('<?php _e("Landing Pages") ?>', 'landing', 'pages', 'GET_PAGELANDINGS');
	addSubTab('<?php _e("Exit Pages") ?>', 'exit', 'pages', 'GET_PAGEEXITS');
	addSubTab('<?php _e("Outgoing Links") ?>', 'outgoing', 'pages', 'GET_OUTGOINGLINKS');
	addSubTab('<?php _e("Downloads") ?>', 'downloads', 'pages', 'GET_DOWNLOADS');
	
	addSubTab('<?php _e("Referrer Types") ?>', 'reftypes', 'referrers', 'GET_REFERRERTYPES');
	addSubTab('<?php _e("Regular Referrers") ?>', 'refdefault', 'referrers', 'GET_REFERRERS&type=DEFAULT');
	addSubTab('<?php _e("Search Engines") ?>', 'refsearch', 'referrers', 'GET_REFERRERS&type=SEARCHENGINES');
	addSubTab('<?php _e("Feed Readers") ?>', 'reffeeds', 'referrers', 'GET_REFERRERS&type=FEEDS');
	addSubTab('<?php _e("Emails") ?>', 'refmails', 'referrers', 'GET_REFERRERS&type=MAILS');
	addSubTab('<?php _e("Social Bookmarks") ?>', 'refbookmarks', 'referrers', 'GET_REFERRERS&type=SOCIALBOOKMARKS');
	addSubTab('<?php _e("Social Networks") ?>', 'refnetworks', 'referrers', 'GET_REFERRERS&type=SOCIALNETWORKS');
	addSubTab('<?php _e("Media") ?>', 'refmedia', 'referrers', 'GET_REFERRERS&type=MEDIA');
	addSubTab('<?php _e("News") ?>', 'refnews', 'referrers', 'GET_REFERRERS&type=NEWS');
	
	addSubTab('<?php _e("Search Queries") ?>', 'queries', 'searches', 'GET_QUERIES');
	addSubTab('<?php _e("Keywords") ?>', 'keywords', 'searches', 'GET_KEYWORDS');
	
	setCurrentSuperTab('visitors');
	</script>
	
	<div id="woopra_footer">
		<?php printf( __('Powered by <a href="%1$s">Woopra Analytics</a>'), 'http://woopra.com'); ?>
	</div>
</div>
<!-- Woopra Analytics Ends Here -->

<?php
}

function woopra_check_analytics() {
	if (get_option('woopra_api_key') && get_option('woopra_website_id') && get_option('woopra_api_key') != "" && get_option('woopra_website_id') != "") {
		return true;
	}
	return false;
}

function woopra_process_request($key,$start_date, $end_date, $limit, $offset) {
	include 'woopra_xml.php';
	$woopraXML = new WoopraAPI();
	$woopraXML->hostname = get_woopra_host();
	$woopraXML->siteid = get_option('woopra_website_id');
	$woopraXML->api_key = get_option('woopra_api_key');
	$init = $woopraXML->Init();
	$entries = null;
	if ($init) {
		$woopraXML->setXML($key, $start_date, $end_date, $limit, $offset);
		if ($woopraXML->processData())	{
			$entries = $woopraXML->data;//['data']['entry'];
		} 
	}
	
	if ($woopraXML->connection_error != null) {
		die('<p style="text-align: center; background: #dc0303; padding: 10px; color: #fff;">'. __("The Woopra Plugin was not able to request your analytics data from the Woopra Engines.") . '<br/><small>'. __("Your hosting provider is not allowing the Woopra Plugin to fetch data from the Woopra Servers.") . '</small></p>');
	}
	
	$woopraXML->clearData();
	
	//print_r($entries);
	
	return $entries;
}



?>
