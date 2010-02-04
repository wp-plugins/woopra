<?php
/**
 * WoopraRender Class for Woopra
 *
 * This class contains all event related code for rendering the data.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage render
 */
class WoopraRender extends WoopraAdmin {
	
	/**
	 * Site API Key
	 * @since 1.4.1
	 * @var string
	 */
	var $api_key;
	
	/**
	 * API Page
	 * @since 1.5.0
	 * @var string
	 */
	var $api_page;
	
	/**
	 * Are we doing a flash data or reg.?
	 * @since 1.4.1
	 * @var string
	 */
	var $data_type;
	
	/**
	 * Number of items
	 * @since 1.4.1
	 * @var int
	 */
	var $limit = 50;
	
	/**
	 * Current offset for more than one page.
	 * @since 1.4.1
	 * @var int
	 */
	var $offset = 0;
	
	/**
	 * What is the hostname?
	 * @since 1.4.1
	 * @var string
	 */
	var $hostname;
	
	/**
	 * All data.
	 * @since 1.4.1
	 * @var array
	 */
	var $entries;
	
	/**
	 * Country array.
	 * @since 1.4.1
	 * @var array
	 */
	var $countries;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraRender() {
		$this->__construct();
	}

	/**
	 * Woopra Render
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		WoopraAdmin::__construct();
		Woopra::__construct();
		//	Country List Create
		$this->init_countries();
		//	Generate the Data
		$this->generate_data();	
	}
	
	/**
	 * Generate the data.
	 * @since 1.4.1
	 * @return 
	 */
	function generate_data() {
		// @todo $_GET == bad. There must be something else.
		if ( !empty($_GET['apipage']) ) {
			
			//	Set Hostname
			$this->hostname = get_option('siteurl');
			
			/**
			 * Process the $_GET
			 */
			
			//	Regular or Flash - Depending on Data is "Rendered"
			$this->data_type = $_GET['datatype'];
			//	API Query Page File
			$this->api_page = $_GET['apipage'];
			//	Website's API Key
			$this->api_key = $_GET['apikey'];
			
			$date_format = $_GET['date_format'];
			$start_date = $_GET['from'];
			$end_date = $_GET['to'];
			
			$xml_data = array(
				'dateFormat'	=>	$date_format,
				'startDay'		=>	$start_date,
				'endDay'		=>	$end_date,
				'limit'			=>	$this->limit,
				'offset'		=>	$this->offset,
			);
			
			//	Append TYPE to the XML Data Array -- Required in this order for the SOAP API Call
			if ( !empty($_GET['type']) )
				$xml_data = array_merge( array( 'type' => $_GET['type'] ), $xml_data );
			
			/** Process XML Class **/
			$xml_process = $this->process_xml("render", $xml_data);
			if ( is_wp_error($xml_process) )
				wp_die($xml_process->get_error_message());
			
			// Clear up memory!
			unset($xml_data, $xml_data_append);
			
			// Render Results
			$this->render_results();

		}
		exit;
	}
	
	/**
	 * Prccess the XML file.
	 * 
	 * @since 1.4.2
	 * 
	 * @param object $area
	 * @param object $xml_data
	 * @return 
	 */
	function process_xml($area, $xml_data) {
		
		if ( empty($area) )
			return new WP_Error('process-xml',sprintf( __('%s: Sorry. Where are we displaying this data? '), 'WoopraRender::process_xml(193)' ) );
		
		//	Set the Woopra XML Object
		$xml = new WoopraXML();
		
		//	Set XML Vars
		$xml->api_page = $this->api_page;
		$xml->api_key = $this->api_key;
		$xml->hostname = $this->woopra_host();
		
		//	Clear the Entries
		$this->entries = null;
		
		//	Start the XML Initilization		
		$xml_init = $xml->init();
		if ( is_wp_error($xml_init) )
			return is_wp_error($xml_init);
		
		//	Set and Query SOAP connection
		if ( $xml->set_xml($area, $xml_data) ) {
			//	Process the SOAP!
			$xml_process_data = $xml->process_data();
			if ( is_wp_error($xml_process_data) )
				return is_wp_error($xml_process_data);
		}
		//	 We do not need the index information here!
		$this->entries = $xml->data['data'];
		
		$xml->clear_data();
		unset($xml);
		return true;
	}
	
