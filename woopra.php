<?php /*

**************************************************************************

Plugin Name:  Woopra
Plugin URI:   http://www.woopra.com
Version:      1.4.1
Description:  This plugin adds Woopra's real-time analytics to any WordPress installation.  Simply sign up at Woopra.com, then activate the plugin!
Author:       <a href="http://www.ekhoury.com">Elie El Khoury</a>, <a href="http://bugssite.org">Shane Froebel</a>
Author URI:   http://www.woopra.com/

**************************************************************************/

/**
 * @author Elie El Khoury, Shane Froebel
 * @copyright 2008
 */
class Woopra {

	/**
	 * @var $woopra_events Holds all the events and their descriptions.
	 * @since 1.4.1
	 * @see __construct()
	 */
	var $woopra_events = array();
	
	/**
	 * @var $woopra_actionable_events Holds events that are fully "acceptable" to be passed onto the application.
	 * @since 1.4.1
	 */
	var $woopra_actionable_events = array();
	
	/**
	 * @var $woopra_vistors Current vistor information on the site are stored in this varabile.
	 * @since 1.4.1
	 */
	var $woopra_vistors = array();

	/** DEBUG VAR **/
	var $debug = false;
	
	/**
	 * Contructor Class
	 * @since 1.4.1
	 * @return
	 * @constructor
	 */
	function __construct() {
		
		//	Load Translation Files
		load_plugin_textdomain( 'woopra', false, '/woopra/locale' );
		
		$this->woopra_events['main'] = array(
			'comment_post' => array(
				'label' => _("Posted Comments"),
				'descp' => _("Everytime a user posts a comment you will see an event in the application."),
			),
		);
		
		//	Filters
		
		
		//	Actions
		add_action( 'admin_menu',               array(&$this, 'register_settings_page') );
		add_action(	'template_redirect',		array(&$this, 'woopra_detect')	);
		add_action( 'wp_footer', 				array(&$this, 'woopra_widget') );
		
		//	Initilize Action Code
		/*
		$all_events = array_merge($this->woopra_events['main'], $this->woopra_events['admin']);
		foreach ($all_events as $event_name) {
			add_action( $event_name, 			array(&$this, $this->process_event) );
		}
		*/
		
	}
	
	/**
	 * Regestration of the Setting Page
	 * @return 
	 */
	function register_settings_page() {
		add_options_page( __('Woopra Settings', 'woopra'), __("Woopra Settings", 'woopra'), 'manage_options', 'woopra', array(&$this, 'settings_page') );
	}

	/**
	 * The setting page itself.
	 * @return 
	 */
	function settings_page() { ?>
	
<div class="wrap">
<?php screen_icon(); ?>
	<h2><?php _e( 'Woopra Settings', 'woopra' ); ?></h2>
	<p><?php _e('For more info about installation and customization, please visit <a href="http://www.woopra.com/installation-guide">the installation page in your member&#8217;s area', 'woopra') ?></a></p>
	
	<form id="woopra_settings_form" method="post" action="options.php">
	<?php settings_fields('woopra_settings'); ?>
	
	<h3><? _e('Main Settings'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('API Key', 'woopra') ?><small><?php _e('(Optional)', 'woopra') ?></small></th>
				<td>
					<input type="text" value="<?php echo attribute_escape( get_option('woopra_api_key') ); ?>" id="apikey" name="apikey"/><br/>
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
					echo "\n\t<label><input id='$key' type='radio' name='woopratab' value='$key' $selected/> $value</label><br />";
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
					echo "\n\t<label><input id='$key' type='radio' name='woopra_status' value='$key' $selected/> $value</label><br />";
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Ignore Administrator', 'woopra') ?></th>
			<td>
				<input type="checkbox" <?php checked('1', get_option('woopra_ignore_admin')); ?> id="ignoreadmin" name="ignoreadmin"/> <label for="ignoreadmin"><?php _e("Ignore Administrator Visits"); ?></label><br /><?php _e("Enable this check box if you want Woopra to ignore your or any other administrator visits."); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area', 'woopra') ?></th>
			<td>
				<input type="checkbox" <?php checked('1', get_option('woopra_track_admin')); ?> id="trackadmin" name="trackadmin"/> <label for="trackadmin"><?php _e("Track Admin Pages"); ?></label><br /><?php printf(__("Admin pages are all pages under %s"), get_option('siteurl')."/wp-admin/" ); ?>
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
					echo "\n\t<input type=\"checkbox\"" . checked('YES', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>				
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Admin Area Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['admin'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('YES', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
			?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Third Party Events', 'woopra') ?></th>
			<td>
			<?php
				foreach ( $this->woopra_events['custom'] as $action => $data) {
					echo "\n\t<input type=\"checkbox\"" . checked('YES', get_option('woopra_event_' . $action)) . " id=\"" . $action . "\" name=\"woopra_event[".$action."]\"/> <label for=\"woopra_event[".$action."]\">".$data['label']."</label><br />".$data['description'];						
				}
				if (!count($this->woopra_events['custom']))
					echo "<strong>" . __('No Custom Events Regestiered.') . "</strong>";
			?>
			</td>
		</tr>				
	</table>
	</form>
	
	<?php }

	/**
	 * Create the Javascript Code at the Bottom
	 * @since 1.4.1
	 * @return 
	 */
	function woopra_widget() {
		$this->debug("woopra_widget()");
	echo "<!-- Woopra Analytics Code -->\n";
	echo "<script type=\"text/javascript\">\r\n";
	echo "var woopra_visitor = new Array();\r\n";
	echo "var woopra_event = new Array();\r\n";
	
	if (get_option('woopra_auto_tag_commentators') == 'YES' && $this->woopra_visitor['name'] != null) {
		echo "woopra_visitor['name'] = '" . js_escape($this->woopra_visitor['name']) . "';\r\n";
		echo "woopra_visitor['email'] = '" . js_escape($this->woopra_visitor['email']) . "';\r\n";
		echo "woopra_visitor['avatar'] = 'http://www.gravatar.com/avatar.php?gravatar_id=" . md5(strtolower($this->woopra_visitor['email'])) . "&size=60&default=http%3A%2F%2Fstatic.woopra.com%2Fimages%2Favatar.png';\r\n";
	}
		
	echo "</script>\r\n";
	echo "<script src=\"http://static.woopra.com/js/woopra.js\" type=\"text/javascript\"></script>";
	echo "\n<!-- End of Woopra Analytics Code -->";
		
	}

	function woopra_detect() {
		$this->debug("woopra_detect()");
		global $userdata;	//	Needed if the user is logged in.
		
		//	Check to see if the user has a cookie.. if so... get it!
		$author = str_replace("\"","\\\"",$_COOKIE['comment_author_'.COOKIEHASH]);
		$email = str_replace("\"","\\\"",$_COOKIE['comment_author_email_'.COOKIEHASH]);
		if (!empty($author)) {
			$this->woopra_visitor['name'] = $author;
			$this->woopra_visitor['email'] = $email;
		}
	
		if (is_user_logged_in()) {
			get_currentuserinfo();
			$this->woopra_visitor['name'] = $userdata->user_login;
			$this->woopra_visitor['email'] = $userdata->user_email;
		}
	}

	/**
	 * Compatability for PHP 4.
	 * @return 
	 */
	function Woopra() {
		$this->__construct();
	}
	
	/**
	 * Debug code. Has to be turned on for it to work.
	 * @return 
	 * @see $this->debug
	 */
	function debug($string) {
		if ($this->debug)
			echo $string . "<br/>";
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'Woopra_Init' ); function Woopra_Init() { global $Woopra; $Woopra = new Woopra(); }

?>
