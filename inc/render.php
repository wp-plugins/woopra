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
require_once('xml.php');
class WoopraRender extends WoopraAdmin {

	/**
	 * The Stats Area
	 * @since 1.4.1
	 * @var string
	 */
	var $key;
	
	/**
	 * Site API Key
	 * @since 1.4.1
	 * @var string
	 */
	var $api_key;
	
	/**
	 * Are we doing a flash data or reg.?
	 * @since 1.4.1
	 * @var string
	 */
	var $data_type;
	
	/**
	 * Starting Date
	 * @since 1.4.1
	 * @var string
	 */
	var $date_from;
	
	/**
	 * Ending Date
	 * @since 1.4.1
	 * @var string
	 */
	var $date_to;
	
	/**
	 * Number of items
	 * 
	 * Note: Soon to be @decrepeted
	 * 
	 * @since 1.4.1
	 * @var int
	 */
	var $limit = 50;	// hardcoded now...
	
	/**
	 * Current offset for more than one page.
	 * @since 1.4.1
	 * @var int
	 */
	var $offset = 0;
	
	/**
	 * What is the hostname
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

	/** THIS IS CUSTOM CODE THAT CAN BE DELETED LATER ON **/

	/**
	 * Generate the data.
	 * @since 1.4.1
	 * @return 
	 */
	function generate_data() {
		if (isset($_GET['wkey'])) {
			$this->data_type = $_GET['datatype'];
			$this->api_key = $_GET['apikey'];
			$this->key = str_replace("&amp;", "&", $_GET['wkey']);
			
			if ($_GET['type'])
				$this->key = $this->key . "&type=" . $_GET['type'];
			
			$this->hostname = get_option('siteurl');
			
			$date_format = $_GET['date_format'];
			$start_date = $_GET['from'];
			$end_date = $_GET['to'];
			
			/** LAST LINES **/
			if ($this->process_xml($this->key, $date_format, $start_date, $end_date, $this->limit, $this->offset)) {
				$this->render_results();
			}
		}
		exit;
	}
	
