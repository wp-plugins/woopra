<?php
error_reporting();
/**
 * WoopraFrontend Class for Woopra
 * This class contains all functions and actions required for Woopra to track Wordpress events and outputs the frontend code.
 */
class WoopraFrontend extends Woopra {

	var $user;
	
	var $config;
	
	function __construct() {		
		
		//Construct parent class
		Woopra::__construct();
		
		//Load configuration
		$this->config = array();
		$this->woopra_config();
		
		// Load PHP SDK
		$this->woopra = new WoopraTracker($this->config);
		
		// Load Event Processing
		$this->events = new WoopraEvents();
		
		//Detect Wordpress user
		$this->user = array();
		add_action('init', array(&$this, 'woopra_detect'));
		
		//If event tracking is turned on, process events
		if ($this->get_option('process_event')) {
			$this->register_events();
			$this->register_woocommerce_events();
		}
		
		if ($this->get_option('other_events')) {
			add_action("woopra_track", array(&$this->woopra, "track"), 10, 3);
		}
		
		//Register front-end tracking
		add_action('init', array(&$this, 'set_tracker'));
		
	}
	
	/**
	 * Registers events
	 * @return none
	 */
	 function register_events() {
	 	$all_events = $this->events->default_events;
	 	$event_status = $this->get_option('woopra_event');
	 	foreach ($all_events as $event_name => $data) {
	 		if (($event_status[$data['action']] == 1)) {
		 		switch($data['action']) {
		 			case "search_query":
		 				if (isset($_GET["s"])) {
							$this->woopra->track("wp search", array("query" => $_GET["s"]), true);
						}
		 			break;
		 			case "comment_post":
		 				add_action('comment_post', array(&$this, 'track_comment'), 10, 1);
		 			break;
		 			case "signup":
		 				add_action('user_register', array(&$this, 'track_signup'), 10, 1);
		 			break;
		 		}
	 		}
		}
	 }

	 /**
	 * Registers woocommerce events
	 * @return none
	 */
	 function register_woocommerce_events() {
	 	$all_events = $this->events->default_woocommerce_events;
	 	$event_status = $this->get_option('woopra_woocommerce_event');
	 	foreach ($all_events as $event_name => $data) {
	 		if (($event_status[$data['action']] == 1)) {
		 		switch($data['action']) {
		 			case "cart":
		 				add_action('woocommerce_cart_loaded_from_session', array(&$this, 'initialize_cart_quantities'));
		 				add_action('woocommerce_after_cart_item_quantity_update', array(&$this, 'track_cart_quantity'));
		 				add_action('woocommerce_before_cart_item_quantity_zero', array(&$this, 'track_cart_quantity_zero'));
		 				add_action('woocommerce_add_to_cart', array(&$this, 'track_cart_add'), 2, 6);
		 				add_action('woocommerce_cart_item_removed', array(&$this, 'track_cart_remove'));
		 			break;
		 			case "checkout":
		 				add_action('woocommerce_checkout_order_processed', array(&$this, 'track_checkout'), 10, 2);
		 			break;
		 			case "coupon":
		 				add_action('woocommerce_applied_coupon', array(&$this, 'track_coupon'));
		 			break;
		 		}
	 		}
		}
	}

	 /**
	 * Tracks a signup
	 * @return none
	 */
	 function track_signup($user_id) {
	 	$user = get_user_by('id', $user_id);
	 	if ( !($user instanceof WP_User) ) {
			return;
		}
		$user_details = array();
		if ($user->has_prop("user_firstname")) {
			$user_details["first name"] = $user->user_firstname;
		}
		if ($user->has_prop("user_lastname")) {
			$user_details["last name"] = $user->user_lastname;
		}
		if ($user->has_prop("user_firstname") && $user->has_prop("user_lastname")) {
			$user_details["full name"] = $user->user_firstname . ' ' . $user->user_lastname;
		}
		if ($user->has_prop("user_email")) {
			$user_details["email"] = $user->user_email;
		}
		if ($user->has_prop("user_login")) {
			$user_details["username"] = $user->user_login;
		}
		$this->woopra->track('wp signup', $user_details, true);
	}
	 
