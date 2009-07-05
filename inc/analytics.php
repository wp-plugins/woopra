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
	 * @var
	 */
	var $countries;
	
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
		
		//	Country List Create
		$this->init_countries();
		
	}
	
	// Display a notice telling the user to fill in their Woopra details
	function analytics_warn() {
		echo '<div class="error"><p>' . sprintf( __( 'You must fill in your API Key in order to view Analytics. Please fill it out on the <a href="%s">settings page</a> in order for you to view your analytics.', 'woopra' ), admin_url('options-general.php?page=woopra') ) . "</p></div>\n";
	}
	
	/**
	 * Display the Analytics
	 * @since 1.4.1
	 * @return none
	 */
	function main() { 
		
		$this->debug($this->page_hookname);
		
		?>
		
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
	
	woopra_website = '<?php echo get_option("siteurl"); ?>';
		
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
	
	/**
	 * 
	 * @return 
	 */
	function generate_data() {
		if (isset($_GET['wkey'])) {

			$key = $_GET['wkey'];
			$this->key = str_replace("&amp;", "&", $key);
			$this->date_from = $_GET['from'];
			$this->date_to = $_GET['to'];
			
			$start_date = $this->woopra_convert_date($this->date_from);
			$end_date = $this->woopra_convert_date($this->date_to);
			
			/** LAST LINES **/
			if ($this->process_xml($this->key, $start_date, $end_date, $this->limit, $this->offset))	
				$this->render_results();				

		}
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
		unset($xml);
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
	
	private function woopra_convert_date($date) {
		$values = split('-', $date);
		$y = (int) $values[0];
		$day_of_year = date('z', mktime(0, 0, 0, (int)$values[1], (int)$values[2] , (int)$values[0]));
		$wdate = (100000 * ($y-2006)) + $day_of_year + 1;
		return $wdate;
	}
	
	private function woopra_contains($str, $sub) {
		return strpos($str, $sub) !== false;
	}
	
	private function sort_analytics_response($entries) {
		usort($entries, 'compare_analytics_entries');
	}
	
	private function woopra_line_chart_date($date) {
		$date = (int) $date;
		$year = 2006 + (int) ($date/100000);
		$day_of_year = $date%100000;
		$to_return = date('F jS', mktime(0,0,0,1,$day_of_year,$year));
		return $to_return;
	}
	
	private function render_chart_data($entries, $key) {
		
		$counter = 0;
		$max = $this->woopra_get_max($entries, 'hits');
		$max = $this->woopra_rounded_max($max);
	
		$values = '';
		$labels = '';
		foreach($entries as $entry) {
			$day = (int) $entry['day'];
			$hits = (int) $entry['hits'];
			if ($values != '') {
				$values .= ',';
				$labels .= ',';
			}
			$values .= $hits;
			$labels .= $this->woopra_encode($this->woopra_line_chart_date($day));
		}
		$values = $values;
		$labels = $labels;
		
		$data = "&x_label_style=10,0x000000,0,5&\r\n";
		$data .= "&x_axis_steps=1&\r\n";
		$data .= "&y_ticks=5,10,5&\r\n";
		$data .= "&line=3,0xB0E050,Jan,12&\r\n";
		$data .= "&values=$values&\r\n";
		$data .= "&x_labels=$labels&\r\n";
		$data .= "&y_min=0&\r\n";
		$data .= "&y_max=$max&\r\n";
		$data .= "&tool_tip=%23x_label%23%3A+%23val%23%20hits&
		\r\n";
		echo $data;
	}
	
	private function render_expanded_referrers($entries, $key) {

		$bydaykey = str_replace('GET_REFERRERS&', 'GET_REFERRERS_BY_DAY&', $key);
		$bydaykey = str_replace 
		?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
		<?php
		
		$counter = 0;
		$sum = $this->woopra_get_sum($entries, 'hits');
		foreach ($entries as $entry) {
		
			$id = (int) $entry['id'];
			$name = urldecode($entry['name']);
			$hits = (int) $entry['hits'];
			$meta = urldecode($entry['meta']);
		
			$percent = 0;
			if ($sum != 0) {
				$percent = round($hits*100/$sum);
			}
			$hashid = $this->woopra_friendly_hash($key);
			?>
			<tr class="<?php echo (($counter++%2==0) ? "expanded_even_row" : "expanded_row"); ?>">
				<td class="wrank"><?php echo $counter; ?></td>
				<td><span class="ellipsis"><a href="<?php echo $name; ?>" target="_blank"><?php echo $this->woopra_render_name($key, $name, $meta); ?></a></span></td>
				<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $bydaykey; ?>', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
				<td class="wbar"><?php echo $this->woopra_bar($percent); ?></td>
			</tr>
			<tr id="wlc-<?php echo $hashid; ?>-<?php echo $id; ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $id; ?>" colspan="4"></td></tr>
			<tr id="refexp-<?php echo $hashid; ?>-<?php echo $id; ?>" style="display: none;"><td colspan="4"><div id="refexptd-<?php echo $hashid; ?>-<?php echo $id; ?>"></div></td></tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	
	private function render_referrers($entries, $key) {

		$bydaykey = str_replace('GET_REFERRERS&', 'GET_REFERRERS_BY_DAY&', $key);
		?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th><?php _e('Referrer'); ?></th>
			<th class="center" width="100"><?php _e('Hits'); ?></th>
			<th width="400">&nbsp;</th>
		</tr>
		<?php
		
		$counter = 0;
		$sum = $this->woopra_get_sum($entries, 'hits');
		foreach($entries as $entry) {
			$id = (int) $entry['id'];
			$name = urldecode($entry['name']);
			$hits = (int) $entry['hits'];
			$meta = urldecode($entry['meta']);
			
			$percent = 0;
			if ($sum != 0)
				$percent = round($hits*100/$sum);
			$hashid = $this->woopra_friendly_hash($key);
			?>
			<tr<?php echo (($counter++%2==0) ? " class=\"even_row\"" : "" ); ?>>
				<td class="wrank"><?php echo $counter; ?></td>
			<?php if ($this->woopra_key_expansible($key)) { ?>
				<td><span class="ellipsis"><a href="#" onclick="return expandReferrer('<?php echo $key . '&id=' . $id; ?>', '<?php echo $hashid .'-'. $id; ?>')"><?php echo $this->woopra_render_name($key, $name, $meta); ?></a></span></td>
			<?php } else { ?>
				<td><span class="ellipsis"><a href="http://<?php echo $name; ?>" target="_blank"><?php echo $this->woopra_render_name($key, $name, $meta); ?></a></span></td>
			<?php } ?>
				<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $bydaykey; ?>', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
				<td class="wbar"><?php echo $this->woopra_bar($percent) ?></td>
			</tr>
			<tr id="wlc-<?php echo $hashid; ?>-<?php echo $id ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid ?>-<?php echo $id; ?>" colspan="4"></td></tr>
			<tr id="refexp-<?php echo $hashid; ?>-<?php echo $id; ?>" style="display: none;"><td colspan="4" style="padding: 0;"><div id="refexptd-<?php echo $hashid; ?>-<?php echo $id; ?>"></div></td></tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	
	private function woopra_get_sum($entries, $key) {
		$sum = 0;
		foreach ($entries as $entry) {
			$val = (int) $entry[$key];
			$sum = $sum + $val;
		}
		return $sum;
	}
	
	private function woopra_key_expansible($key) {
		if ($this->woopra_contains($key, '&type=SEARCHENGINES') || $this->woopra_contains($key, '&type=FEEDS') || $this->woopra_contains($key, '&type=MAILS'))
			return false;
		return true;
	}
	
	private function woopra_friendly_hash($value) {
		return substr(md5($value),0,4);
	}
	
	private function woopra_get_max($entries, $key) {
		$max = 0;
		foreach ($entries as $entry) {
			$val = (int) $entry[$key];
			if ($val > $max)
				$max = $val;
		}
		
		return $max;
	}
	
	private function woopra_seconds_to_string($seconds) {
		$min = floor($seconds/60);
		$sec = $seconds%60;
		return $min . "m " . $sec . "s";
	}
	
	private function woopra_date_to_string($date) {
		$date = (int) $date;
		$year = 2006 + (int) ($date/100000);
		$day_of_year = $date%100000;
		$to_return = date('F j, Y', mktime(0,0,0,1,$day_of_year,$year)); 
		return $to_return;
	}
	
	private function woopra_bar($percent) {
		$barurl = get_option('siteurl') . '/wp-content/plugins/woopra/images/bar.png';
		$width = $percent . "%";
		if ($percent < 1)
			$width = "1";
		
		return '<img src="'.$barurl.'" width="'.$width.'" height="16" />';
	}
	
	private function init_countries() {
		$this->countries = array(
			"TJ" => "Tajikistan",
			"TH" => "Thailand",
			"TG" => "Togo",
			"GY" => "Guyana",
			"GW" => "Guinea-bissau",
			"GU" => "Guam",
			"GT" => "Guatemala",
			"GR" => "Greece",
			"GP" => "Guadeloupe",
			"SZ" => "Swaziland",
			"SY" => "Syria",
			"GM" => "Gambia",
			"GL" => "Greenland",
			"SV" => "El Salvador",
			"GI" => "Gibraltar",
			"GH" => "Ghana",
			"SR" => "Suriname",
			"GF" => "French Guiana",
			"GE" => "Georgia",
			"GD" => "Grenada",
			"SN" => "Senegal",
			"GB" => "United Kingdom",
			"SM" => "San Marino",
			"GA" => "Gabon",
			"SL" => "Sierra Leone",
			"SK" => "Slovakia",
			"SI" => "Slovenia",
			"SG" => "Singapore",
			"SE" => "Sweden",
			"SD" => "Sudan",
			"SC" => "Seychelles",
			"SB" => "Solomon Islands",
			"SA" => "Saudi Arabia",
			"FR" => "France",
			"FO" => "Faroe Islands",
			"FM" => "Micronesia",
			"RW" => "Rwanda",
			"FJ" => "Fiji",
			"RU" => "Russia",
			"FI" => "Finland",
			"RS" => "Serbia",
			"RO" => "Romania",
			"EU" => "European Union",
			"ET" => "Ethiopia",
			"ES" => "Spain",
			"ER" => "Eritrea",
			"EG" => "Egypt",
			"EE" => "Estonia",
			"EC" => "Ecuador",
			"DZ" => "Algeria",
			"QA" => "Qatar",
			"DO" => "Dominican Republic",
			"PY" => "Paraguay",
			"PW" => "Palau",
			"DK" => "Denmark",
			"DJ" => "Djibouti",
			"PT" => "Portugal",
			"PS" => "Palestine",
			"PR" => "Puerto Rico",
			"DE" => "Germany",
			"PL" => "Poland",
			"PK" => "Pakistan",
			"PH" => "Philippines",
			"PG" => "Papua New Guinea",
			"CZ" => "Czech Republic",
			"PF" => "French Polynesia",
			"CY" => "Cyprus",
			"PE" => "Peru",
			"CU" => "Cuba",
			"PA" => "Panama",
			"CS" => "Serbia",
			"CR" => "Costa Rica",
			"CO" => "Colombia",
			"CN" => "China",
			"CM" => "Cameroon",
			"CL" => "Chile",
			"CK" => "Cook Islands",
			"CI" => "Cote D'ivoire",
			"CH" => "Switzerland",
			"CF" => "Central African Republic",
			"CD" => "Congo",
			"OM" => "Oman",
			"CA" => "Canada",
			"BZ" => "Belize",
			"BY" => "Belarus",
			"BW" => "Botswana",
			"BT" => "Bhutan",
			"BS" => "Bahamas",
			"BR" => "Brazil",
			"BO" => "Bolivia",
			"NZ" => "New Zealand",
			"BN" => "Brunei Darussalam",
			"BM" => "Bermuda",
			"BJ" => "Benin",
			"NU" => "Niue",
			"BI" => "Burundi",
			"BH" => "Bahrain",
			"BG" => "Bulgaria",
			"NR" => "Nauru",
			"BF" => "Burkina Faso",
			"BE" => "Belgium",
			"NP" => "Nepal",
			"BD" => "Bangladesh",
			"NO" => "Norway",
			"BB" => "Barbados",
			"BA" => "Bosnia And Herzegowina",
			"NL" => "Netherlands",
			"ZW" => "Zimbabwe",
			"NI" => "Nicaragua",
			"NG" => "Nigeria",
			"AZ" => "Azerbaijan",
			"NF" => "Norfolk Island",
			"AX" => "�land Islands",
			"AW" => "Aruba",
			"NC" => "New Caledonia",
			"ZM" => "Zambia",
			"NA" => "Namibia",
			"AU" => "Australia",
			"AT" => "Austria",
			"AS" => "American Samoa",
			"AR" => "Argentina",
			"AP" => "Non-spec",
			"AO" => "Angola",
			"MZ" => "Mozambique",
			"AN" => "Netherlands",
			"MY" => "Malaysia",
			"AM" => "Armenia",
			"MX" => "Mexico",
			"AL" => "Albania",
			"MW" => "Malawi",
			"MV" => "Maldives",
			"MU" => "Mauritius",
			"ZA" => "South Africa",
			"AI" => "Anguilla",
			"MT" => "Malta",
			"AG" => "Antigua And Barbuda",
			"MR" => "Mauritania",
			"AF" => "Afghanistan",
			"AE" => "United Arab Emirates",
			"MP" => "Northern Mariana Islands",
			"AD" => "Andorra",
			"MO" => "Martinique",
			"MN" => "Mongolia",
			"MM" => "Myanmar",
			"ML" => "Mali",
			"MK" => "Macedonia",
			"MG" => "Madagascar",
			"MD" => "Moldova",
			"MC" => "Monaco",
			"MA" => "Morocco",
			"YE" => "Yemen",
			"LY" => "Libya",
			"LV" => "Latvia",
			"LU" => "Luxembourg",
			"LT" => "Lithuania",
			"LS" => "Lesotho",
			"LR" => "Liberia",
			"LK" => "Sri Lanka",
			"LI" => "Liechtenstein",
			"LC" => "Saint Lucia",
			"LB" => "Lebanon",
			"LA" => "Laos",
			"KZ" => "Kazakhstan",
			"KY" => "Cayman Islands",
			"KW" => "Kuwait",
			"KR" => "Korea",
			"KN" => "Saint Kitts And Nevis",
			"KI" => "Kiribati",
			"KH" => "Cambodia",
			"WS" => "Samoa",
			"KG" => "Kyrgyzstan",
			"KE" => "Kenya",
			"JP" => "Japan",
			"JO" => "Jordan",
			"JM" => "Jamaica",
			"VU" => "Vanuatu",
			"VN" => "Viet Nam",
			"VI" => "U.S. Virgin Islands",
			"VG" => "British Virgin Islands",
			"VE" => "Venezuela",
			"VA" => "Vatican",
			"IT" => "Italy",
			"IS" => "Iceland",
			"IR" => "Iran",
			"IQ" => "Iraq",
			"UZ" => "Uzbekistan",
			"IO" => "British Indian Ocean Territory",
			"IN" => "India",
			"UY" => "Uruguay",
			"IL" => "Israel",
			"US" => "United States",
			"IE" => "Ireland",
			"ID" => "Indonesia",
			"UG" => "Uganda",
			"UA" => "Ukraine",
			"HU" => "Hungary",
			"HT" => "Haiti",
			"HR" => "Croatia",
			"TZ" => "Tanzania",
			"HN" => "Honduras",
			"TW" => "Taiwan",
			"HK" => "Hong Kong",
			"TV" => "Tuvalu",
			"TT" => "Trinidad And Tobago",
			"TR" => "Turkey",
			"00" => "Unknown",
			"TO" => "Tonga",
			"TN" => "Tunisia",
			"TM" => "Turkmenistan"
		);
	}

	private function woopra_country_flag($country) {
		return "<img src=\"http://static.woopra.com/images/flags/$country.png\" />";
	}

	private function woopra_browser_icon($browser) {
		$browser = strtolower($browser);
	    if (stripos($browser, "firefox") !== false) {
	        return $this->oopra_get_image("browsers/firefox");
	    }
	    if (stripos($browser, "explorer 7") !== false) {
	        return $this->woopra_get_image("browsers/ie7");
	    }
	    if (stripos($browser, "explorer 8") !== false) {
	        return $this->woopra_get_image("browsers/ie7");
	    }
	    if (stripos($browser, "explorer") !== false) {
	        return $this->woopra_get_image("browsers/ie");
	    }
	    if (stripos($browser, "safari") !== false) {
	        return $this->woopra_get_image("browsers/safari");
	    }
	    if (stripos($browser, "chrome") !== false) {
	        return $this->woopra_get_image("browsers/chrome");
	    }
	    if (stripos($browser, "opera") !== false) {
	        return $this->woopra_get_image("browsers/opera");
	    }
	    if (stripos($browser, "mozilla") !== false) {
	        return $this->woopra_get_image("browsers/mozilla");
	    }
	    if (stripos($browser, "netscape") !== false) {
	        return $this->woopra_get_image("browsers/netscape");
	    }
	    if (stripos($browser, "konqueror") !== false) {
	        return $this->woopra_get_image("browsers/konqueror");
	    }
	    if (stripos($browser, "unknown") !== false || stripos($browser, "other") !== false) {
	        return $this->woopra_get_image("browsers/unknown");
	    }
	    return "";
	}
	
	private function woopra_platform_icon($platform) {
		$platform = strtolower($platform);
	    if (stripos($platform, "windows") !== false) {
	        return $this->woopra_get_image("os/windows");
	    }
	    if (stripos($platform, "mac") !== false) {
	        return $this->woopra_get_image("os/mac");
	    }
	    if (stripos($platform, "apple") !== false) {
	        return $this->woopra_get_image("os/mac");
	    }
	    if (stripos($platform, "ubuntu") !== false) {
	        return $this->woopra_get_image("os/ubuntu");
	    }
	    if (stripos($platform, "redhat") !== false) {
	        return $this->woopra_get_image("os/redhat");
	    }
	    if (stripos($platform, "suse") !== false) {
	        return $this->woopra_get_image("os/suse");
	    }
	    if (stripos($platform, "fedora") !== false) {
	        return $this->woopra_get_image("os/fedora");
	    }
	    if (stripos($platform, "debian") !== false) {
	        return $this->woopra_get_image("os/debian");
	    }
	    if (stripos($platform, "linux") !== false) {
	        return $this->woopra_get_image("os/linux");
	    }
	    if (stripos($platform, "playstation") !== false) {
	        return $this->woopra_get_image("os/playstation");
	    }
	    if (stripos($platform, "unknown") !== false || stripos($platform, "other") !== false) {
	        return $this->woopra_get_image("browsers/unknown");
	    }
	    return "";
	}
	
	private function woopra_get_image($name) {
		return "<img src=\"http://static.woopra.com/images/$name.png\" />";
	}

	private function woopra_render_name($key, $name = null, $meta = null) {
		if (is_null($name)) {
			switch ($key) {
				case 'GET_COUNTRIES':
					return __('Country');
				case 'GET_VISITBOUNCES':
					return __('Pageviews per Visit');
				case 'GET_VISITDURATIONS':
					return __('Durations');
				case 'GET_BROWSERS':
					return __('Browser');
				case 'GET_PLATFORMS':
					return __('Platform');
				case 'GET_RESOLUTIONS':
					return __('Resolution');
				case 'GET_LANGUAGES':
					return __('Language');
				case 'GET_PAGEVIEWS':
					return __('Pages');
				case 'GET_PAGELANDINGS':
					return __('Landing Pages');
				case 'GET_PAGEEXITS':
					return __('Exit Pages');
				case 'GET_OUTGOINGLINKS':
					return __('Outgoing Links');
				case 'GET_DOWNLOADS':
					return __('Downloads');
				case 'GET_QUERIES':
					return __('Search Queries');
				case 'GET_KEYWORDS':
					return __('Keywords');
				default:
					return __('Name');
			}
		} else {
			switch ($key) {
				case 'GET_COUNTRIES':
					return $this->woopra_country_flag($name) . " " . $this->countries[$name];
				case 'GET_SPECIALVISITORS':
					$vars = Array();
					parse_str($meta, $vars);
					$avatar = 'http://static.woopra.com/images/avatar.png';
					if (isset($vars['avatar'])) {
						$avatar = $vars['avatar']; 
					}
					$toreturn .= '<img style="float: left; margin-right: 9px;" src="'.$avatar.'" width="32" height="32" /> ';
					$toreturn .= "<strong>$name</strong>";
					if (isset($vars['email'])) {
						$toreturn .= '<br/><small>(<a href="mailto:'.$vars['email'].'">'.$vars['email'].'</a>)</small>';
					}
					return $toreturn;
				case 'GET_VISITBOUNCES':
					$post_text = 'pageviews';
					if ($name == '1') {
						$post_text = 'pageview';
					}
					return $name . " " . $post_text;
				case 'GET_VISITDURATIONS':
					$name = str_replace('-', ' to ', $name);
					return $name . ' minutes';
				case 'GET_BROWSERS':
					return $this->woopra_browser_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'GET_PLATFORMS':
					return $this->woopra_platform_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'GET_PAGEVIEWS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'GET_PAGELANDINGS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'GET_PAGEEXITS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'GET_OUTGOINGLINKS':
					return "<a href=\"$name\" target=\"_blank\">$name</a>";
				case 'GET_DOWNLOADS':
					return "<a href=\"$name\" target=\"_blank\">$name</a>";
				default:
					return $name;
			}
		}
	}
	
	private function render_default_model($entries, $key) {
		?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $this->woopra_render_name($key); ?></th>
			<th class="center" width="100"><?php _e("Hits", 'woopra') ?></th>
			<th width="400">&nbsp;</th>
		</tr>
		<?php
		$counter = 0;
		$sum = $this->woopra_get_sum($entries, 'hits');
		foreach($entries as $entry) {
			$id = (int) $entry['id'];
			$name = urldecode($entry['name']);
			$hits = (int) $entry['hits'];
			$meta = urldecode($entry['meta']);
			$percent = 0;
			if ($sum != 0) {
				$percent = round($hits*100/$sum);
			}
			
			$hashid = $this->woopra_friendly_hash($key);
			?>
			<tr<?php echo (($counter++%2==0) ? " class=\"even_row\"" : ""); ?>>
				<td class="wrank"><?php echo $counter; ?></td>
				<td><span class="ellipsis"><?php echo $this->woopra_render_name($key,$name,$meta); ?></span></td>
				<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>_BY_DAY', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
				<td class="wbar"><?php echo $this->woopra_bar($percent); ?></td>
			</tr>
			<tr id="wlc-<?php echo $hashid; ?>-<?php echo $id; ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $id ?>" colspan="4"></td></tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	
	private function render_overview($entries) {	
	?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<th><?php _e("Day", 'woopra') ?></th>
			<th class="center"><?php _e("Avg Time Spent", 'woopra') ?></th>
			<th class="center"><?php _e("New Visitors", 'woopra') ?></th>
			<th class="center"><?php _e("Visits", 'woopra') ?></th>
			<th class="center"><?php _e("Pageviews", 'woopra') ?></th>
			<th width="400">&nbsp;</th>
		</tr>
		<?php
		$counter = 0;
		$max = $this->woopra_get_max($entries, 'pageviews');
		foreach($entries as $entry) {
			
			$pageviews = (int) $entry['pageviews'];
			$percent = round($pageviews*100/$max);
			$timespenttotal = (int) $entry['timespenttotal'];
			$timesamples = (int) $entry['timespentsamples'];
		
			$timespent = 0;
			if ($timesamples != 0) {
				$timespent = round(($timespenttotal/1000)/$timesamples);
			}
			$timespentstring = $this->woopra_seconds_to_string($timespent);
		
			$newvisitors =(int) $entry['newvisitors'];
			$visitors = (int) $entry['visitors'];
			$newvisitorsstring = "-";
			if ($newvisitors <= $visitors && $visitors != 0) {
				$newvisitorsstring = (int) ($newvisitors*100/$visitors) . '%';
			}
			?>
			<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
				<td class="whighlight"><?php echo $this->woopra_date_to_string($entry['day']); ?></td>
				<td class="center"><?php echo $timespentstring; ?></td>
				<td class="center"><?php echo $newvisitorsstring; ?></td>
				<td class="center" class="center"><?php echo $entry['visits']; ?></td>
				<td class="center whighlight"><?php echo $entry['pageviews']; ?></td>
				<td class="wbar"><?php echo $this->woopra_bar($percent); ?></td>
			</tr>
		<?php
		}
		?>
		</table>
	<?php
	}
	
	/** RENDERING RESULTS **/
	
	private function render_results() {
		
		if ($this->entries == null || sizeof($this->entries) == 0) {
			echo '<p align="center">' . __("Your query returned 0 results.", 'woopra') . '<br/>' . __('Click <a href="#" onclick="refreshCurrent(); return false;">here</a> to retry again!', 'woopra') . '</p>';
			return;
		} else {
			$this->sort_analytics_response($this->entries);
			
			if ($this->woopra_contains($this->key, 'BY_DAY')) {
				$this->render_chart_data($this->entries, $this->key);
				return;
			}
			
			if ($this->woopra_contains($this->key, 'GET_REFERRERS&')) {
				if ($this->woopra_contains($this->key, '&id=')) {
					$this->render_expanded_referrers($this->entries, $this->key);
				} else {
					$this->render_referrers($this->entries, $this->key);
				}
				return;
			}
			
			switch ($this->key) {
				case 'GET_GLOBALS':
					$this->render_overview($this->entries);
					break;
				case 'GET_COUNTRIES':
					$this->render_default_model($this->entries, 'GET_COUNTRIES');
					break;
				default:
					$this->render_default_model($this->entries, $this->key);
					break;
			}
		}
	}
	
}

?>