	/**
	 * Prccess the XML file.
	 * @since 1.4.1
	 * @return 
	 * @param object $key
	 * @param object $date_format
	 * @param object $start_date
	 * @param object $end_date
	 * @param object $limit
	 * @param object $offset
	 */
	function process_xml($key, $date_format, $start_date, $end_date, $limit, $offset) {
		
		$xml = new WoopraXML;
		
		$xml->api_key = $this->api_key;
		$xml->hostname = $this->woopra_host();
	
		$this->entries = null;
		if ($xml->init()) {
			if ($xml->set_xml($key, $date_format, $start_date, $end_date, $limit, $offset)) {
				if ($xml->process_data()) {
					$this->entries = $xml->data;
				}
			}
		}
		
		if ($xml->connection_error != null || $xml->error_msg != null || !$xml->init()) {
			echo '<div class="error"><p>' . sprintf(__('The Woopra Plugin was not able to request your analytics data from the Woopra Engines<br/><small>Your hosting provider is not allowing the Woopra Plugin to fetch data from the Woopra Servers.<br/>%s<br/><a href="http://www.woopra.com/forums/">Report this error onto the forums!</a><br/>', 'woopra'), $xml->connection_error . $xml->error_msg) . '</small></p></div>';
			return false;
		}
		
		if (($this->entries == null) || (count($this->entries) == 0)) {
			echo '<p align="center">' . _('Your query returned 0 results.<br/>Click <a href="#" onclick="refreshCurrent(); return false;">here</a> to retry again!') . '</p>';
			return;
		}
		
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

		$this->sort_analytics_response();
		
		if ($this->data_type != "regular") {
			$this->render_chart_data($this->entries);
			exit;
		}
		
		if ($this->woopra_contains($this->key, 'REFERRERS')) {
			$this->render_referrers($this->entries, $this->key);
			exit;
		}
		
		switch ($this->key) {
			case 'GLOBALS':
				$this->render_overview($this->entries);
				break;
			case 'COUNTRIES':
				$this->render_default_model($this->entries, 'COUNTRIES');
				break;
			default:
				$this->render_default_model($this->entries, $this->key);
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
				<th class="center"><?php _e("Day", 'woopra') ?></th>
				<th class="center"><?php _e("Avg Time Spent", 'woopra') ?></th>
				<th class="center"><?php _e("New Visitors", 'woopra') ?></th>
				<th class="center"><?php _e("Total Visits", 'woopra') ?></th>
				<th class="center"><?php _e("Pageviews", 'woopra') ?></th>
				<th>&nbsp;</th>
			</tr>
			<?php
			
			$counter = 0;
			$max = $this->woopra_max($entries, 'pvs');
			foreach($entries as $entry) {
				
				//	Time Code
				$timespentstring = $this->woopra_ms_to_string((int) $entry['timespent']);
				//	Vistor Code
				$newvisitors = (int) $entry['newvtrs'];
				$visitors = (int) $entry['vts'];
				// @todo Code to figure out % of new vistors
				//	Page Views Code
				$pageviews = (int) $entry['pvs'];
				//	Percent Code
				$percent = round($pageviews*100/$max);
				
				
				$hashid = $this->woopra_friendly_hash('GLOBALS');
				
				?>
				<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
					<td class="wrank"><?php echo $entry['day']; ?></td>
					<td class="center"><?php echo $timespentstring; ?></td>
					<td class="center"><?php echo $visitorsstring; ?></td>
					<td class="center"><?php echo number_format($visitors); ?></td>
					<td class="center highlight"><a href="#" onclick="return expandByDay('GLOBALS', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo number_format($pageviews); ?></a></td>
					<td class="wbar"><?php echo $this->woopra_bar($percent); ?></td>
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
			$sum = $this->woopra_sum($entries, 'vts');
			
			foreach($entries as $index => $entry) {
				$name = urldecode($entry['name']);
				$hits = (int) $entry['vts'];
				$meta = urldecode($entry['meta']);
				$percent = 0;
				if ($sum != 0) {
					$percent = round($hits*100/$sum);
				}
				$hashid = $this->woopra_friendly_hash($key);
				?>
				<tr<?php echo (($counter++%2==0) ? " class=\"even_row\"" : ""); ?>>
					<td class="wrank"><?php echo $counter; ?></td>
					<td><span class="ellipsis"><?php echo $this->woopra_render_name($key, $name, $meta); ?></span></td>
					<td width="100" class="center highlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo $hits; ?></a></td>
					<td class="wbar"><?php echo $this->woopra_bar($percent); ?></td>
				</tr>
				<tr id="wlc-<?php echo $hashid; ?>-<?php echo $counter; ?>" style=" height: 120px; display: none;">
					<td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $counter ?>" colspan="4"></td>
				</tr>
			<?php
		}
		?>
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
			<th><?php _e('Referrer', 'woopra'); ?></th>
			<th class="center" width="100"><?php _e('Hits', 'woopra'); ?></th>
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
				<td class="wrank"><?php echo $counter; ?></td>
				<td><span class="ellipsis"><a href="<?php echo $name; ?>" target="_blank"><?php echo $this->woopra_render_name($key, $name, $meta); ?></a></span></td>
				<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>', '<?php echo $hashid; ?>', <?php echo $counter; ?>, <?php echo $entry['index']; ?>)"><?php echo $hits; ?></a></td>
				<td class="wbar"><?php echo $this->woopra_bar($percent) ?></td>
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
	 * @param object $entries
	 * @return 
	 */
	function render_chart_data($entries) {
	
		$chart = new WoopraChart;

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
				case 'COUNTRIES':
					return __('Country', 'woopra');
				case 'VISITBOUNCES':
					return __('Pageviews per Visit', 'woopra');
				case 'VISITDURATIONS':
					return __('Durations', 'woopra');
				case 'BROWSERS':
					return __('Browser', 'woopra');
				case 'PLATFORMS':
					return __('Platform', 'woopra');
				case 'RESOLUTIONS':
					return __('Resolution', 'woopra');
				case 'LANGUAGES':
					return __('Language', 'woopra');
				case 'PAGEVIEWS':
					return __('Pages', 'woopra');
				case 'PAGELANDINGS':
					return __('Landing Pages', 'woopra');
				case 'PAGEEXITS':
					return __('Exit Pages', 'woopra');
				case 'OUTGOINGLINKS':
					return __('Outgoing Links', 'woopra');
				case 'DOWNLOADS':
					return __('Downloads', 'woopra');
				case 'QUERIES':
					return __('Search Queries', 'woopra');
				case 'KEYWORDS':
					return __('Keywords', 'woopra');
				default:
					return __('Name', 'woopra');
			}
		} else {
			switch ($key) {
				case 'COUNTRIES':
					return $this->woopra_country_flag($name) . " " . $this->countries[$name];
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
				case 'VISITBOUNCES':
					$post_text = 'pageviews';
					if ($name == '1') {
						$post_text = 'pageview';
					}
					return $name . " " . $post_text;
				case 'VISITDURATIONS':
					$name = str_replace('-', ' to ', $name);
					return $name . ' minutes';
				case 'BROWSERS':
					return $this->woopra_browser_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'PLATFORMS':
					return $this->woopra_platform_icon($name) . "&nbsp;&nbsp;" . $name;
				case 'PAGEVIEWS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'PAGELANDINGS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'PAGEEXITS':
					return $meta . "<br/>" . "<small><a href=\"http://".$this->woopra_host()."$name\" target=\"_blank\">$name</a></small>";
				case 'OUTGOINGLINKS':
					return "<a href=\"$name\" target=\"_blank\">$name</a>";
				case 'DOWNLOADS':
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
	
	/**
	 * Create Flag
	 * @since 1.4.1
	 * @return 
	 * @param object $country
	 */
	function woopra_country_flag($country) {
		return "<img src=\"http://static.woopra.com/images/flags/$country.png\" />";
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
	
	/**
	 * Get the broswer image.
	 * @since 1.4.1
	 * @return string
	 * @param object $browser
	 */
	function woopra_browser_icon($browser) {
		$browser = strtolower($browser);
	    if (stripos($browser, "firefox") !== false) {
	        return $this->woopra_image("browsers/firefox");
	    }
	    if (stripos($browser, "explorer 7") !== false) {
	        return $this->woopra_image("browsers/ie7");
	    }
	    if (stripos($browser, "explorer 8") !== false) {
	        return $this->woopra_image("browsers/ie7");	//	should this me updated?
	    }
	    if (stripos($browser, "explorer") !== false) {
	        return $this->woopra_image("browsers/ie");
	    }
	    if (stripos($browser, "safari") !== false) {
	        return $this->woopra_image("browsers/safari");
	    }
	    if (stripos($browser, "chrome") !== false) {
	        return $this->woopra_image("browsers/chrome");
	    }
	    if (stripos($browser, "opera") !== false) {
	        return $this->woopra_image("browsers/opera");
	    }
	    if (stripos($browser, "mozilla") !== false) {
	        return $this->woopra_image("browsers/mozilla");
	    }
	    if (stripos($browser, "netscape") !== false) {
	        return $this->woopra_image("browsers/netscape");
	    }
	    if (stripos($browser, "konqueror") !== false) {
	        return $this->woopra_image("browsers/konqueror");
	    }
	    if (stripos($browser, "unknown") !== false || stripos($browser, "other") !== false) {
	        return $this->woopra_image("browsers/unknown");
	    }
	    return "";
	}
	
	/**
	 * Platform Icon
	 * @since 1.4.1
	 * @return string
	 * @param object $platform
	 */
	function woopra_platform_icon($platform) {
		$platform = strtolower($platform);
	    if (stripos($platform, "windows") !== false) {
	        return $this->woopra_image("os/windows");
	    }
	    if (stripos($platform, "mac") !== false) {
	        return $this->woopra_image("os/mac");
	    }
	    if (stripos($platform, "apple") !== false) {
	        return $this->woopra_image("os/mac");
	    }
	    if (stripos($platform, "ubuntu") !== false) {
	        return $this->woopra_image("os/ubuntu");
	    }
	    if (stripos($platform, "redhat") !== false) {
	        return $this->woopra_image("os/redhat");
	    }
	    if (stripos($platform, "suse") !== false) {
	        return $this->woopra_image("os/suse");
	    }
	    if (stripos($platform, "fedora") !== false) {
	        return $this->woopra_image("os/fedora");
	    }
	    if (stripos($platform, "debian") !== false) {
	        return $this->woopra_image("os/debian");
	    }
	    if (stripos($platform, "linux") !== false) {
	        return $this->woopra_image("os/linux");
	    }
	    if (stripos($platform, "playstation") !== false) {
	        return $this->woopra_image("os/playstation");
	    }
	    if (stripos($platform, "unknown") !== false || stripos($platform, "other") !== false) {
	        return $this->woopra_image("browsers/unknown");
	    }
	    return "";
	}
	
	/**
	 * Return the country list.
	 * @since 1.4.1
	 * @return array
	 */
	function init_countries() {
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
		$sort_by = (isset($entry1['day'])?'day':'vts');
		
		$v1 = (int)$entry1[$sort_by];
		$v2 = (int)$entry2[$sort_by];
			
		if ($v1 == $v2)
			return -1;
		return ($v1 > $v2)?-1:1;	
	}
	
}

?>
