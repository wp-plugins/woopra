<?php
/**
 * WoopraAdmin Class for Woopra
 *
 * This class contains all functions and actions required
 * for Woopra to work on the backend of WordPress.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage admin
 */
class WoopraAdmin extends Woopra {

	/**
	 * Current Event
	 * @since 1.4.1
	 * @var array
	 */
	var $event;
	
	/**
	 * All the events.
	 * @since 1.4.1
	 * @var array
	 */
	var $_events;

	/**
	 * The plugin file.
	 * @since 1.4.1
	 * @var string
	 */
	var $plugin_file;
	
	/**
	 * The plugin basename.
	 * @since 1.4.1
	 * @var string
	 */
	var $plugin_basename;


	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraAdmin() {
		$this->__construct();
	}
	
	/**
	 * Frontend Contructor Class
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		Woopra::__construct();
		
		//	Should we be upgrading the options?
		if ( version_compare( $this->get_option('version'), $this->version, '!=' ) && !empty($this->options) )
			$this->check_upgrade();
		
		//	Store Plugin Location Information
		$this->plugin_file = dirname( dirname ( __FILE__ ) ) . '/woopra.php';
		$this->plugin_basename = plugin_basename( $this->plugin_file );
		
		//	Load Transations File
		load_plugin_textdomain( 'woopra', false, '/woopra/locale' );

		//	Run this when installed or upgraded.
		register_activation_hook( $this->plugin_file,	array(&$this, 'init') 				);
		register_deactivation_hook( $this->plugin_file, array(&$this, 'init_deactivate')	);
		
		//	Only run if activated!
		if ($this->get_option('activated')) {
			//	Admin Actions
			add_action( 'admin_enqueue_scripts', 		array(&$this, 'enqueue' ) 							);
			add_action(	'admin_menu', 					array(&$this, 'woopra_add_menu') 					);
			
			//	AJAX Render
			add_action(	'wp_ajax_woopra',				array(&$this, 'render_page' ) 						);
		}

		add_action( 'admin_menu',					array(&$this, 'register_settings_page') 			);
		add_action( 'admin_init',					array(&$this, 'admin_init' ) 						);
		
		//	Process Events
		$this->event = new WoopraEvents();
				
	}
	
	/*** MAIN FUNCTIONS ***/
	
	/**
	 * Initialize Woopra Default Settings
	 * @since 1.4.1
	 * @return none
	 */
	function init() {
		if (!get_option('woopra')) {
			add_option('woopra', $this->defaults());
		} else if (!$this->get_option('activated')) {
			$this->init_activate();
		} else {
			$this->check_upgrade();
		}
	}
	
	/**
	 * Mark that we are activated!
	 * @since 1.4.1
	 * @return none
	 */
	function init_activate() {
		$woopra = get_option('woopra');
		$newopts = array (
				'activated'		=>	1,
		);
		update_option( 'woopra', array_merge($woopra, $newopts) );
	}
	
	/**
	 * Mark that we are no deactivated!
	 * @since 1.4.1
	 * @return none
	 */
	function init_deactivate() {
		$woopra = get_option('woopra');
		$newopts = array (
				'activated'		=>	0,
		);
		update_option( 'woopra', array_merge($woopra, $newopts) );
	}

	/**
	 * Regestration of the Setting Page
	 * @since 1.4.1
	 * @return none
	 */
	function register_settings_page() {
		add_options_page( __('Woopra', 'woopra'), __("Woopra", 'woopra'), 'manage_options', 'woopra', array(&$this, 'settings_page') );
		add_filter ( "plugin_action_links_{$this->plugin_basename}" , array ( &$this , 'filter_plugin_actions' ) );	
	}
	
	/**
	 * Add a settings link to the plugin actions
	 * @param array $links Array of the plugin action links
	 * @return array
	 * @since 1.4.1.1
	 */
	function filter_plugin_actions($links) { 
		$settings_link = '<a href="options-general.php?page=woopra">' . __('Settings', 'woopra') . '</a>'; 
		array_unshift($links, $settings_link); 
		return $links;
	}
	
	/**
	 * Add the Menu to Access the Stat Pages
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_add_menu() {
		
		if (false && function_exists('add_menu_page')) {
			if ($this->get_option('analytics_tab') && $this->get_option('analytics_tab') ==	'toplevel') {
				add_menu_page(__("Woopra Analytics", 'woopra'), __("Woopra Analytics", 'woopra'), "manage_options", "woopra.php", array(&$this, 'content_page') ); 
			} else {
				add_submenu_page('index.php', __("Woopra Analytics", 'woopra'), __("Woopra Analytics", 'woopra'), 'manage_options', "woopra-analytics", array(&$this, 'content_page') );
			}
		}
	}

	/**
	 * Whitelist the 'woopra' options
	 * @since 1.4.1
	 * @return none
	 */
	function admin_init () {
		register_setting( 'woopra', 'woopra', array(&$this , 'update') );
	}
	