	 /**
	 * Tracks a comment
	 * @return none
	 */
	 function track_comment($comment_id) {
	 	$comment_details = array();
	 	$comment = get_comment($comment_id);
	 	$post_info = get_post($comment->comment_post_ID);
	 	$comment_details["id"] = $comment->comment_ID;
	 	$comment_details["post title"] = $post_info->post_title;
	 	$comment_details["post permalink"] = get_permalink($post_info->ID);
	 	$comment_details["author name"] = $comment->comment_author;
	 	$comment_details["author email"] = $comment->comment_author_email;
	 	if ($comment->comment_author_url) {
	 		$comment_details["author website"] = $comment->comment_author_url;
	 	}
	 	$comment_details["content"] = $comment->comment_content;
	 	if (!is_user_logged_in()) {
	 		$user_details = array();
	 		$user_details["author name"] = $comment->comment_author;
	 		$user_details["author email"] = $comment->comment_author_email;
			$this->woopra->identify($user_details);
		}
	 	$this->woopra->track("wp comment", $comment_details, true);
	 }

	 /**
	 * Initializes cart quantities (to compute deltas later on)
	 * @return none
	 */
	 function initialize_cart_quantities() {
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$this->cart_quantities = $cart->get_cart_item_quantities();
	 }

	 /**
	 * Tracks a cart update
	 * @return none
	 */
	 function track_cart_quantity($cart_item_key, $quantity = 1) {
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$cart->calculate_totals();
	 	$content = $cart->get_cart();
	 	$item = $content[$cart_item_key];
	 	$quantity_before = $this->cart_quantities[$item['variation_id'] ? $item['variation_id'] : $item['product_id']];
	 	$quantity_after = $item["quantity"];
	 	$product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
	 	$params = array(
	 		"item sku" => $product->get_sku(),
	 		"item title" => $product->get_title(),
	 		"item price" => $product->get_price(),
	 		"quantity" => ($quantity_after - $quantity_before),
	 		"value" => ($quantity_after - $quantity_before)*$product->get_price()
	 	);
	 	$this->user['wc cart size'] = $cart->get_cart_contents_count();
	 	$this->user['wc cart value'] = $cart->subtotal;
	 	if (!is_user_logged_in()) {
			$this->woopra->identify($this->user);
	 	} else {
	 		$this->woopra_detect();
	 	}
	 	$this->woopra->track('wc cart update', $params, true);
	 }

	 /**
	 * Tracks a cart update
	 * @return none
	 */
	 function track_cart_quantity_zero($cart_item_key) {
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$content = $cart->get_cart();
	 	$item = $content[$cart_item_key];
	 	$product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
	 	$params = array(
	 		"item sku" => $product->get_sku(),
	 		"item title" => $product->get_title(),
	 		"item price" => $product->get_price(),
	 		"quantity" => -$item["quantity"],
	 		"value" => -$item["quantity"]*$product->get_price()
	 	);
	 	unset( $cart->cart_contents[ $cart_item_key ] );
	 	$cart->calculate_totals();
	 	$this->user['wc cart size'] = $cart->get_cart_contents_count();
	 	$this->user['wc cart value'] = $cart->subtotal;
	 	if (!is_user_logged_in()) {
			$this->woopra->identify($this->user);
	 	} else {
	 		$this->woopra_detect();
	 	}
	 	$this->woopra->track('wc cart update', $params, true);
	 }

	 /**
	 * Tracks a cart update
	 * @return none
	 */
	 function track_cart_add($cart_item_key, $product_id = 0, $quantity = 1, $variation_id = null, $variation = null, $cart_item_data = null) {
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$cart->calculate_totals();
	 	$content = $cart->get_cart();
	 	$item = $content[$cart_item_key];
	 	$product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
	 	$params = array(
	 		"item sku" => $product->get_sku(),
	 		"item title" => $product->get_title(),
	 		"item price" => $product->get_price(),
	 		"quantity" => $quantity,
	 		"value" => $quantity*$product->get_price()
	 	);
	 	$this->user['wc cart size'] = $cart->get_cart_contents_count();
	 	$this->user['wc cart value'] = $cart->subtotal;
	 	if (!is_user_logged_in()) {
			$this->woopra->identify($this->user);
	 	} else {
	 		$this->woopra_detect();
	 	}
	 	$this->woopra->track('wc cart update', $params, true);
	 }
	 
