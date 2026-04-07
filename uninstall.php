<?php
/**
 * Uninstall handler for Joshua Bink | Website beheer.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Cleans up all options, transients, and custom capabilities.
 *
 * @package DeWebmaatjesClientDashboard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Option and capability names (match main plugin file).
$option_name     = 'dwmcd_settings';
$capability_name = 'manage_dewebmaatjes_dashboard';

// 1. Remove the custom capability from every user who has it.
$users = get_users( array( 'fields' => array( 'ID' ) ) );
foreach ( $users as $user_obj ) {
	$user = new WP_User( (int) $user_obj->ID );
	if ( $user->has_cap( $capability_name ) ) {
		$user->remove_cap( $capability_name );
	}
}

// 2. Delete plugin options.
delete_option( $option_name );

// 3. Delete transients.
delete_transient( 'dwmcd_ga4_data' );

// 4. Clean up any site-meta in multisite context.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 1000 ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( $option_name );
		delete_transient( 'dwmcd_ga4_data' );
		restore_current_blog();
	}
}
