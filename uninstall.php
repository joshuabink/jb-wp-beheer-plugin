<?php
/**
 * Uninstall handler for Joshua Bink | Website beheer.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Cleans up temporary data and transients ONLY.
 * Preserves user-configured plugin settings (logo, colors, menu order, etc.)
 * in wp_options so they can be restored if the plugin is reinstalled.
 *
 * @package JB_WP_Beheer_Plugin
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Option and capability names (match main plugin file).
$option_name     = 'jbwp_settings';
$capability_name = 'manage_jbwp_dashboard';

// 1. Remove the custom capability from every user who has it.
$users = get_users( array( 'fields' => array( 'ID' ) ) );
foreach ( $users as $user_obj ) {
	$user = new WP_User( (int) $user_obj->ID );
	if ( $user->has_cap( $capability_name ) ) {
		$user->remove_cap( $capability_name );
	}
}

// 2. Delete temporary/cache data ONLY (NOT settings).
// Settings are preserved in wp_options for re-installation.
delete_transient( 'jbwp_ga4_data' );
delete_transient( 'jbwp_dashboard_cache' );
delete_option( 'jbwp_update_check_time' );

// 3. Clean up any site-meta in multisite context.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 1000 ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_transient( 'jbwp_ga4_data' );
		delete_transient( 'jbwp_dashboard_cache' );
		delete_option( 'jbwp_update_check_time' );
		restore_current_blog();
	}
}

// NOTE: jbwp_settings option is intentionally NOT deleted.
// This preserves user configuration (logo, colors, menu order, etc.)
// when the plugin is reinstalled.