	/**
	 * Render the Results
	 * @since 1.4.1
	 * @return none
	 */
	function render_results() {
		
		if ( !is_array($this->entries) )
			return;
		
		$this->sort_analytics_response();
		
		if ($this->data_type != "regular") {
			$this->render_chart_data($this->entries);
			exit;
		}
		
		switch ($this->api_page) {
			case 'getGlobals':
				$this->render_overview($this->entries);
				break;
			default:
				$this->render_default_model($this->entries, $this->api_page);
				break;
		}
	}
	
	/**
	 * Render the Overview
	 * @since 1.4.1
	 * @return none
	 * @param object $entries
	 */
	function render_overview($entries) {
		arsort($entries);	// force arsort.
	?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<th class="text-header"><?php _e("Day", 'woopra') ?></th>
				<th class="text-header"><?php _e("Avg Time Spent", 'woopra') ?></th>
				<th class="text-header"><?php _e("New Visitors %", 'woopra') ?></th>
				<th class="text-header"><?php _e("Total Visits", 'woopra') ?></th>
				<th class="text-header"><?php _e("Page Views", 'woopra') ?></th>
				<th>&nbsp;</th>
			</tr>
			<?php
			$counter = 0;
			$max = $this->woopra_max($entries, 'totalPageviews');
			foreach($entries as $entry) {
				
				//	Time Code
				//$timespentstring = $this->woopra_ms_to_string((int) $entry['totalTimeSpent']);
				//	Vistor Code
				$newvisitors = (int) $entry['totalNewVisitors'];
				$visitors = (int) $entry['totalVisitors'];
				
				$visitorsstring = "-";
				if ($newvisitors <= $visitors && $visitors != 0) {
					$visitorsstring = (int) ($newvisitors*100/$visitors) . '%';
				}
				
				//	Page Views Code
				$pageviews = (int) $entry['totalPageviews'];
				//	Percent Code
				$percent = round($pageviews*100/$max);
				
				$hashid = $this->woopra_friendly_hash('GLOBALS');
				
				?>
				<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
					<td class="index"><?php echo $entry['day']; ?></td>
					<td class="text-item"><?php echo $timespentstring; ?></td>
					<td class="text-item"><?php echo $visitorsstring; ?></td>
					<td class="text-item"><?php echo number_format($visitors); ?></td>
					<td class="text-item highlight"><a href="#" onclick="return expandByDay('GLOBALS', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo number_format($pageviews); ?></a></td>
					<td class="bar"><?php echo $this->woopra_bar($percent); ?></td>
				</tr>
				<tr id="wlc-<?php echo $hashid; ?>-<?php echo $counter; ?>" style=" height: 120px; display: none;">
					<td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $counter ?>" colspan="6"></td>
				</tr>
			<?php
			}
		?>
		</table>
	<?php
	}
	
	/**
	 * Render the default model
	 * @since 1.4.1
	 * @return none
	 * @param object $entries
	 * @param object $key
	 */
	function render_default_model($entries, $key) {
		
		//	Show data from highest to lowest.
		$this->sort_analytics_response();
		
		?>
		
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<th>&nbsp;</th>
				<th class="text-header"><?php echo $this->woopra_render_name($key); ?></th>
				<th class="text-header" width="100"><?php _e("Hits", 'woopra') ?></th>
				<th width="400">&nbsp;</th>
			</tr>
			<?php
			
			$counter = 0;
			$sum = $this->woopra_sum($entries, 'totalVisits');
			
			foreach ($entries as $entry) {
				$name = urldecode($entry['name']);
				$hits = (int) $entry['totalVisits'];
				$meta = urldecode($entry['meta']);
				$percent = 0;
				if ($sum != 0) {
					$percent = round($hits*100/$sum);
				}
			
				$hashid = $this->woopra_friendly_hash($key);
				?>
				<tr<?php echo (($counter++%2==0) ? " class=\"even_row\"" : ""); ?>>
					<td class="index"><?php echo $counter; ?></td>
					<td><span class="ellipsis"><?php echo $this->woopra_render_name($key, $name, $meta); ?></span></td>
					<td width="100" class="text-item highlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo $hits; ?></a></td>
					<td class="bar"><?php echo $this->woopra_bar($percent); ?></td>
				</tr>
				<tr id="wlc-<?php echo $hashid; ?>-<?php echo $counter; ?>" style=" height: 120px; display: none;">
					<td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $counter ?>" colspan="4"></td>
				</tr>
			<?php } ?>
		</table>
		<?php
	}
	