	/**
	 * Scripts and Style Enqueue
	 * @since 1.4.1
	 * @return none
	 * @param object $hook_action
	 */
	function enqueue($hook_action) {
		$plugin_url = $this->plugin_url();
		if (('dashboard_page_woopra-analytics' == $hook_action) || ('toplevel_page_woopra' == $hook_action)) {
			wp_enqueue_script( 'woopra-analytics',	$plugin_url. '/js/analytics.js'	);
			wp_localize_script( 'woopra-analytics', 'woopradefaultL10n', array(
									'apikey'		=>	$this->get_option('api_key'),
									'siteurl'		=>	get_option('siteurl'),
									'baseurl'		=>	$plugin_url,
									'dateformat'	=>	$this->get_option('date_format'),
									'error'			=>	__('An javascript error has happened. Please try again later.', 'woopra'),
				)
			);
			wp_enqueue_script( 'woopra-swfobject',	$plugin_url . '/js/swfobject.js'							);
			// ** jQuery Datepicker **/
			wp_enqueue_script( 'woopra-datepicker',	$plugin_url . '/js/ui.datepicker.js',		array('jquery')	);
			
			wp_enqueue_style( 'woopra-analytics',	$plugin_url . '/css/analytics.css'							);
			wp_enqueue_style( 'woopra-datepicker',	$plugin_url . '/css/ui.datepicker.css'						);
		}
	}
	
	
	/*** OTHER FUNCTIONS ***/
	
	/**
	 * Check if an upgraded is needed
	 * @since 1.4.1
	 * @return none
	 */
	function check_upgrade() {
		if ($this->version_compare(array( '1.4.1' => '<')))
			$this->upgrade('1.4.1');
		else if ($this->version_compare(array( '1.4.1' => '>' , '1.4.1.1' => '<' )))
			$this->upgrade('1.4.1.1');
		else if ($this->version_compare(array( '1.4.1.1' => '>' , '1.4.2' => '<' )))
			$this->upgrade('1.4.2');
		else if ($this->version_compare(array( '1.4.2' => '>' , '1.4.3' => '<' )))
			$this->upgrade('1.4.3');
		else if ($this->version_compare(array( '1.4.3.2' => '<' )))
			$this->upgrade('1.4.3.2');
	}

	/**
	 * Compare Versions
	 * @since 1.4.1.1
	 * @return boolean
	 */
	function version_compare($versions) {
		foreach ($versions as $version => $operator) {
			if (version_compare($this->get_option('version'), $version, $operator))
				$response = true;
			else
				$response = false; 
		}
		return $response;
	}

