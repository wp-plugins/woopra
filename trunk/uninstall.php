<?php
/**
 * Uninstalls the woopra options when an uninstall has been requested 
 * from the WordPress admin. Only can be done from Admin Area!
 *
 * @package woopra
 * @subpackage uninstall
 * @since 1.4.1.1
 */

// If uninstall/delete not called from WordPress then exit
if( ! defined ( 'ABSPATH' ) && ! defined ( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete shadowbox option from options table
delete_option ( 'woopra' );
?>