	/**
	 * Render Referrers Section
	 * @return 
	 * @param object $entries
	 * @param object $key
	 */
	function render_referrers($entries, $key) {
		
		?>
		<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th class="text-header"><?php _e('Referrer', 'woopra'); ?></th>
			<th class="text-header" width="100"><?php _e('Hits', 'woopra'); ?></th>
			<th width="400">&nbsp;</th>
		</tr>
		<?php
		
		$counter = 0;
		$sum = $this->woopra_sum($entries, 'vts');
		
		foreach($entries as $index => $entry) {
			$name = urldecode($entry['name']);
			$hits = (int) $entry['vts'];
			$meta = urldecode($entry['meta']);
			
			$percent = 0;
			if ($sum != 0)
				$percent = round($hits*100/$sum);
			
			$hashid = $this->woopra_friendly_hash($key);
			?>
			<tr<?php echo (($counter++%2==0) ? " class=\"even_row\"" : "" ); ?>>
				<td class="index"><?php echo $counter; ?></td>
				<td><span class="ellipsis"><a href="<?php echo $name; ?>" target="_blank"><?php echo $this->woopra_render_name($key, $name, $meta); ?></a></span></td>
				<td width="100" class="text-item highlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo $hits; ?></a></td>
				<td class="bar"><?php echo $this->woopra_bar($percent) ?></td>
			</tr>
			<tr id="wlc-<?php echo $hashid; ?>-<?php echo $counter ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid ?>-<?php echo $counter; ?>" colspan="4"></td></tr>
			<tr id="refexp-<?php echo $hashid; ?>-<?php echo $counter; ?>" style="display: none;"><td colspan="4" style="padding: 0;"><div id="refexptd-<?php echo $hashid; ?>-<?php echo $counter; ?>"></div></td></tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	
	/**
	 * Render the chart data format. Using Open-Flash-2 PHP Librarys
	 * @since 1.4.3
	 * @param object $entries
	 * @return 
	 */
	function render_chart_data($entries) {
		$chart = new WoopraChart();
		if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
			unset($chart);
			exit;
		}
		foreach ($entries as $index => $entry) {
			if ($entry['index'] == $_GET['id'])
				$chart->data = $entry;
		}
		echo $chart->render();
		unset($chart);
		exit;
	}
	
	/** PRIVATE FUNCTIONS **/
	
	/**
	 * Milliseconds to Mintues and Seconds
	 * @since 1.4.1
	 * @return string
	 * @param object $milli
	 */
	function woopra_ms_to_string($milli) {
		$minutes = (($milli % (1000*60*60)) / (1000*60));
		$seconds = ((($milli % (1000*60*60)) % (1000*60)) / 1000);
		return sprintf(__("%dm, %ds", 'woopra'), $minutes, $seconds);
	}
	
	/**
	 * Rendering the table name headers.
	 * @since 1.4.1
	 * @return mixed
	 * @param object $key
	 * @param object $name[optional]
	 * @param object $meta[optional]
	 */
	function woopra_render_name($key, $name = null, $meta = null) {
		if (is_null($name)) {
			switch ($key) {
				case 'getCountries':
					return __('Country', 'woopra');
				case 'getVisitBounces':
					return __('Pageviews per Visit', 'woopra');
				case 'getVisitDurations':
					return __('Durations', 'woopra');
				case 'getBrowsers':
					return __('Browser', 'woopra');
				case 'getPlatforms':
					return __('Platform', 'woopra');
				case 'getResolutions':
					return __('Resolution', 'woopra');
				case 'getLanguages':
					return __('Language', 'woopra');
				case 'getPageviews':
					return __('Pages', 'woopra');
				case 'getPageLandings':
					return __('Landing Pages', 'woopra');
				case 'getPageExits':
					return __('Exit Pages', 'woopra');
				case 'getOutgoingLinks':
					return __('Outgoing Links', 'woopra');
				case 'getDownloads':
					return __('Downloads', 'woopra');
				case 'getQueries':
					return __('Search Queries', 'woopra');
				case 'getKeywords':
					return __('Keywords', 'woopra');
				default:
					return __('Name', 'woopra');
			}
		} else {
			switch ($key) {
				case 'getCountries':
					return $this->country_flag($name) . " " . $this->countries[$name];
					
				/**  Ummm... do we do this anymore anyway? **/
				case 'SPECIALVISITORS':
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
					
					
				case 'getVisitBounces':
					return sprintf(_n("%s pageview", "%s pageviews", $name), $name);
				case 'getBrowsers':
					return $this->browser_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'getPlatforms':
					return $this->platform_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'getPageviews':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'getPageLandings':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'getPageExits':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'getOutgoingLinks':
					return "<a href=\"$name\" target=\"_blank\">$name</a>";
				case 'getDownloads':
					return "<a href=\"$name\" target=\"_blank\">$name</a>";
				default:
					return $name;
			}
		}
	}
	
	/**
	 * Return a pretty version of the hostname.
	 * @since 1.4.1
	 * @return string
	 */
	function woopra_host() {
		$site = $this->hostname;
		preg_match('@^(?:http://)?([^/]+)@i', $site, $matches);
		$host = $matches[1];
		return preg_replace('!^www\.!i', '', $host);
	}
	
	/**
	 * Find out the largest thing of hits so we can make the one bar '100%'
	 * @since 1.4.1
	 * @return int
	 * @param object $entries
	 * @param object $key
	 */
	function woopra_sum($entries, $key) {
		$sum = 0;
		foreach ($entries as $entry) {
			$val = (int) $entry[$key];
			$sum = $sum + $val;
		}
		return $sum;
	}
	
	/**
	 * What is the MAX number?
	 * @return 
	 * @param object $entries
	 * @param object $key
	 */
	function woopra_max($entries, $key) {
		$max = 0;
		foreach ($entries as $entry) {
			$val = (int) $entry[$key];
			if ($val > $max)
				$max = $val;
		}
		
		return $max;
	}
	
	/** IMAGE FUNCTIONS **/
	
	/**
	 * Create Flag
	 * @since 1.4.1
	 * @return 
	 * @param object $country
	 */
	function country_flag($country) {
		return "<img src=\"http://static.woopra.com/images/flags/$country.png\" />";
	}
	
	/**
	 * Get the broswer image.
	 * @since 1.4.1
	 * @return string
	 * @param object $browser
	 */
	function browser_icon($browser) {
		$browser = strtolower($browser);
		if ( stripos($browser, "firefox") !== false ) {
			return $this->woopra_image("browsers/firefox");
		}
		if ( stripos($browser, "explorer 7") !== false ) {
			return$this->woopra_image("browsers/ie7");
		}
		if ( stripos($browser, "explorer 8") !== false ) {
			return$this->woopra_image("browsers/ie7");	//	should this me updated?
		}
		if ( stripos($browser, "explorer") !== false ) {
			return$this->woopra_image("browsers/ie");
		}
		if ( stripos($browser, "safari") !== false ) {
			return$this->woopra_image("browsers/safari");
		}
		if ( stripos($browser, "chrome") !== false ) {
			return$this->woopra_image("browsers/chrome");
		}
		if ( stripos($browser, "opera") !== false ) {
			return$this->woopra_image("browsers/opera");
		}
		if ( stripos($browser, "mozilla") !== false ) {
			return$this->woopra_image("browsers/mozilla");
		}
		if ( stripos($browser, "netscape") !== false ) {
			return$this->woopra_image("browsers/netscape");
		}
		if ( stripos($browser, "konqueror") !== false ) {
			return$this->woopra_image("browsers/konqueror");
		}
		if ( stripos($browser, "iphone") !== false ) {
			return $this->woopra_image("os/mac");
		}
		if ( stripos($browser, "unknown") !== false || stripos($browser, "other") !== false ) {
			return$this->woopra_image("browsers/unknown");
		}
		return "";
	}
	
	/**
	 * Platform Icon
	 * @since 1.4.1
	 * @return string
	 * @param object $platform
	 */
	function platform_icon($platform) {
		$platform = strtolower($platform);
		if ( stripos($platform, "windows") !== false ) {
			return$this->woopra_image("os/windows");
		}
		if ( stripos($platform, "mac") !== false ) {
			return$this->woopra_image("os/mac");
		}
		if ( stripos($platform, "apple") !== false ) {
			return$this->woopra_image("os/mac");
		}
		if ( stripos($platform, "ubuntu") !== false ) {
			return$this->woopra_image("os/ubuntu");
		}
		if ( stripos($platform, "redhat") !== false ) {
			return$this->woopra_image("os/redhat");
		}
		if ( stripos($platform, "suse") !== false ) {
			return$this->woopra_image("os/suse");
		}
		if ( stripos($platform, "fedora") !== false ) {
			return$this->woopra_image("os/fedora");
		}
		if ( stripos($platform, "debian") !== false ) {
			return$this->woopra_image("os/debian");
		}
		if ( stripos($platform, "linux") !== false ) {
			return$this->woopra_image("os/linux");
		}
		if ( stripos($platform, "playstation") !== false ) {
			return$this->woopra_image("os/playstation");
		}
		if ( stripos($platform, "nokia mobile") !== false ) {
			return$this->woopra_image("browsers/unknown");
		}
		if ( stripos($platform, "unknown") !== false || stripos($platform, "other") !== false ) {
			return$this->woopra_image("browsers/unknown");
		}
		return "";
	}
	
	/**
	 * Create Woopra Image
	 * @since 1.4.1
	 * @return 
	 * @param object $name
	 */
	function woopra_image($name) {
		return "<img src=\"http://static.woopra.com/images/$name.png\" />";
	}
	
	/**
	 * Create BAR Parcent
	 * @return 
	 * @param object $percent
	 */	
	function woopra_bar($percent) {
		//	@todo Add more colors!
		$barurl = $this->plugin_url() . '/images/bar.png';
		$width = $percent . "%";
		if ($percent < 1)
			$width = "1";
		
		return '<img src="'.$barurl.'" width="'.$width.'" height="16" />';
	}
	
	/** INITILIZED FUNCTIONS ONLY!!! **/
	
	/**
	 * Return the country list.
	 * @since 1.4.1
	 * @return array
	 */
	function init_countries() {
		$this->countries = array(
			"TJ" => _("Tajikistan"),
			"TH" => _("Thailand"),
			"TG" => _("Togo"),
			"GY" => _("Guyana"),
			"GW" => _("Guinea-bissau"),
			"GU" => _("Guam"),
			"GT" => _("Guatemala"),
			"GR" => _("Greece"),
			"GP" => _("Guadeloupe"),
			"SZ" => _("Swaziland"),
			"SY" => _("Syria"),
			"GM" => _("Gambia"),
			"GL" => _("Greenland"),
			"SV" => _("El Salvador"),
			"GI" => _("Gibraltar"),
			"GH" => _("Ghana"),
			"SR" => _("Suriname"),
			"GF" => _("French Guiana"),
			"GE" => _("Georgia"),
			"GD" => _("Grenada"),
			"SN" => _("Senegal"),
			"GB" => _("United Kingdom"),
			"SM" => _("San Marino"),
			"GA" => _("Gabon"),
			"SL" => _("Sierra Leone"),
			"SK" => _("Slovakia"),
			"SI" => _("Slovenia"),
			"SG" => _("Singapore"),
			"SE" => _("Sweden"),
			"SD" => _("Sudan"),
			"SC" => _("Seychelles"),
			"SB" => _("Solomon Islands"),
			"SA" => _("Saudi Arabia"),
			"FR" => _("France"),
			"FO" => _("Faroe Islands"),
			"FM" => _("Micronesia"),
			"RW" => _("Rwanda"),
			"FJ" => _("Fiji"),
			"RU" => _("Russia"),
			"FI" => _("Finland"),
			"RS" => _("Serbia"),
			"RO" => _("Romania"),
			"EU" => _("European Union"),
			"ET" => _("Ethiopia"),
			"ES" => _("Spain"),
			"ER" => _("Eritrea"),
			"EG" => _("Egypt"),
			"EE" => _("Estonia"),
			"EC" => _("Ecuador"),
			"DZ" => _("Algeria"),
			"QA" => _("Qatar"),
			"DO" => _("Dominican Republic"),
			"PY" => _("Paraguay"),
			"PW" => _("Palau"),
			"DK" => _("Denmark"),
			"DJ" => _("Djibouti"),
			"PT" => _("Portugal"),
			"PS" => _("Palestine"),
			"PR" => _("Puerto Rico"),
			"DE" => _("Germany"),
			"PL" => _("Poland"),
			"PK" => _("Pakistan"),
			"PH" => _("Philippines"),
			"PG" => _("Papua New Guinea"),
			"CZ" => _("Czech Republic"),
			"PF" => _("French Polynesia"),
			"CY" => _("Cyprus"),
			"PE" => _("Peru"),
			"CU" => _("Cuba"),
			"PA" => _("Panama"),
			"CS" => _("Serbia"),
			"CR" => _("Costa Rica"),
			"CO" => _("Colombia"),
			"CN" => _("China"),
			"CM" => _("Cameroon"),
			"CL" => _("Chile"),
			"CK" => _("Cook Islands"),
			"CI" => _("Cote D'ivoire"),
			"CH" => _("Switzerland"),
			"CF" => _("Central African Republic"),
			"CD" => _("Congo"),
			"OM" => _("Oman"),
			"CA" => _("Canada"),
			"BZ" => _("Belize"),
			"BY" => _("Belarus"),
			"BW" => _("Botswana"),
			"BT" => _("Bhutan"),
			"BS" => _("Bahamas"),
			"BR" => _("Brazil"),
			"BO" => _("Bolivia"),
			"NZ" => _("New Zealand"),
			"BN" => _("Brunei Darussalam"),
			"BM" => _("Bermuda"),
			"BJ" => _("Benin"),
			"NU" => _("Niue"),
			"BI" => _("Burundi"),
			"BH" => _("Bahrain"),
			"BG" => _("Bulgaria"),
			"NR" => _("Nauru"),
			"BF" => _("Burkina Faso"),
			"BE" => _("Belgium"),
			"NP" => _("Nepal"),
			"BD" => _("Bangladesh"),
			"NO" => _("Norway"),
			"BB" => _("Barbados"),
			"BA" => _("Bosnia And Herzegowina"),
			"NL" => _("Netherlands"),
			"ZW" => _("Zimbabwe"),
			"NI" => _("Nicaragua"),
			"NG" => _("Nigeria"),
			"AZ" => _("Azerbaijan"),
			"NF" => _("Norfolk Island"),
			"AX" => _("�land Islands"),
			"AW" => _("Aruba"),
			"NC" => _("New Caledonia"),
			"ZM" => _("Zambia"),
			"NA" => _("Namibia"),
			"AU" => _("Australia"),
			"AT" => _("Austria"),
			"AS" => _("American Samoa"),
			"AR" => _("Argentina"),
			"AP" => _("Non-spec"),
			"AO" => _("Angola"),
			"MZ" => _("Mozambique"),
			"AN" => _("Netherlands"),
			"MY" => _("Malaysia"),
			"AM" => _("Armenia"),
			"MX" => _("Mexico"),
			"AL" => _("Albania"),
			"MW" => _("Malawi"),
			"MV" => _("Maldives"),
			"MU" => _("Mauritius"),
			"ZA" => _("South Africa"),
			"AI" => _("Anguilla"),
			"MT" => _("Malta"),
			"AG" => _("Antigua And Barbuda"),
			"MR" => _("Mauritania"),
			"AF" => _("Afghanistan"),
			"AE" => _("United Arab Emirates"),
			"MP" => _("Northern Mariana Islands"),
			"AD" => _("Andorra"),
			"MO" => _("Martinique"),
			"MN" => _("Mongolia"),
			"MM" => _("Myanmar"),
			"ML" => _("Mali"),
			"MK" => _("Macedonia"),
			"MG" => _("Madagascar"),
			"MD" => _("Moldova"),
			"MC" => _("Monaco"),
			"MA" => _("Morocco"),
			"YE" => _("Yemen"),
			"LY" => _("Libya"),
			"LV" => _("Latvia"),
			"LU" => _("Luxembourg"),
			"LT" => _("Lithuania"),
			"LS" => _("Lesotho"),
			"LR" => _("Liberia"),
			"LK" => _("Sri Lanka"),
			"LI" => _("Liechtenstein"),
			"LC" => _("Saint Lucia"),
			"LB" => _("Lebanon"),
			"LA" => _("Laos"),
			"KZ" => _("Kazakhstan"),
			"KY" => _("Cayman Islands"),
			"KW" => _("Kuwait"),
			"KR" => _("Korea"),
			"KN" => _("Saint Kitts And Nevis"),
			"KI" => _("Kiribati"),
			"KH" => _("Cambodia"),
			"WS" => _("Samoa"),
			"KG" => _("Kyrgyzstan"),
			"KE" => _("Kenya"),
			"JP" => _("Japan"),
			"JO" => _("Jordan"),
			"JM" => _("Jamaica"),
			"VU" => _("Vanuatu"),
			"VN" => _("Viet Nam"),
			"VI" => _("U.S. Virgin Islands"),
			"VG" => _("British Virgin Islands"),
			"VE" => _("Venezuela"),
			"VA" => _("Vatican"),
			"IT" => _("Italy"),
			"IS" => _("Iceland"),
			"IR" => _("Iran"),
			"IQ" => _("Iraq"),
			"UZ" => _("Uzbekistan"),
			"IO" => _("British Indian Ocean Territory"),
			"IN" => _("India"),
			"UY" => _("Uruguay"),
			"IL" => _("Israel"),
			"US" => _("United States"),
			"IE" => _("Ireland"),
			"ID" => _("Indonesia"),
			"UG" => _("Uganda"),
			"UA" => _("Ukraine"),
			"HU" => _("Hungary"),
			"HT" => _("Haiti"),
			"HR" => _("Croatia"),
			"TZ" => _("Tanzania"),
			"HN" => _("Honduras"),
			"TW" => _("Taiwan"),
			"HK" => _("Hong Kong"),
			"TV" => _("Tuvalu"),
			"TT" => _("Trinidad And Tobago"),
			"TR" => _("Turkey"),
			"00" => _("Unknown"),
			"TO" => _("Tonga"),
			"TN" => _("Tunisia"),
			"TM" => _("Turkmenistan")
		);
	}
	
	/** OVERRIDE PHP FUNCTIONS **/
	
	/**
	 * Quick Method of strpos
	 * @since 1.4.1
	 * @return boolean
	 * @param object $str
	 * @param object $sub
	 * @uses strpos
	 */
	function woopra_contains($str, $sub) {
		return strpos($str, $sub) !== false;
	}
	
	/**
	 * Encode Woopra String
	 * @since 1.4.1
	 * @return string
	 * @param object $string
	 * @uses str_replace
	 */
	function woopra_encode($string) {
		return str_replace(',', '%2C', urlencode($string));
	}
	
	/**
	 * Create a 'nice' hash.
	 * @since 1.4.1
	 * @return string
	 * @param object $value
	 * @uses substr
	 * @uses md5
	 */
	function woopra_friendly_hash($value) {
		return substr(md5($value),0,4);
	}
	
	/**
	 * Sort Analytics
	 * @since 1.4.1
	 * @return none
	 */
	function sort_analytics_response() {
		usort($this->entries, array(&$this, 'compare_analytics_entries'));
	}
	
	/**
	 * Compares fields.
	 * @return boolean
	 * @param object $entry1
	 * @param object $entry2
	 * @users sort_analytics_response
	 */
	function compare_analytics_entries($entry1, $entry2) {
		$sort_by = (isset($entry1['day'])?'day':'totalVisits');
		
		$v1 = (int)$entry1[$sort_by];
		$v2 = (int)$entry2[$sort_by];
			
		if ($v1 == $v2)
			return -1;
		return ($v1 > $v2)?-1:1;	
	}
	
}

?>