	/**
	 * Upgrade options
	 * @return none
	 * @since 1.4.1
	 */
	function upgrade($ver) {
		if ( $ver == '1.4.1' ) {
			$woopra = get_option('woopra');
			
			/* Upgrading from non-class to class */
			$tagging = (get_option('woopra_auto_tag_commentators') == 'YES') ? 1 : 0;
			$ignoreadmin = (get_option('woopra_ignore_admin') == 'YES') ? 1 : 0;
			$trackadmin = (get_option('woopra_track_admin') == 'YES') ? 1 : 0;
			$comment_event = (get_option('woopra_show_comments') == 'YES') ? 1 : 0;
			$search_event = (get_option('woopra_show_searches') == 'YES') ? 1 : 0;
			
			$api_key = get_option('woopra_api_key');
			$tab = get_option('woopra_analytics_tab');
	
			$api_key = (!empty($api_key)) ? $api_key : '';
			$tab = (!empty($tab)) ? $tab : 'dashboard';

			$newopts = array (
					'version'		=>	'1.4.1',
					'activated'		=>	1,
					'api_key'		=>	$api_key,
					'analytics_tab'	=>	$tab,
					'run_status'	=>	'on',
					'ignore_admin'	=>	$tagging,
					'ignore_admin'	=>	$ignoreadmin,
					'track_admin'	=>	$trackadmin,
					'woopra_events'	=>	array(
						'comment_post'		=>	$comment_event,
						'the_search_query'	=>	$search_event,
					),
			);

			/* Delete old options */
			delete_option('woopra_api_key');
			delete_option('woopra_analytics_tab');
			delete_option('woopra_auto_tag_commentators');
			delete_option('woopra_ignore_admin');
			delete_option('woopra_track_admin');
			delete_option('woopra_show_comments');
			delete_option('woopra_show_searches');
			
			update_option( 'woopra', array_merge($woopra, $newopts) );
		} else if ( $ver == '1.4.1.1' ) {
			
			$woopra = get_option('woopra');

			$newopts = array (
					'version'		=>	'1.4.1.1'
			);
			
			unset($woopra['version']);
			update_option( 'woopra', array_merge($woopra, $newopts) );
		} else if ( $ver == '1.4.2' ) {
			
			$woopra = get_option('woopra');

			$newopts = array (
					'version'		=>	'1.4.2',
					'use_timeout'	=>	0,
					'timeout'		=>	600,
			);
			
			unset($woopra['version']);
			update_option( 'woopra', array_merge($woopra, $newopts) );
		} else if ( $ver == '1.4.3' ) {
			
			$woopra = get_option('woopra');

			$newopts = array (
					'version'			=>	'1.4.3',
					'process_event'	=>	1,
			);
			
			unset($woopra['version']);
			update_option( 'woopra', array_merge($woopra, $newopts) );
		} else if (( $ver == '1.4.3.1' ) || ( $ver == '1.4.3.2' )) {
			
			$woopra = get_option('woopra');

			$newopts = array (
					'version'			=>	$ver,
			);
			
			unset($woopra['version']);
			update_option( 'woopra', array_merge($woopra, $newopts) );
		}
	}

	/**
	 * Return the default options
	 * @since 1.4.1
	 * @return array
	 */
	function defaults() {
        $domainName = $this->getDomainName();
		$defaults = array(
			'version'			=> '',
			'activated'			=> 1,
			'api_key'			=> '',
			'analytics_tab'		=> 'dashboard',
			'run_status'		=> 'on',
			'date_format'		=> 'yyyy-MM-dd',
			'limit'				=> 50,
            'trackas'           => $domainName,
			'ignore_admin'		=> 0,
			'track_admin'		=> 0,
			'use_timeout'		=> 0,
			'process_event'		=> 1,
			'timeout'			=> 600,
			'track_author'		=> 1,
			'hide_campaign'		=> 0,
			'woopra_event'		=> array(
				'search_query' => 1,
				'signup' => 1,
				'comment_post' => 1
			), 'woopra_woocommerce_event'	=> array(
				'cart' => 1,
				'checkout' => 1,
				'coupon' => 1
			)
		);
		return $defaults;
	}

	/**
	 * Update/validate the options in the options table from the POST
	 * @since 1.4.1
	 * @return none
	 */
	function update($options) {
		if ( isset($options['delete']) && $options['delete'] == 'true' ) {
			delete_option('woopra');
		} else if ( isset($options['default']) && $options['default'] == 'true' ) {
			return $this->defaults();
		} else {
			unset($options['delete'], $options['default']);
			
			if ( !is_numeric( $options['timeout'] ) )
				$this->fire_error( 'timeout_not_numeric' , array('message' => 'You entred (<strong>%s</strong>) as a timeout value. This is not a vaild entry. Please enter whole numbers only!', 'values' => $options['timeout']) );
			
			$this->check_error( 'timeout_not_numeric' );
			
			if ($options['timeout'] <= 0)
				$options['use_timeout'] = false;

			return $options;
		}
	}
	
