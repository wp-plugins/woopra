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
	 * @var string
	 */
	var $plugin_file;
	
	/**
	 * @var string
	 */
	var $plugin_basename;
	
	/**
	 * @var
	 */
	var $page_hookname;

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
		
		if ( version_compare( $this->get_option('version'), $this->version, '!=' ) && $this->get_option('version') !== false )
			$this->check_upgrade();
		
		$this->plugin_file = dirname( dirname ( __FILE__ ) ) . '/woopra.php';
		$this->plugin_basename = plugin_basename( $this->plugin_file );
		
		//	Load Transations File
		load_plugin_textdomain( 'woopra', false, '/woopra/locale' );

		//	Run this when installed or upgraded.
		register_activation_hook( $this->plugin_file , array(&$this, 'init') );
		
		//	Admin Actions
		add_action( 'admin_menu',               array(&$this, 'register_settings_page') 			);
		add_action(	'admin_menu', 				array(&$this, 'woopra_add_menu') 					);
		add_action( 'admin_init',				array(&$this, 'admin_init' ) 						);
		add_action( 'admin_enqueue_scripts', 	array(&$this, 'enqueue' ) 							);
		
	}
	
	/*** MAIN FUNCTIONS ***/
	
	/**
	 * Initialize Woopra Default Settings
	 * @since 1.4.1
	 * @return none
	 */
	function init() {
		if (!get_option('woopra'))
			add_option('woopra', $this->defaults());
		else
			$this->check_upgrade();
	}

	/**
	 * Regestration of the Setting Page
	 * @since 1.4.1
	 * @return none
	 */
	function register_settings_page() {
		add_options_page( __('Woopra Settings', 'woopra'), __("Woopra Settings", 'woopra'), 'manage_options', 'woopra', array(&$this, 'settings_page') );
	}

	/**
	 * Add the Menu to Access the Stat Pages
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_add_menu() {
		if (function_exists('add_menu_page')) {
			if ($this->get_option('analytics_tab') && $this->get_option('analytics_tab') ==	'toplevel') {
				add_menu_page(__("Woopra Analytics", 'woopra'), __("Woopra Analytics", 'woopra'), "manage_options", "woopra-analytics.php", array(&$this, 'content_page') ); 
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
	 * @param object $hook_action
	 * @return none
	 */
	function enqueue($hook_action) {
		$plugin_url = $this->plugin_url();
		if ('dashboard_page_woopra-analytics' == $hook_action) {
			wp_enqueue_script( 'woopra-analytics',	$plugin_url. '/js/analytics.js'	);
			wp_localize_script( 'woopra-analytics', 'woopradefaultL10n', array(
									'apikey'	=>	$this->get_option('api_key'),
	                                'error'		=>	__('An error has happened. Please try again later.', 'woopra')
								)
			);
			wp_enqueue_script( 'woopra-swfobject',	$plugin_url . '/js/swfobject.js'	);
			wp_enqueue_script( 'woopra-datepicker',	$plugin_url . '/js/datepicker.js'	);
			
			wp_enqueue_style( 'woopra-analytics',	$plugin_url . '/css/analytics.css'	);
			wp_enqueue_style( 'woopra-datepicker',	$plugin_url . '/css/datepicker.css'	);			
		}
	}
	
	/*** OTHER FUNCTIONS ***/
	
	/**
	 * Check if an upgraded is needed
	 * @since 1.4.1
	 * @return none
	 */
	function check_upgrade () {
		if ( version_compare($this->get_option('version'), WOOPRA_VERSION, '<') )
			$this->upgrade(WOOPRA_VERSION);
	}

	/**
	 * Upgrade options 
	 *
	 * @return none
	 * @since 1.4.1
	 */
	function upgrade($ver) {
		if ( $ver == '1.4.1' ) {
			$woopra = get_option('woopra');
			
			/* Upgrading from non-class to class */
			$tagging = (get_option('woopra_auto_tag_commentators' == 'YES')) ? 1 : 0;
			$ignoreadmin = (get_option('woopra_ignore_admin' == 'YES')) ? 1 : 0;
			$trackadmin = (get_option('woopra_track_admin' == 'YES')) ? 1 : 0;
						
			$newopts = array (
					'version'		=>	$this->version,
					'api_key'		=>	get_option('woopra_api_key'),
					'analytics_tab'	=>	get_option('woopra_analytics_tab'),
					'run_status'	=>	'on',
					'ignore_admin'	=>	$tagging,
					'ignore_admin'	=>	$ignoreadmin,
					'track_admin'	=>	$trackadmin,
			);
			update_option( 'woopra', array_merge($woopra, $newopts) );
		}
	}

	/**
	 * Return the default options
	 * @since 1.4.1
	 * @return array
	 */
	function defaults() {
		$defaults = array(
			'version' 		=> '',
			'api_key'		=> '',
			'analytics_tab'	=> 'dashboard',
			'run_status'	=> 'on',
			'auto_tagging'	=> 1,
			'ignore_admin'	=> 0,
			'track_admin'	=> 0,		
		);
		return $defaults;
	}

	/**
	 * Update/validate the options in the options table from the POST
	 *
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
			return $options;
		}
	}

	/**
	 * The setting page itself.
	 * @since 1.4.1
	 * @return none
	 */
	function settings_page() { ?>
	
<div class="wrap">
<?php screen_icon(); ?>
	<h2><?php _e( 'Woopra Settings', 'woopra' ); ?></h2>
	<p><?php _e('For more info about installation and customization, please visit <a href="http://www.woopra.com/installation-guide">the installation page in your member&#8217;s area</a>', 'woopra') ?></p>
	
	<form method="post" action="options.php">
	<?php settings_fields('woopra'); ?>
	
	<h3><? _e('Main Settings'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('API Key', 'woopra') ?><small><?php _e('(Optional)', 'woopra') ?></small></th>
				<td>
					<input type="text" value="<?php echo attribute_escape( $this->get_option('api_key') ); ?>" id="api_key" name="woopra[api_key]"/><br/>
					<?php _e("You can find the Website's API Key in <a href='http://www.woopra.com/members/'>your member&#8217;s area", 'woopra') ?></a>
				</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Analytics Link Location', 'woopra') ?></th>
			<td>
			<?php
				$woopra_tab_options = array('dashboard' => __("At the dashboard menu", 'woopra'), 'toplevel' => __('At the top level menu', 'woopra'));
				foreach ( $woopra_tab_options as $key => $value) {
					$selected = ($this->get_option('analytics_tab') == $key) ? 'checked="checked"' : '';
					echo "\n\t<label><input id='$key' type='radio' name='woopra[analytics_tab]' value='$key' $selected/> $value</label><br />";
				}
			?>
			</td>
		</tr>
	</table>
	<br/>
	<h3><? _e('Tracking Settings'); ?></h3>
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
			<th scope="row"><?php _e('Auto Tagging', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('auto_tagging')); ?> id="auto_tagging" name="woopra[auto_tagging]"/> <label for="auto_tagging"><?php _e("Automatically Tag Members &amp; Commentators"); ?></label><br /><?php _e("Enable this check box if you want Woopra to auto-tag visitors."); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Ignore Administrator', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('ignore_admin')); ?> id="ignore_admin" name="woopra[ignore_admin]"/> <label for="ignore_admin"><?php _e("Ignore Administrator Visits"); ?></label><br /><?php _e("Enable this check box if you want Woopra to ignore your or any other administrator visits."); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area', 'woopra') ?></th>
			<td>
				<input type="checkbox" value="1"<?php checked('1', $this->get_option('track_admin')); ?> id="track_admin" name="woopra[track_admin]"/> <label for="track_admin"><?php _e("Track Admin Pages"); ?></label><br /><?php printf(__("Admin pages are all pages under %s"), $this->get_option('siteurl')."/wp-admin/" ); ?>
			</td>
		</tr>
	</table>
	<br/>
	<h3><? _e('Event Settings'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Main Area Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['main'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('1', $this->get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra[woopra_event][".$action."]\"/> <label for=\"woopra[woopra_event][".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>				
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['admin'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('1', $this->get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra[woopra_event][".$action."]\"/> <label for=\"woopra[woopra_event][".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Third Party Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['custom'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('1', $this->get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra[woopra_event][".$action."]\"/> <label for=\"woopra[woopra_event][".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
				if (!count($this->woopra_events['custom']))
					echo "<strong>" . __('No Custom Events Regestiered.') . "</strong>";
			?>
			</td>
		</tr>				
	</table>
	
	<p class="submit">
		<input type="submit" name="woopra-submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	
	</form>
	
	<?php }

	/**
	 * The content page.
	 * @since 1.4.1
	 * @return none
	 */
	function content_page() {
		$WoopraAnalytics = new WoopraAnalytics;
		$WoopraAnalytics->main();
	}

}