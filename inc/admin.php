<?php
/**
 * WoopraAdmin Class for Woopra
 *
 * This class contains all functions and actions required for Woopra to work on the frontend of WordPress
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage admin
 */
class WoopraAdmin extends Woopra {

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
		
		//	Load Transations File
		load_plugin_textdomain( 'woopra', false, '/woopra/locale' );
		
		//	Run this when installed or upgraded.
		register_activation_hook(__FILE__, array(&$this, 'init') );
		
		//	Actions
		add_action( 'admin_menu',               array(&$this, 'register_settings_page') 		);
		add_action( 'admin_init',				array(&$this, 'register_settings' ) 			);
		add_action(	'admin_menu', 				array(&$this, 'woopra_add_menu') 				);
		add_action(	'template_redirect',		array(&$this, 'woopra_detect')					);
		add_action( 'admin_print_scripts',		array(&$this, 'woopra_analytics_head') 			);
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget'), 			10	);
		
		//	Initilize Action Code
		/*
		$all_events = array_merge($this->woopra_events['main'], $this->woopra_events['admin']);
		foreach ($all_events as $event_name) {
			add_action( $event_name, 			array(&$this, $this->process_event) );
		}
		*/
		
	}
	
	/**
	 * Initialize Woopra Default Settings
	 * @since 1.4.1
	 * @return none
	 */
	function init() {
		$this->debug("init()");
		if (!get_option('woopra')) {
			add_option('woopra', $this->defaults());
			$this->first_upgrade();	// maybe not needed.
		} else {
			$this->check_upgrade();
		}
	}
	
	/**
	 * Whitelist the 'woopra' options
	 * @since 1.4.1
	 * @return none
	 */
	function register_settings () {
		$this->debug("register_settings()");
		register_setting( 'woopra', 'woopra', array(&$this , 'update') );
	}

	/**
	 * This funcition is run on the first time this pluin is installed from 1.4.1
	 * @return none
	 */
	function first_upgrade() {
		//	Restore the user's current options
		//	Delete Options
		delete_option('woopra_analytics_tab');
		delete_option('woopra_api_key');
		delete_option('woopra_auto_tag_commentators');
		delete_option('woopra_ignore_admin');
		delete_option('woopra_show_comments');
		delete_option('woopra_track_admin');
	}
	
	/**
	 * Check if an upgraded is needed
	 * @since 1.4.1
	 * @return none
	 */
	function check_upgrade () {
		// update poss. new default options with current options. But how do we check that? :P
	}

	/**
	 * Return the default options
	 * @since 1.4.1
	 * @return array
	 */
	function defaults() {
		$defaults = array();
		return $defaults;
	}

	/**
	 * Update/validate the options in the options table from the POST
	 *
	 * @since 1.4.1
	 * @return none
	 */
	function update($options) {
		return $options;
	}

	/**
	 * Regestration of the Setting Page
	 * @since 1.4.1
	 * @return none
	 */
	function register_settings_page() {
		$this->debug("register_settings_page()");
		add_options_page( __('Woopra Settings', 'woopra'), __("Woopra Settings", 'woopra'), 'manage_options', 'woopra', array(&$this, 'settings_page') );
	}

	/**
	 * The setting page itself.
	 * @since 1.4.1
	 * @return none
	 */
	function settings_page() {
		$this->debug("settings_page()");
		 ?>
	
<div class="wrap">
<?php screen_icon(); ?>
	<h2><?php _e( 'Woopra Settings', 'woopra' ); ?></h2>
	<p><?php _e('For more info about installation and customization, please visit <a href="http://www.woopra.com/installation-guide">the installation page in your member&#8217;s area', 'woopra') ?></a></p>
	
	<form id="woopra_settings_form" method="post" action="options.php">
	<?php settings_fields('woopra'); ?>
	
	<h3><? _e('Main Settings'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('API Key', 'woopra') ?><small><?php _e('(Optional)', 'woopra') ?></small></th>
				<td>
					<input type="text" value="<?php echo attribute_escape( get_option('woopra_api_key') ); ?>" id="woopra_api_key" name="woopra_api_key"/><br/>
					<?php _e("You can find the Website's API Key in <a href='http://www.woopra.com/members/'>your member&#8217;s area", 'woopra') ?></a>
				</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Analytics Link Location', 'woopra') ?></th>
			<td>
			<?php
				$woopra_tab_options = array('dashboard' => __("At the dashboard menu", 'woopra'), 'toplevel' => __('At the top level menu', 'woopra'));
				foreach ( $woopra_tab_options as $key => $value) {
					$selected = (get_option('woopra_analytics_tab') == $key) ? 'checked="checked"' : '';
					echo "\n\t<label><input id='$key' type='radio' name='woopra_analytics_tab' value='$key' $selected/> $value</label><br />";
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
				$woopra_status_options = array('on' => __("On", 'woopra'), 'off' => __('Off', 'woopra'));
				foreach ( $woopra_status_options as $key => $value) {
					$selected = (get_option('woopra_status_option') == $key) ? 'checked="checked"' : '';
					echo "\n\t<label><input id='$key' type='radio' name='woopra_status_option' value='$key' $selected/> $value</label><br />";
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Ignore Administrator', 'woopra') ?></th>
			<td>
				<input type="checkbox" <?php checked('1', get_option('woopra_ignore_admin')); ?> id="woopra_ignore_admin" name="woopra_ignore_admin"/> <label for="woopra_ignore_admin"><?php _e("Ignore Administrator Visits"); ?></label><br /><?php _e("Enable this check box if you want Woopra to ignore your or any other administrator visits."); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area', 'woopra') ?></th>
			<td>
				<input type="checkbox" <?php checked('1', get_option('woopra_track_admin')); ?> id="woopra_track_admin" name="woopra_track_admin"/> <label for="woopra_track_admin"><?php _e("Track Admin Pages"); ?></label><br /><?php printf(__("Admin pages are all pages under %s"), get_option('siteurl')."/wp-admin/" ); ?>
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
					echo "\n\t<input type=\"checkbox\"" . checked('1', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>				
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['admin'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('1', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Third Party Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['custom'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('1', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
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

	/** NON-WORDPRESS RELATED CODE **/

	/**
	 * Add the Menu to Access the Stat Pages
	 * @since 1.4.1
	 * @return none
	 */
	function woopra_add_menu() {
		$this->debug("woopra_add_menu()");
		if (function_exists('add_menu_page')) {
			if (get_option('woopra_analytics_tab') && get_option('woopra_analytics_tab') ==	'toplevel') {
				//	This is untested.
				add_menu_page(__("Woopra Analytics", 'woopra'), __("Woopra Analytics", 'woopra'), "manage_options", "woopra_analytics.php", "woopra_analytics_show_content"); 
			} else {
				add_submenu_page('index.php', __("Woopra Analytics", 'woopra'), __("Woopra Analytics", 'woopra'), 'manage_options', "woopra-analytics", "woopra_analytics_show_content");
			}
		}
	}


}