	/**
	 * The setting page itself.
	 * @since 1.4.1
	 * @return none
	 */
	function settings_page() {
	
	$this->_events = $this->event->default_events;
	$event_status = $this->get_option('woopra_event');

	$this->_woocommerce_events = $this->event->default_woocommerce_events;
	$woocommerce_event_status = $this->get_option('woopra_woocommerce_event');

	$process_event = $this->get_option('process_event') != false ? $this->get_option('process_event') : '0';

	?>
	
<div class="wrap">
	
	<input type="button" style="font-size: 16px;width: 300px;height: 42px;float: right;margin-top: 20px;margin-right: 10px;" onclick="window.open('http://www.woopra.com/live/')" class="button-primary" value="<?php _e('Launch Woopra', 'woopra') ?>" />
	
<?php screen_icon(); ?>
	<h2><?php _e( 'Woopra Settings', 'woopra' ); ?></h2>
	<p><?php _e('For more info about installation and customization, please visit <a href="http://www.woopra.com/installation-guide">the installation page in your member&#8217;s area</a>', 'woopra') ?></p>
	
	
	<form method="post" action="options.php">
	<?php settings_fields('woopra'); ?>
	
	<input type="hidden" name="woopra[version]" value="<?php echo $this->version; ?>" />
	<input type="hidden" name="woopra[activated]" value="<?php echo $this->get_option('activated'); ?>" />
	<input type="hidden" name="woopra[date_format]" value="yyyy-MM-dd" />
		
	<h3><? _e('Tracking Settings', 'woopra'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Status', 'woopra') ?></th>
			<td>
			<?php
				$run_statuss = array('on' => __("On", 'woopra'), 'off' => __('Off', 'woopra'));
				foreach ( $run_statuss as $key => $value) {
					$selected = ($this->get_option('run_status') == $key) ? 'checked="checked"' : '';
					echo "\n\t<label><input id='$key' type='radio' name='woopra[run_status]' value='$key' $selected/> $value</label><br />";
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Auto Timeout', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('use_timeout')); ?> id="use_timeout" name="woopra[use_timeout]"/> <label for="use_timeout"><?php _e("Override Woopra Default 'Auto Time Out'"); ?></label>
				<p class="description"><?php _e("Turn this 'on' if you want to override Woopra Default Timeout Settings (600 seconds). Setting this to low might cause incorrect statistics. Once a user is considered 'timed out' they will be considered gone. If they revisit they will be considered a 'new visit' and might mess up your statistics. This must be a whole number. (e.g. 34, 23) System will automaticly turn off if the number is less than or equal to 'zero'.", 'woopra'); ?></p>
				<label for="timeout"><?php _e('Seconds before Timeout:') ?> </label> <input type="text" value="<?php echo $this->get_option('timeout'); ?>" <?php checked( '1', $this->get_option('use_timeout') ) ?> id="timeout" name="woopra[timeout]" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><?php _e('Track As', 'woopra') ?></th>
			<td>
                <input type="text" value="<?php echo $this->get_option('trackas') != "" ? $this->get_option('trackas') : $this->getDomainName() ; ?>" id="trackas" name="woopra[trackas]" />
				<p class="description"><?php _e("Enter the your domain name here (i.e. domain.com) - don't need the www.") ?> </label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Ignore Administrator', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('ignore_admin')); ?> id="ignore_admin" name="woopra[ignore_admin]"/> <label for="ignore_admin"><?php _e("Ignore Administrator Visits", 'woopra'); ?></label>
				<p class="description"><?php _e("Enable this check box if you want Woopra to ignore your or any other administrator visits.", 'woopra'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('track_admin')); ?> id="track_admin" name="woopra[track_admin]"/> <label for="track_admin"><?php _e("Track Admin Pages", 'woopra'); ?></label>
				<p class="description"><?php printf(__("Admin pages are all pages under %s", 'woopra'), $this->get_option('siteurl')."/wp-admin/" ); ?></p>
			</td>
		</tr>
		<tr align="top">
			<th scope="row"><?php _e('Authors and Categories','woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('track_author')); ?> id="track_author" name="woopra[track_author]"/> <label for="track_author"><?php _e("Track Authors and Categories"); ?> </label>
				<p class="description"><?php _e("Enable this check box if you want Woopra to track the author and the category of a visited blog post as custom event properties. <a href=\"https://www.woopra.com/docs/getting-started/custom-data/\" target=\"_blank\">More about custom data</a>.",'woopra'); ?></p>
			</td>
		</tr>
		<tr align="top">
			<th scope="row"><?php _e('Campaign Tracking','woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('hide_campaign')); ?> id="hide_campaign" name="woopra[hide_campaign]"/> <label for="hide_campaign"><?php _e("Hide Campaign Properties"); ?> </label>
				<p class="description"><?php _e("Hide campaign properties from url (e.g. woo_campaign). <a href=\"https://www.woopra.com/docs/manual/campaign-tracking/\" target=\"_blank\">More about Campaign Tracking</a>.",'woopra'); ?></p>
			</td>
		</tr>
	</table>
	<br/>
	<h3><? _e('Event Settings', 'woopra'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Global Event Tracking', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1" <?php checked('1', $process_event);?>id="process_event" name="woopra[process_event]"/> <label for="process_event"><?php _e("Turn on Event Tracking System", 'woopra'); ?></label>
				<p class="description"><?php printf(__("If this is turned on, all events that are selected below will be tracked and reported back to the Woopra system.", 'woopra')); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Main Area Events', 'woopra') ?></th>
			<td>
			<?php
				$event_reg = 0;
				foreach ( $this->_events as $event => $data) {
					if (!$data['adminonly']) {
						$event_reg++;
						$checked = $process_event && $event_status[(isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter']))];
						echo "\n\t<input type=\"checkbox\" value=\"1\" class=\"custom-events\"" . checked( '1', $checked, false ) . disabled('0', $process_event, false) . " id=\"" . ((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter']))) . "\" name=\"woopra[woopra_event][".((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter'])))."]\"/> <label for=\"woopra[woopra_event][".((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter'])))."]\">".$data['name']."</label> - ".$data['label']."<br/>";
					}
				}
				if ($event_reg < 1)
					echo "<strong>" . __('No Main Events Registered.', 'woopra') . "</strong>";
			?>				
			</td>
		</tr>
		<?php if(is_plugin_active( 'woocommerce/woocommerce.php' )) { ?>
		<tr valign="top">
			<th scope="row"><?php _e('WooCommerce Events', 'woopra') ?></th>
			<td>
			<?php
				$event_reg = 0;
				foreach ( $this->_woocommerce_events as $event => $data) {
					if (!$data['adminonly']) {
						$event_reg++;
						$checked = $process_event && $woocommerce_event_status[(isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter']))];
						echo "\n\t<input type=\"checkbox\" value=\"1\" class=\"custom-events\"" . checked( '1', $checked, false ) . disabled('0', $process_event, false) . " id=\"" . ((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter']))) . "\" name=\"woopra[woopra_woocommerce_event][".((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter'])))."]\"/> <label for=\"woopra[woopra_woocommerce_event][".((isset($data['setting']) ? $data['setting'] : (isset($data['action']) ? $data['action'] : $data['filter'])))."]\">".$data['name']."</label> - ".$data['label']."<br/>";
					}
				}
				if ($event_reg < 1)
					echo "<strong>" . __('No WooCommerce Events Registered.', 'woopra') . "</strong>";
			?>				
			</td>
		</tr>

		<?php } ?>
		<tr valign="top">
			<th scope="row"><?php _e('Other Events Tracking', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('other_events')); disabled('0', $process_event); ?> class="custom-events" id="other_events" name="woopra[other_events]"/> <label for="other_events"><?php _e("Custom Event Tracking", 'woopra'); ?></label><br /><?php printf(__("Turn this feature on to allow other developers to track events with Woopra. Developers can refer to the example below.<br><pre style='color:#000000;'><pre>
<span style='color:#5f5035; background:#ffffe8; '>&lt;?php</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>do_action</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>\"woopra_track\"</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$event_name</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>\"pv\"</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$event_properties</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>array</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$back_end_processing</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>false</span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#5f5035; background:#ffffe8; '>?></span>
</pre>", 'woopra')); ?>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" name="woopra-submit" class="button-primary" value="<?php _e('Save Changes', 'woopra') ?>" />
	</p>
	
	</form>
	</div>
	<script>
		document.getElementById('process_event').addEventListener('click',
    		function(){
    			custom_events = document.getElementsByClassName('custom-events');
    			for (i in custom_events) {
    				var event = custom_events[i];
    				if (this.checked) {
						event.disabled = null;
					    event.checked = "cheked";
					} else {
						event.checked = null;
					    event.disabled = "disabled";
					}
    			}
    		}
    	);
	</script>
	
	<?php }

	/**
	 * The content page.
	 * @since 1.4.1
	 * @return none
	 */
	function content_page() {
		$WoopraAnalytics = new WoopraAnalytics();
		$WoopraAnalytics->main();
		unset($WoopraAnalytics);
	}
	
	/**
	 * The render page.
	 * @since 1.4.1
	 * @return none
	 */
	function render_page() {
		$WoopraRender = new WoopraRender();
		unset($WoopraRender);
    }
	
    /**
	 * fetches the domain name.
	 * @return domain name
	 */
    function getDomainName() {
        $url = $_SERVER['SERVER_NAME'];
        if ((substr_count($url, 'http://www.')) > 0) {
            $url = str_replace('http://www.', '', $url);
        } elseif ((substr_count($url, 'http://')) > 0) {
            $url = str_replace('http://', '', $url);
        } elseif ((substr_count($url, 'www.')) > 0) {
            $url = str_replace('www.', '', $url);
        }
        if ((substr_count($url, '/')) > 0) {
            $url = substr($url, 0, strrpos($url, '/'));
        }
        $url = preg_replace("/\/.*$/is" , "" ,$url);
        return $url;
    }
    
}
?>