	 /**
	 * Tracks when item is removed from cart
	 * @return none
	 */
	 function track_cart_remove($cart_item_key) {
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$content = $cart->removed_cart_contents;
	 	$item = $content[$cart_item_key];
	 	$quantity = $item["quantity"];
	 	$product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
	 	$params = array(
	 		"item sku" => $product->get_sku(),
	 		"item title" => $product->get_title(),
	 		"item price" => $product->get_price(),
	 		"quantity" => -$quantity,
	 		"value" => -$quantity*$product->get_price()
	 	);
	 	$this->user['wc cart size'] = $cart->get_cart_contents_count();
	 	$this->user['wc cart value'] = $cart->subtotal;
	 	if (!is_user_logged_in()) {
			$this->woopra->identify($this->user);
	 	} else {
	 		$this->woopra_detect();
	 	}
	 	$this->woopra->track('wc cart update', $params, true);
	 }

	 /**
	 * Tracks a checkout
	 * @return none
	 */
	 function track_checkout($order_id, $params) {
	 	$this->user["wc cart size"] = 0;
	 	$this->user["wc cart value"] = 0;
	 	if (!is_user_logged_in()) {
	 		$this->user['name'] = $params["billing_first_name"] . " " . $params["billing_last_name"];
			$this->user['email'] = $params["billing_email"];
			$this->woopra->identify($this->user);
	 	} else {
	 		$this->woopra_detect();
	 	}
	 	global $woocommerce;
	 	$cart = $woocommerce->cart;
	 	$order = new WC_Order($order_id);
	 	$new_params = array(
	 		"cart subtotal" => $cart->subtotal,
	 		"cart value" => $order->get_total(),
	 		"cart size" => $order->get_item_count(),
	 		"payment method" => $params["payment_method"],
	 		"shipping method" => $order->get_shipping_method(),
	 		"order discount" => $order->get_total_discount(),
	 		"order number" => $order->get_order_number()
	 	);
	 	$this->woopra->track('wc checkout', $new_params, true);
	 }

	 /**
	 * Tracks a coupon
	 * @return none
	 */
	 function track_coupon($coupon_code) {
	 	$coupon = new WC_COUPON($coupon_code);
		if ($coupon->is_valid()) {
			$this->woopra_detect();
			$params = array(
		 		"code" => $coupon->code,
		 		"discount type" => $coupon->discount_type,
		 		"amount" => $coupon->amount
		 	);
			$this->woopra->track('wc coupon applied', $params, true);
		}
	 }
	
	/**
	 * Loads Wordpress User & identifies it
	 * @return none
	 */
	function woopra_detect() {
		
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$this->user['name'] = $current_user->display_name;
			$this->user['email'] = $current_user->user_email;
			if (current_user_can('manage_options')) {
				$this->user['admin'] = 1;
			}
			//	Identify with woopra
			$this->woopra->identify($this->user);
		}
	}
	
	function track() {
		if ($this->get_option('track_article') && is_single()) {
			$page_data = array();
	        wp_reset_query();
	        global $post;
	        $myvar = get_the_category($post->ID);
	        $myvar = $myvar[0]->cat_name;
			$page_data["author"] = js_escape(get_the_author_meta("display_name",$post->post_author));
			$page_data["category"] = isset($myvar) ? js_escape($myvar) : "Uncategorized";
			$page_data["permalink"] = js_escape(get_the_permalink());
			$page_data["title"] = js_escape(get_the_title());
			$page_data["post date"] = get_the_time('U')*1000;
			$this->woopra->track("wp article", $page_data)->js_code();
		} else {
			$this->woopra->track()->js_code();
		}
	}
	
	/**
	 * Outputs JS tracker
	 * @return none
	 */
	function set_tracker() {
		global $post;
		if (current_user_can('manage_options') && $this->get_option('ignore_admin') == 0) {
			if($this->get_option('track_admin') == 1) {
				add_action('admin_head', array(&$this, 'track'), 10);
			}
			add_action('wp_head', array(&$this, 'track'), 10);
		} elseif(!current_user_can('manage_options')) {
			add_action('wp_head', array(&$this, 'track'), 10);
		}
	}
	
	
		
	/**
	* Loads Woopra configuration
	* @return none
	*/
	function woopra_config() {
		
		if ($this->get_option('trackas')) {
			$this->config["domain"] = $this->get_option('trackas');
		}
		if ($this->get_option('use_timeout')) {
			$this->config["idle_timeout"] = $this->get_option('timeout')*1000;
		}
		$this->config["download_tracking"] = $this->get_option('track_downloads') == 1 ? true : false;
		$this->config["outgoing_tracking"] = $this->get_option('track_outgoing') == 1 ? true : false;
		$this->config["hide_campaign"] = $this->get_option('hide_campaign') == 1 ? true : false;
		$this->config["app"] = "wordpress";
	}
	
}
?>
