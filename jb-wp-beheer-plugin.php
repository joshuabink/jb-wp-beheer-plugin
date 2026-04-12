<?php
/**
 * Plugin Name:       JB WP Beheer Plugin
 * Plugin URI:        https://github.com/joshuabink/jb-wp-beheer-plugin
 * Description:       Professioneel klantdashboard voor WordPress websites.
 * Version:           4.3.1
 * Author:            Joshua Bink
 * Author URI:        https://github.com/joshuabink
 * License:           GPL-2.0-or-later
 * Text Domain:       jb-wp-beheer-plugin
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Update URI:        https://github.com/joshuabink/jb-wp-beheer-plugin
 *
 * @package JB_WP_Beheer_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Double-load sentinel ────────────────────────────────────────────────────
// Defensive: if this file ever gets included twice in the same request
// (symlinks, custom loaders, mu-plugins copies, …) the second include silently
// returns instead of fatalling on "Cannot redeclare function". The plugin's
// internal function prefix is now `jbwp_` so it can no longer collide with the
// legacy "De Webmaatjes Client Dashboard" plugin (`dwmcd_` prefix), which means
// both can technically coexist in the plugins folder without crashing.
if ( defined( 'JBWP_PLUGIN_VERSION' ) ) {
	return;
}

// ── Plugin identity ──────────────────────────────────────────────────────────
// Public-facing identifiers (slug, version, paths). Keep in sync with the
// header above so the auto-updater and WP plugin screens use the same values.
define( 'JBWP_PLUGIN_VERSION', '4.3.1' );
define( 'JBWP_PLUGIN_SLUG',    'jb-wp-beheer-plugin' );
define( 'JBWP_PLUGIN_FILE',    __FILE__ );
define( 'JBWP_PLUGIN_DIR',     plugin_dir_path( __FILE__ ) );
define( 'JBWP_PLUGIN_URL',     plugin_dir_url( __FILE__ ) );
define( 'JBWP_GITHUB_REPO',    'https://github.com/joshuabink/jb-wp-beheer-plugin/' );
define( 'JBWP_GITHUB_BRANCH',  'main' );

// ── Backwards-compatible internal constants ─────────────────────────────────
// The plugin originally shipped under the "dwmcd" prefix. The DB option key,
// capability name and version constant are kept identical so existing
// installations keep their settings, user roles and cached data after the
// rename to JB WP Beheer Plugin (v4.0.0).
define( 'DWMCD_OPTION',  'dwmcd_settings' );
define( 'DWMCD_CAP',     'manage_dewebmaatjes_dashboard' );
define( 'DWMCD_VERSION', JBWP_PLUGIN_VERSION );

// ── Auto-update via GitHub Releases ─────────────────────────────────────────
// Loads the YahnisElsts plugin-update-checker library so this plugin can
// receive updates from GitHub Releases without going through wordpress.org.
//
// The loader is fully defensive: if the lib/plugin-update-checker folder is
// missing (e.g. partial install, manual upload, or future swap to a private
// update server), the plugin will still boot normally without fatals.
require_once JBWP_PLUGIN_DIR . 'includes/updater.php';
jbwp_bootstrap_updater();

// ── Defaults ──────────────────────────────────────────────────────────────────

function jbwp_defaults() {
	return array(
		// Dashboard
		'welcome_text'           => 'Welkom in je websitebeheer. Gebruik hieronder de belangrijkste acties.',
		// Logo & site-icoon
		'logo_id'                => 0,
		'site_icon_id'           => 0,
		// Kleuren
		'accent_color'           => '#2952ff',
		'sidebar_bg'             => '#ffffff',
		// Aankondiging
		'announcement_enabled'   => 0,
		'announcement_text'      => '',
		'announcement_type'      => 'info',
		// Google Analytics (GA4)
		'ga4_enabled'            => 0,
		'ga4_property_id'        => '',
		'ga4_service_account'    => '',
		// Site statistieken & recente inhoud
		'show_stats'             => 1,
		'show_recent_content'    => 1,
		'recent_content_count'   => 5,
		'article_cpt_slug'       => 'post',
		// Webshop (WooCommerce)
		'is_webshop'             => 0,
		'webshop_show_stats'     => 1,
		'webshop_show_stock'     => 1,
		'webshop_low_stock_limit'=> 5,
		'webshop_stock_count'    => 5,
		// Menu-organizer
		'menu_organizer_enabled' => 0,
		'menu_groups'            => array(),
		'menu_order'             => array(),
		// Support blok
		'show_support'           => 1,
		'support_title'          => 'Hulp nodig?',
		'support_text'           => 'Neem contact op voor support of wijzigingen aan uw website.',
		'support_button'         => 'Contact opnemen',
		'support_url'            => '',
		// Login pagina branding
		'login_bg_id'            => 0,
		'login_welcome_text'     => '',
		'login_card_style'       => 1,
		// Media & Gebruikers modules
		'media_categories_enabled'  => 0,
		'media_replacement_enabled' => 0,
		'local_avatar_enabled'      => 0,
		// Menu verbergen (niet-beheerders)
		'hide_comments'          => 1,
		'hide_tools'             => 1,
		// WP branding
		'hide_wp_logo'           => 1,
		'hide_notices'           => 1,
		'custom_footer'          => 1,
		'footer_text'            => 'Website beheerd door Joshua Bink',
		// Toegang
		'manager_user_ids'       => array(),
		// Snelle acties
		'quick_actions'          => array(
			array(
				'enabled'     => 1,
				'title'       => 'Nieuw bericht',
				'description' => 'Schrijf een nieuw bericht of artikel',
				'icon'        => 'edit',
				'action_type' => 'new_post_type',
				'target'      => 'post',
				'capability'  => 'edit_posts',
			),
			array(
				'enabled'     => 1,
				'title'       => "Pagina's beheren",
				'description' => "Bekijk en bewerk alle pagina's",
				'icon'        => 'admin-page',
				'action_type' => 'list_post_type',
				'target'      => 'page',
				'capability'  => 'edit_pages',
			),
			array(
				'enabled'     => 1,
				'title'       => 'Afbeeldingen uploaden',
				'description' => "Upload foto's en bestanden",
				'icon'        => 'format-image',
				'action_type' => 'media_upload',
				'target'      => '',
				'capability'  => 'upload_files',
			),
			array(
				'enabled'     => 1,
				'title'       => 'Website bekijken',
				'description' => 'Open de live website',
				'icon'        => 'external',
				'action_type' => 'site_url',
				'target'      => '',
				'capability'  => 'read',
			),
		),
	);
}

function jbwp_get_settings() {
	$saved = get_option( DWMCD_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, jbwp_defaults() );
}

// ── Activation / deactivation ─────────────────────────────────────────────────

function jbwp_activate() {
	if ( ! get_option( DWMCD_OPTION ) ) {
		add_option( DWMCD_OPTION, jbwp_defaults() );
	}
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if ( $user instanceof WP_User && $user->ID && ! $user->has_cap( DWMCD_CAP ) ) {
			$user->add_cap( DWMCD_CAP );
		}
	}
}
register_activation_hook( __FILE__, 'jbwp_activate' );

function jbwp_deactivate() {
	// Only iterate users that actually have the capability (efficient for large sites)
	$s   = jbwp_get_settings();
	$ids = array_map( 'absint', (array) ( $s['manager_user_ids'] ?? array() ) );
	$cur = get_current_user_id();
	if ( $cur ) {
		$ids[] = $cur;
	}
	$ids = array_unique( array_filter( $ids ) );
	foreach ( $ids as $uid ) {
		$user = get_user_by( 'id', (int) $uid );
		if ( $user instanceof WP_User && $user->has_cap( DWMCD_CAP ) ) {
			$user->remove_cap( DWMCD_CAP );
		}
	}
	delete_transient( 'dwmcd_ga4_data' );
}
register_deactivation_hook( __FILE__, 'jbwp_deactivate' );

// ── Lookup helpers ────────────────────────────────────────────────────────────

function jbwp_type_options() {
	return array(
		'new_post_type'  => 'Nieuw item aanmaken (post type)',
		'list_post_type' => 'Overzicht openen (post type)',
		'media_upload'   => 'Media uploaden',
		'media_library'  => 'Mediabibliotheek openen',
		'admin_url'      => 'WordPress admin pagina (vrije URL)',
		'external_url'   => 'Externe link (nieuw tabblad)',
		'site_url'       => 'Website bezoeken (nieuw tabblad)',
	);
}

function jbwp_type_hints() {
	return array(
		'new_post_type'  => 'Post type slug, bijv: post, page, product, vacature, project.',
		'list_post_type' => 'Post type slug, bijv: post, page, product, vacature, project.',
		'media_upload'   => 'Opent de media-uploadpagina. Geen doel nodig.',
		'media_library'  => 'Opent de mediabibliotheek. Geen doel nodig.',
		'admin_url'      => 'Relatief pad na /wp-admin/. Voorbeelden: edit-comments.php (reacties), users.php (gebruikers), admin.php?page=gf_entries (Gravity Forms), admin.php?page=wpforms-entries (WPForms), admin.php?page=wpcf7 (Contact Form 7), customize.php (customizer), nav-menus.php (menu\'s), edit.php?post_type=shop_order (WooCommerce bestellingen).',
		'external_url'   => 'Volledige URL inclusief https://. Opent in een nieuw tabblad.',
		'site_url'       => 'Opent de live website in een nieuw tabblad. Geen doel nodig.',
	);
}

function jbwp_type_placeholders() {
	return array(
		'new_post_type'  => 'bijv. post, page, product, vacature',
		'list_post_type' => 'bijv. post, page, product, vacature',
		'media_upload'   => '',
		'media_library'  => '',
		'admin_url'      => 'bijv. edit-comments.php of admin.php?page=gf_entries',
		'external_url'   => 'https://...',
		'site_url'       => '',
	);
}

function jbwp_types_without_target() {
	return array( 'media_upload', 'media_library', 'site_url' );
}

function jbwp_types_new_tab() {
	return array( 'external_url', 'site_url' );
}

function jbwp_icon_options() {
	return array(
		'edit'               => 'Potlood',
		'media-document'     => 'Document',
		'admin-page'         => 'Pagina',
		'admin-post'         => 'Bericht',
		'welcome-write-blog' => 'Blog schrijven',
		'text'               => 'Tekst',
		'tag'                => 'Tag',
		'category'           => 'Categorie',
		'format-quote'       => 'Citaat',
		'sticky'             => 'Vastgezet',
		'clipboard'          => 'Klembord',
		'book'               => 'Boek',
		'upload'             => 'Upload',
		'images-alt2'        => 'Afbeelding',
		'format-gallery'     => 'Galerij',
		'video-alt3'         => 'Video',
		'format-audio'       => 'Audio',
		'camera'             => 'Camera',
		'format-image'       => 'Foto',
		'admin-links'        => 'Link',
		'external'           => 'Externe link',
		'search'             => 'Zoeken',
		'plus-alt'           => 'Toevoegen',
		'update-alt'         => 'Bijwerken',
		'yes-alt'            => 'Vinkje',
		'star-filled'        => 'Ster',
		'heart'              => 'Hart',
		'flag'               => 'Vlag',
		'download'           => 'Downloaden',
		'share'              => 'Delen',
		'admin-home'         => 'Home',
		'menu'               => 'Menu',
		'arrow-right-alt'    => 'Pijl rechts',
		'list-view'          => 'Lijst',
		'grid-view'          => 'Raster',
		'dashboard'          => 'Dashboard',
		'businessperson'     => 'Persoon',
		'groups'             => 'Groep',
		'email'              => 'Email',
		'phone'              => 'Telefoon',
		'calendar-alt'       => 'Kalender',
		'chart-bar'          => 'Grafiek',
		'money-alt'          => 'Geld',
		'products'           => 'Producten',
		'cart'               => 'Winkelwagen',
		'store'              => 'Winkel',
		'portfolio'          => 'Portfolio',
		'admin-tools'        => 'Gereedschap',
		'admin-settings'     => 'Instellingen',
		'admin-plugins'      => 'Plugins',
		'shield'             => 'Beveiliging',
		'backup'             => 'Backup',
		'database'           => 'Database',
		'car'                => 'Auto',
		'building'           => 'Gebouw',
		'feedback'           => 'Bericht',
		'location'           => 'Locatie',
		'hammer'             => 'Hamer',
		'lightbulb'          => 'Idee',
		'visibility'         => 'Zichtbaar',
		'lock'               => 'Slot',
		'info'               => 'Info',
		'warning'            => 'Waarschuwing',
		'bell'               => 'Notificatie',
	);
}

function jbwp_action_url( $action, $settings = null ) {
	if ( null === $settings ) {
		$settings = jbwp_get_settings();
	}
	$type   = $action['action_type'] ?? '';
	$target = $action['target'] ?? '';

	switch ( $type ) {
		case 'new_post_type':
			$target = sanitize_key( $target ?: 'post' );
			return admin_url( 'post-new.php?post_type=' . $target );
		case 'list_post_type':
			$target = sanitize_key( $target ?: 'post' );
			return 'post' === $target ? admin_url( 'edit.php' ) : admin_url( 'edit.php?post_type=' . $target );
		case 'media_upload':
			return admin_url( 'media-new.php' );
		case 'media_library':
			return admin_url( 'upload.php' );
		case 'admin_url':
			return $target ? admin_url( ltrim( $target, '/' ) ) : '';
		case 'external_url':
			return $target ? esc_url( $target ) : '';
		case 'site_url':
			return home_url( '/' );
		default:
			return '';
	}
}

function jbwp_resolved_actions() {
	$settings = jbwp_get_settings();
	$new_tab  = jbwp_types_new_tab();
	$output   = array();

	foreach ( (array) $settings['quick_actions'] as $action ) {
		if ( empty( $action['enabled'] ) || empty( $action['title'] ) ) {
			continue;
		}
		$cap = ! empty( $action['capability'] ) ? $action['capability'] : 'read';
		if ( ! current_user_can( $cap ) ) {
			continue;
		}
		$url = jbwp_action_url( $action, $settings );
		if ( ! $url ) {
			continue;
		}
		$action['url']     = $url;
		$action['new_tab'] = in_array( $action['action_type'] ?? '', $new_tab, true );
		$output[]          = $action;
	}
	return $output;
}

// ── Shared helpers ───────────────────────────────────────────────────────────

/**
 * Parse sidebar background hex and return derived color variables.
 */
function jbwp_parse_sidebar_colors( $hex ) {
	$sb      = sscanf( $hex, '#%02x%02x%02x' );
	$sb_r    = isset( $sb[0] ) ? (int) $sb[0] : 255;
	$sb_g    = isset( $sb[1] ) ? (int) $sb[1] : 255;
	$sb_b    = isset( $sb[2] ) ? (int) $sb[2] : 255;
	$lum     = ( $sb_r * 299 + $sb_g * 587 + $sb_b * 114 ) / 1000;
	$is_dark = $lum <= 140;
	return array(
		'lum'        => $lum,
		'is_dark'    => $is_dark,
		'text'       => $is_dark ? '#e8eef8' : '#35435d',
		'muted'      => $is_dark ? '#a0aec0' : '#8a9ab5',
		'hover'      => $is_dark ? 'rgba(255,255,255,0.10)' : 'rgba(0,0,0,0.06)',
		'sub'        => $is_dark ? '#1a1f2e' : '#f4f6fa',
		'sub_border' => $is_dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
	);
}

/**
 * Output properly sized favicon <link> tags for a given attachment ID.
 * Uses wp_get_attachment_image_url() to serve correctly sized images
 * instead of the full-size original.
 */
function jbwp_favicon_tags( $icon_id ) {
	$icon_id = (int) $icon_id;
	if ( ! $icon_id ) {
		return;
	}
	$fallback = wp_get_attachment_url( $icon_id );
	if ( ! $fallback ) {
		return;
	}
	$url_16  = wp_get_attachment_image_url( $icon_id, array( 16, 16 ) )   ?: $fallback;
	$url_32  = wp_get_attachment_image_url( $icon_id, array( 32, 32 ) )   ?: $fallback;
	$url_180 = wp_get_attachment_image_url( $icon_id, array( 180, 180 ) ) ?: $fallback;
	$url_192 = wp_get_attachment_image_url( $icon_id, array( 192, 192 ) ) ?: $fallback;

	echo '<link rel="icon" href="' . esc_url( $url_32 ) . '" sizes="32x32" type="image/png">';
	echo '<link rel="icon" href="' . esc_url( $url_16 ) . '" sizes="16x16" type="image/png">';
	echo '<link rel="icon" href="' . esc_url( $url_192 ) . '" sizes="192x192" type="image/png">';
	echo '<link rel="apple-touch-icon" href="' . esc_url( $url_180 ) . '">';
}

/**
 * Get GA4 service account JSON — supports wp-config.php constant as secure alternative.
 */
function jbwp_ga4_json() {
	if ( defined( 'DWMCD_GA4_JSON' ) && '' !== DWMCD_GA4_JSON ) {
		return DWMCD_GA4_JSON;
	}
	$s = jbwp_get_settings();
	return $s['ga4_service_account'] ?? '';
}

// ── GA4 helpers ───────────────────────────────────────────────────────────────

function jbwp_b64url( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

function jbwp_ga4_credentials() {
	$s = jbwp_get_settings();
	$json = jbwp_ga4_json();
	if ( empty( $s['ga4_enabled'] ) || empty( $json ) ) {
		return null;
	}
	$creds = json_decode( $json, true );
	if ( ! is_array( $creds )
		|| empty( $creds['private_key'] )
		|| empty( $creds['client_email'] ) ) {
		return null;
	}
	return $creds;
}

function jbwp_ga4_jwt( $creds ) {
	if ( ! function_exists( 'openssl_sign' ) ) {
		return null;
	}
	$now     = time();
	$header  = jbwp_b64url( (string) wp_json_encode( array( 'alg' => 'RS256', 'typ' => 'JWT' ) ) );
	$payload = jbwp_b64url( (string) wp_json_encode( array(
		'iss'   => $creds['client_email'],
		'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
		'aud'   => 'https://oauth2.googleapis.com/token',
		'exp'   => $now + 3600,
		'iat'   => $now,
	) ) );
	$signing_input = $header . '.' . $payload;
	$pkey = openssl_pkey_get_private( $creds['private_key'] );
	if ( ! $pkey ) {
		return null;
	}
	openssl_sign( $signing_input, $sig, $pkey, 'SHA256' );
	return $signing_input . '.' . jbwp_b64url( $sig );
}

function jbwp_ga4_access_token( $creds ) {
	$jwt = jbwp_ga4_jwt( $creds );
	if ( ! $jwt ) {
		return null;
	}
	$response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
		'timeout' => 10,
		'body'    => array(
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion'  => $jwt,
		),
	) );
	if ( is_wp_error( $response ) ) {
		return null;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return $body['access_token'] ?? null;
}

function jbwp_ga4_run_report( $property_id, $token, $body ) {
	$response = wp_remote_post(
		'https://analyticsdata.googleapis.com/v1beta/properties/' . (int) $property_id . ':runReport',
		array(
			'timeout' => 10,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body' => (string) wp_json_encode( $body ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return null;
	}
	return json_decode( wp_remote_retrieve_body( $response ), true );
}

function jbwp_ga4_fetch_fresh() {
	$creds = jbwp_ga4_credentials();
	if ( ! $creds ) {
		return null;
	}
	$s           = jbwp_get_settings();
	$property_id = preg_replace( '/[^0-9]/', '', $s['ga4_property_id'] ?? '' );
	if ( ! $property_id ) {
		return null;
	}

	$token = jbwp_ga4_access_token( $creds );
	if ( ! $token ) {
		return array( 'error' => 'auth_failed' );
	}

	$daily_report = jbwp_ga4_run_report( $property_id, $token, array(
		'dateRanges' => array( array( 'startDate' => '27daysAgo', 'endDate' => 'today' ) ),
		'dimensions' => array( array( 'name' => 'date' ) ),
		'metrics'    => array( array( 'name' => 'sessions' ) ),
		'orderBys'   => array( array( 'dimension' => array( 'dimensionName' => 'date' ) ) ),
		'limit'      => 28,
	) );

	$prev_report = jbwp_ga4_run_report( $property_id, $token, array(
		'dateRanges' => array( array( 'startDate' => '55daysAgo', 'endDate' => '28daysAgo' ) ),
		'metrics'    => array( array( 'name' => 'sessions' ) ),
	) );

	$channel_report = jbwp_ga4_run_report( $property_id, $token, array(
		'dateRanges' => array( array( 'startDate' => '27daysAgo', 'endDate' => 'today' ) ),
		'dimensions' => array( array( 'name' => 'sessionDefaultChannelGroup' ) ),
		'metrics'    => array( array( 'name' => 'sessions' ) ),
		'orderBys'   => array( array( 'metric' => array( 'metricName' => 'sessions' ), 'desc' => true ) ),
		'limit'      => 6,
	) );

	if ( null === $daily_report ) {
		return array( 'error' => 'api_failed' );
	}

	$daily_sessions = array();
	$total_sessions = 0;
	foreach ( $daily_report['rows'] ?? array() as $row ) {
		$v               = (int) ( $row['metricValues'][0]['value'] ?? 0 );
		$daily_sessions[] = $v;
		$total_sessions  += $v;
	}

	$prev_sessions = 0;
	foreach ( $prev_report['rows'] ?? array() as $row ) {
		$prev_sessions += (int) ( $row['metricValues'][0]['value'] ?? 0 );
	}

	$channels = array();
	foreach ( $channel_report['rows'] ?? array() as $row ) {
		$channels[] = array(
			'name'     => $row['dimensionValues'][0]['value'] ?? 'Onbekend',
			'sessions' => (int) ( $row['metricValues'][0]['value'] ?? 0 ),
		);
	}

	$data = array(
		'total_sessions' => $total_sessions,
		'prev_sessions'  => $prev_sessions,
		'daily_sessions' => $daily_sessions,
		'channels'       => $channels,
		'fetched_at'     => time(),
	);
	set_transient( 'dwmcd_ga4_data', $data, 3 * HOUR_IN_SECONDS );
	return $data;
}

function jbwp_ga4_get_data() {
	$cached = get_transient( 'dwmcd_ga4_data' );
	return ( false !== $cached ) ? $cached : jbwp_ga4_fetch_fresh();
}

function jbwp_ga4_sparkline( $values ) {
	$values = array_map( 'intval', $values );
	if ( count( $values ) < 2 ) {
		return '';
	}
	$max = max( $values ) ?: 1;
	$w   = 600;
	$h   = 80;
	$n   = count( $values );
	$pts = array();
	foreach ( $values as $i => $v ) {
		$x     = round( ( $i / ( $n - 1 ) ) * $w, 1 );
		$y     = round( $h - ( $v / $max ) * ( $h * 0.92 ), 1 );
		$pts[] = $x . ',' . $y;
	}
	$line_d = 'M ' . implode( ' L ', $pts );
	$fill_d = $line_d . ' L ' . $w . ',' . $h . ' L 0,' . $h . ' Z';
	return '<svg class="dwmcd-ga4-chart" viewBox="0 0 ' . $w . ' ' . $h
		. '" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">'
		. '<defs><linearGradient id="ga4g" x1="0" y1="0" x2="0" y2="1">'
		. '<stop offset="0%" stop-color="var(--dwmcd-accent)" stop-opacity="0.18"/>'
		. '<stop offset="100%" stop-color="var(--dwmcd-accent)" stop-opacity="0"/>'
		. '</linearGradient></defs>'
		. '<path d="' . esc_attr( $fill_d ) . '" fill="url(#ga4g)"/>'
		. '<path d="' . esc_attr( $line_d ) . '" fill="none" stroke="var(--dwmcd-accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';
}

function jbwp_ga4_render_widget( $data ) {
	if ( ! $data ) {
		return '<div class="dwmcd-ga4-unconfigured"><span class="dashicons dashicons-chart-area"></span><p>Geen data beschikbaar. Controleer de Analytics instellingen.</p></div>';
	}
	if ( isset( $data['error'] ) ) {
		$msg = 'auth_failed' === $data['error']
			? 'Authenticatie mislukt — controleer de service account JSON en het property ID.'
			: 'API-aanroep mislukt — controleer het property ID en de rechten van het service account.';
		return '<div class="dwmcd-ga4-error"><span class="dashicons dashicons-warning"></span><p>' . esc_html( $msg ) . '</p></div>';
	}

	$total    = (int) ( $data['total_sessions'] ?? 0 );
	$prev     = (int) ( $data['prev_sessions'] ?? 0 );
	$trend    = $prev > 0 ? round( ( $total - $prev ) / $prev * 100, 1 ) : 0;
	$trend_up = $trend >= 0;
	$daily    = $data['daily_sessions'] ?? array();
	$channels = $data['channels'] ?? array();
	$ch_total = array_sum( array_column( $channels, 'sessions' ) ) ?: 1;

	$ch_colors = array( '#4f86f7', '#a78bfa', '#93c5fd', '#f9a8d4', '#fca5a5', '#86efac' );
	$nl_names  = array(
		'Organic Search' => 'Organisch zoeken',
		'Direct'         => 'Direct',
		'Organic Social' => 'Organisch sociaal',
		'Referral'       => 'Verwijzing',
		'Unassigned'     => 'Niet toegewezen',
		'Email'          => 'E-mail',
		'Paid Search'    => 'Betaald zoeken',
		'Paid Social'    => 'Betaald sociaal',
		'Display'        => 'Display',
	);

	ob_start();
	?>
	<div class="dwmcd-ga4-widget">
		<div class="dwmcd-ga4-left">
			<small class="dwmcd-ga4-label">Alle bezoekers &mdash; afgelopen 28 dagen</small>
			<div class="dwmcd-ga4-total"><?php echo number_format_i18n( $total ); ?></div>
			<div class="dwmcd-ga4-trend <?php echo $trend_up ? 'up' : 'down'; ?>">
				<span><?php echo $trend_up ? '&#8593;' : '&#8595;'; ?> <?php echo esc_html( abs( $trend ) ); ?>%</span>
				<small>vergeleken met de 28 vorige dagen</small>
			</div>
			<?php echo jbwp_ga4_sparkline( $daily ); ?>
		</div>
		<div class="dwmcd-ga4-right">
			<small class="dwmcd-ga4-label">Per kanaal</small>
			<div class="dwmcd-ga4-channels">
				<?php foreach ( $channels as $i => $ch ) :
					$pct   = round( $ch['sessions'] / $ch_total * 100, 1 );
					$color = $ch_colors[ $i % count( $ch_colors ) ];
					$name  = $nl_names[ $ch['name'] ] ?? $ch['name'];
				?>
				<div class="dwmcd-ga4-channel">
					<div class="dwmcd-ga4-channel-header">
						<span class="dwmcd-ga4-dot" style="background:<?php echo esc_attr( $color ); ?>"></span>
						<span class="dwmcd-ga4-channel-name"><?php echo esc_html( $name ); ?></span>
						<span class="dwmcd-ga4-channel-pct"><?php echo esc_html( $pct ); ?>%</span>
					</div>
					<div class="dwmcd-ga4-bar-wrap">
						<div class="dwmcd-ga4-bar" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $color ); ?>"></div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<small class="dwmcd-ga4-source">Bron: Google Analytics</small>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

// ── WooCommerce / Webshop helpers ─────────────────────────────────────────────

function jbwp_woo_active() {
	return class_exists( 'WooCommerce' );
}

function jbwp_webshop_enabled() {
	$s = jbwp_get_settings();
	return ! empty( $s['is_webshop'] ) && jbwp_woo_active();
}

/**
 * Get aggregated webshop stats for the given period (in days).
 * Returns an array with revenue, order_count, aov, customer_count + previous-period equivalents.
 */
function jbwp_webshop_get_stats() {
	if ( ! jbwp_woo_active() ) {
		return null;
	}
	$cached = get_transient( 'dwmcd_webshop_stats' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$out      = array();
	$periods  = array(
		'today'  => array( 'days' => 1,  'label' => 'Vandaag' ),
		'week'   => array( 'days' => 7,  'label' => 'Afgelopen 7 dagen' ),
		'month'  => array( 'days' => 30, 'label' => 'Afgelopen 30 dagen' ),
	);
	$statuses = array( 'wc-processing', 'wc-completed', 'wc-on-hold' );

	foreach ( $periods as $key => $cfg ) {
		$days = (int) $cfg['days'];
		// Current period
		if ( 1 === $days ) {
			$start = strtotime( 'today midnight' );
		} else {
			$start = strtotime( '-' . $days . ' days midnight' );
		}
		$end       = current_time( 'timestamp' );
		$prev_end  = $start - 1;
		$prev_start = $prev_end - ( $end - $start );

		$current = jbwp_webshop_query_orders( $start, $end, $statuses );
		$prev    = jbwp_webshop_query_orders( $prev_start, $prev_end, $statuses );

		$out[ $key ] = array(
			'label'         => $cfg['label'],
			'revenue'       => $current['revenue'],
			'orders'        => $current['count'],
			'aov'           => $current['count'] > 0 ? $current['revenue'] / $current['count'] : 0,
			'prev_revenue'  => $prev['revenue'],
			'prev_orders'   => $prev['count'],
		);
	}

	// Daily revenue last 14 days for sparkline
	$daily = array();
	for ( $i = 13; $i >= 0; $i-- ) {
		$day_start = strtotime( '-' . $i . ' days midnight' );
		$day_end   = $day_start + DAY_IN_SECONDS - 1;
		$d         = jbwp_webshop_query_orders( $day_start, $day_end, $statuses );
		$daily[]   = (float) $d['revenue'];
	}
	$out['daily_revenue'] = $daily;

	// New customers last 30 days
	$new_customers = 0;
	if ( function_exists( 'get_users' ) ) {
		$users = get_users( array(
			'role'         => 'customer',
			'date_query'   => array(
				array( 'after' => '30 days ago' ),
			),
			'fields'       => 'ID',
			'number'       => -1,
		) );
		$new_customers = is_array( $users ) ? count( $users ) : 0;
	}
	$out['new_customers'] = $new_customers;

	$out['currency_symbol'] = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '€';

	set_transient( 'dwmcd_webshop_stats', $out, 5 * MINUTE_IN_SECONDS );
	return $out;
}

/**
 * Query WooCommerce orders between two timestamps and return revenue + count.
 * Uses wc_get_orders for HPOS compatibility.
 */
function jbwp_webshop_query_orders( $start, $end, $statuses ) {
	$result = array( 'revenue' => 0.0, 'count' => 0 );
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return $result;
	}
	$args = array(
		'limit'        => -1,
		'status'       => $statuses,
		'date_created' => sprintf( '%d...%d', (int) $start, (int) $end ),
		'return'       => 'objects',
		'type'         => 'shop_order',
	);
	$orders = wc_get_orders( $args );
	if ( ! is_array( $orders ) ) {
		return $result;
	}
	foreach ( $orders as $order ) {
		if ( ! is_object( $order ) ) {
			continue;
		}
		$result['revenue'] += (float) $order->get_total();
		$result['count']++;
	}
	return $result;
}

/**
 * Get low stock + out of stock products with limit.
 */
function jbwp_webshop_get_stock_alerts( $low_threshold = 5, $limit = 10 ) {
	if ( ! jbwp_woo_active() ) {
		return null;
	}
	global $wpdb;

	// Out of stock count (separate from list)
	$out_of_stock_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_stock_status' AND pm.meta_value = 'outofstock'
		 WHERE p.post_type IN ('product','product_variation') AND p.post_status = 'publish'"
	);

	// Low stock list (in stock but at or below threshold)
	$low_threshold = max( 0, (int) $low_threshold );
	$limit         = max( 1, (int) $limit );

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT p.ID AS id, p.post_title AS title, p.post_type AS type, p.post_parent AS parent_id, CAST(pm.meta_value AS SIGNED) AS stock
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm  ON p.ID = pm.post_id AND pm.meta_key = '_stock'
		 INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_manage_stock' AND pm2.meta_value = 'yes'
		 INNER JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock_status' AND pm3.meta_value = 'instock'
		 WHERE p.post_type IN ('product','product_variation') AND p.post_status = 'publish'
		 AND CAST(pm.meta_value AS SIGNED) <= %d AND CAST(pm.meta_value AS SIGNED) > 0
		 ORDER BY CAST(pm.meta_value AS SIGNED) ASC
		 LIMIT %d",
		$low_threshold,
		$limit
	) );

	$low_count = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm  ON p.ID = pm.post_id AND pm.meta_key = '_stock'
		 INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_manage_stock' AND pm2.meta_value = 'yes'
		 INNER JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock_status' AND pm3.meta_value = 'instock'
		 WHERE p.post_type IN ('product','product_variation') AND p.post_status = 'publish'
		 AND CAST(pm.meta_value AS SIGNED) <= %d AND CAST(pm.meta_value AS SIGNED) > 0",
		$low_threshold
	) );

	return array(
		'low_count'          => $low_count,
		'out_of_stock_count' => $out_of_stock_count,
		'low_items'          => is_array( $rows ) ? $rows : array(),
	);
}

/**
 * Format an amount as currency using WooCommerce settings if available.
 */
function jbwp_webshop_format_money( $amount, $symbol = '€' ) {
	if ( function_exists( 'wc_price' ) ) {
		return wp_strip_all_tags( wc_price( $amount ) );
	}
	return $symbol . ' ' . number_format_i18n( (float) $amount, 2 );
}

/**
 * Render a small SVG sparkline for daily revenue.
 */
function jbwp_webshop_sparkline( $values ) {
	$values = array_values( array_map( 'floatval', (array) $values ) );
	if ( empty( $values ) ) {
		return '';
	}
	$w   = 100;
	$h   = 28;
	$max = max( $values );
	$min = min( $values );
	if ( $max === $min ) {
		$max = $min + 1;
	}
	$step = count( $values ) > 1 ? $w / ( count( $values ) - 1 ) : $w;
	$pts  = array();
	foreach ( $values as $i => $v ) {
		$x = round( $i * $step, 2 );
		$y = round( $h - ( ( $v - $min ) / ( $max - $min ) ) * $h, 2 );
		$pts[] = $x . ',' . $y;
	}
	$line = 'M' . implode( ' L', $pts );
	$fill = $line . ' L' . $w . ',' . $h . ' L0,' . $h . ' Z';
	return '<svg class="dwmcd-webshop-spark" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none">'
		. '<defs><linearGradient id="dwmcdWsg" x1="0" y1="0" x2="0" y2="1">'
		. '<stop offset="0" stop-color="#10b981" stop-opacity=".25"/>'
		. '<stop offset="1" stop-color="#10b981" stop-opacity="0"/>'
		. '</linearGradient></defs>'
		. '<path d="' . esc_attr( $fill ) . '" fill="url(#dwmcdWsg)"/>'
		. '<path d="' . esc_attr( $line ) . '" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';
}

function jbwp_webshop_render_stats_widget() {
	$data = jbwp_webshop_get_stats();
	if ( ! $data ) {
		return '';
	}
	$sym = $data['currency_symbol'] ?? '€';

	$periods = array( 'today' => 'today', 'week' => 'week', 'month' => 'month' );
	ob_start();
	?>
	<div class="dwmcd-webshop-widget">
		<div class="dwmcd-webshop-stats-row">
			<?php foreach ( $periods as $key ) :
				$p = $data[ $key ];
				$delta = $p['prev_revenue'] > 0 ? round( ( $p['revenue'] - $p['prev_revenue'] ) / $p['prev_revenue'] * 100, 1 ) : 0;
				$up    = $delta >= 0;
			?>
			<div class="dwmcd-webshop-stat">
				<small class="dwmcd-webshop-stat-label"><?php echo esc_html( $p['label'] ); ?></small>
				<div class="dwmcd-webshop-stat-value"><?php echo esc_html( jbwp_webshop_format_money( $p['revenue'], $sym ) ); ?></div>
				<div class="dwmcd-webshop-stat-meta">
					<span class="dwmcd-webshop-trend <?php echo $up ? 'up' : 'down'; ?>">
						<?php echo $up ? '&#8593;' : '&#8595;'; ?> <?php echo esc_html( abs( $delta ) ); ?>%
					</span>
					<span class="dwmcd-webshop-stat-orders"><?php echo (int) $p['orders']; ?> bestelling<?php echo $p['orders'] === 1 ? '' : 'en'; ?></span>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<div class="dwmcd-webshop-secondary">
			<div class="dwmcd-webshop-secondary-item">
				<small class="dwmcd-webshop-stat-label">Gemiddelde orderwaarde (30d)</small>
				<strong><?php echo esc_html( jbwp_webshop_format_money( $data['month']['aov'], $sym ) ); ?></strong>
			</div>
			<div class="dwmcd-webshop-secondary-item">
				<small class="dwmcd-webshop-stat-label">Nieuwe klanten (30d)</small>
				<strong><?php echo (int) $data['new_customers']; ?></strong>
			</div>
			<div class="dwmcd-webshop-secondary-item dwmcd-webshop-spark-wrap">
				<small class="dwmcd-webshop-stat-label">Omzet trend (14d)</small>
				<?php echo jbwp_webshop_sparkline( $data['daily_revenue'] ); ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function jbwp_webshop_render_stock_widget() {
	$s     = jbwp_get_settings();
	$alert = jbwp_webshop_get_stock_alerts(
		(int) ( $s['webshop_low_stock_limit'] ?? 5 ),
		(int) ( $s['webshop_stock_count'] ?? 5 )
	);
	if ( ! $alert ) {
		return '';
	}

	ob_start();
	?>
	<div class="dwmcd-webshop-stock">
		<div class="dwmcd-webshop-stock-summary">
			<div class="dwmcd-webshop-stock-stat warn">
				<span class="dashicons dashicons-warning"></span>
				<div>
					<strong><?php echo (int) $alert['low_count']; ?></strong>
					<small>Lage voorraad</small>
				</div>
			</div>
			<div class="dwmcd-webshop-stock-stat danger">
				<span class="dashicons dashicons-dismiss"></span>
				<div>
					<strong><?php echo (int) $alert['out_of_stock_count']; ?></strong>
					<small>Uitverkocht</small>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $alert['low_items'] ) ) : ?>
			<ul class="dwmcd-webshop-stock-list">
				<?php foreach ( $alert['low_items'] as $row ) :
					$pid       = (int) $row->id;
					$parent_id = (int) $row->parent_id;
					$edit_id   = $parent_id ? $parent_id : $pid;
					$title     = get_the_title( $edit_id ) ?: '(Geen titel)';
					if ( $parent_id && 'product_variation' === $row->type ) {
						$title .= ' &mdash; variant #' . $pid;
					}
				?>
					<li>
						<div class="dwmcd-webshop-stock-info">
							<a href="<?php echo esc_url( (string) get_edit_post_link( $edit_id ) ); ?>"><?php echo wp_kses_post( $title ); ?></a>
							<small><?php echo (int) $row->stock; ?> op voorraad</small>
						</div>
						<a class="dwmcd-edit-btn" href="<?php echo esc_url( (string) get_edit_post_link( $edit_id ) ); ?>">Bewerken</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( 0 === (int) $alert['out_of_stock_count'] ) : ?>
			<p class="dwmcd-empty-msg"><span class="dashicons dashicons-yes-alt" style="color:#16a34a"></span> Geen voorraadwaarschuwingen — alles op orde!</p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

// ── Menu-organizer helpers ────────────────────────────────────────────────────

/**
 * Returns current WP $menu items as flat array of [slug, label, icon].
 * Must be called after admin_menu has fired.
 */
function jbwp_current_menu_items() {
	global $menu;
	$items = array();
	if ( ! is_array( $menu ) ) {
		return $items;
	}
	foreach ( $menu as $item ) {
		if ( ! isset( $item[2] ) || '' === $item[2] ) {
			continue;
		}
		if ( isset( $item[4] ) && false !== strpos( $item[4], 'wp-menu-separator' ) ) {
			continue;
		}
		$raw   = $item[0] ?? $item[2];
		$label = trim( wp_strip_all_tags( preg_replace( '/<span\b[^>]*>.*?<\/span>/is', '', $raw ) ) );
		if ( '' === $label ) {
			$label = $item[2];
		}
		$items[] = array(
			'slug'  => $item[2],
			'label' => $label,
			'icon'  => $item[6] ?? '',
		);
	}
	return $items;
}

/**
 * Returns ['groups' => [...], 'items' => [...]] for the menu editor UI.
 * Items include live label/icon merged with saved config (visible, custom_label, group).
 */
function jbwp_build_menu_config( $settings ) {
	$current = jbwp_current_menu_items();
	$by_slug = array();
	foreach ( $current as $item ) {
		$by_slug[ $item['slug'] ] = $item;
	}

	$saved_items  = (array) ( $settings['menu_order'] ?? array() );
	$saved_groups = (array) ( $settings['menu_groups'] ?? array() );

	$defaults = array(
		'visible'      => 1,
		'custom_label' => '',
		'group'        => '',
	);

	// Build ordered items list
	$result = array();
	$seen   = array();

	foreach ( $saved_items as $config ) {
		$slug = $config['slug'] ?? '';
		if ( ! $slug || ! isset( $by_slug[ $slug ] ) ) {
			continue;
		}
		$seen[ $slug ] = true;
		$result[]      = array_merge( $defaults, $by_slug[ $slug ], array(
			'visible'      => isset( $config['visible'] ) ? (int) $config['visible'] : 1,
			'custom_label' => $config['custom_label'] ?? '',
			'group'        => $config['group'] ?? '',
		) );
	}

	// Append any new items not yet in saved config
	foreach ( $current as $item ) {
		if ( ! isset( $seen[ $item['slug'] ] ) ) {
			$result[] = array_merge( $defaults, $item );
		}
	}

	return array(
		'groups' => $saved_groups,
		'items'  => $result,
	);
}

// ── Settings registration & sanitisation ──────────────────────────────────────

add_action( 'admin_init', function () {
	register_setting( 'dwmcd_group', DWMCD_OPTION, 'jbwp_sanitize_settings' );
} );

function jbwp_sanitize_settings( $input ) {
	if ( ! is_array( $input ) ) {
		return jbwp_get_settings();
	}
	$d   = jbwp_defaults();
	$out = jbwp_get_settings();

	// Dashboard
	$out['welcome_text']  = sanitize_textarea_field( $input['welcome_text']  ?? $d['welcome_text'] );
	// Logo & site-icoon
	$out['logo_id']      = absint( $input['logo_id']      ?? 0 );
	$out['site_icon_id'] = absint( $input['site_icon_id'] ?? 0 );
	// Kleuren
	$out['accent_color'] = sanitize_hex_color( $input['accent_color'] ?? $d['accent_color'] ) ?: $d['accent_color'];
	$out['sidebar_bg']   = sanitize_hex_color( $input['sidebar_bg']   ?? $d['sidebar_bg']   ) ?: $d['sidebar_bg'];
	// Aankondiging
	$out['announcement_enabled'] = empty( $input['announcement_enabled'] ) ? 0 : 1;
	$out['announcement_text']    = sanitize_textarea_field( $input['announcement_text'] ?? '' );
	$out['announcement_type']    = in_array( $input['announcement_type'] ?? '', array( 'info', 'warning', 'success' ), true )
		? $input['announcement_type'] : 'info';
	// GA4
	$out['ga4_enabled']     = empty( $input['ga4_enabled'] ) ? 0 : 1;
	$out['ga4_property_id'] = preg_replace( '/[^0-9]/', '', $input['ga4_property_id'] ?? '' );
	$ga4_json = trim( $input['ga4_service_account'] ?? '' );
	if ( '' !== $ga4_json ) {
		$decoded = json_decode( $ga4_json, true );
		if ( is_array( $decoded )
			&& isset( $decoded['private_key'], $decoded['client_email'], $decoded['type'] )
			&& 'service_account' === $decoded['type'] ) {
			$out['ga4_service_account'] = $ga4_json;
		}
	} elseif ( isset( $input['ga4_service_account'] ) && '' === $ga4_json ) {
		$out['ga4_service_account'] = '';
	}
	if ( $out['ga4_property_id'] !== $d['ga4_property_id']
		|| $out['ga4_service_account'] !== $d['ga4_service_account'] ) {
		delete_transient( 'dwmcd_ga4_data' );
	}
	// Statistieken & inhoud
	$out['show_stats']           = empty( $input['show_stats'] ) ? 0 : 1;
	$out['show_recent_content']  = empty( $input['show_recent_content'] ) ? 0 : 1;
	$out['recent_content_count'] = max( 1, min( 20, absint( $input['recent_content_count'] ?? 5 ) ) );
	// Support comma-separated CPT slugs (e.g. "post,nieuws,blog")
	$cpt_raw = $input['article_cpt_slug'] ?? 'post';
	$cpt_slugs = array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( ',', $cpt_raw ) ) ) );
	$out['article_cpt_slug'] = $cpt_slugs ? implode( ',', $cpt_slugs ) : 'post';

	// Webshop (WooCommerce)
	$out['is_webshop']              = empty( $input['is_webshop'] )         ? 0 : 1;
	$out['webshop_show_stats']      = empty( $input['webshop_show_stats'] ) ? 0 : 1;
	$out['webshop_show_stock']      = empty( $input['webshop_show_stock'] ) ? 0 : 1;
	$out['webshop_low_stock_limit'] = max( 0, min( 999, absint( $input['webshop_low_stock_limit'] ?? 5 ) ) );
	$out['webshop_stock_count']     = max( 1, min( 20, absint( $input['webshop_stock_count'] ?? 5 ) ) );
	// Wis cache als webshop instellingen wijzigen
	if ( ( $out['is_webshop'] !== ( $d['is_webshop'] ) ) || ( $out['webshop_low_stock_limit'] !== $d['webshop_low_stock_limit'] ) ) {
		delete_transient( 'dwmcd_webshop_stats' );
	}

	// Menu-organizer — read from JSON field submitted by JS on save
	$out['menu_organizer_enabled'] = empty( $input['menu_organizer_enabled'] ) ? 0 : 1;
	if ( ! empty( $input['menu_order_reset'] ) ) {
		$out['menu_order']  = array();
		$out['menu_groups'] = array();
	} else {
		$menu_data_raw = trim( $input['menu_data'] ?? '' );
		if ( '' !== $menu_data_raw ) {
			$menu_data = json_decode( $menu_data_raw, true );
			if ( is_array( $menu_data ) ) {
				// Groups
				$valid_groups = array();
				foreach ( (array) ( $menu_data['groups'] ?? array() ) as $grp ) {
					if ( ! is_array( $grp ) || empty( $grp['id'] ) ) {
						continue;
					}
					$valid_groups[] = array(
						'id'   => sanitize_text_field( $grp['id'] ),
						'name' => sanitize_text_field( $grp['name'] ?? '' ),
					);
				}
				$out['menu_groups'] = $valid_groups;

				// Items
				$valid_order = array();
				foreach ( (array) ( $menu_data['items'] ?? array() ) as $item ) {
					if ( ! is_array( $item ) || empty( $item['slug'] ) ) {
						continue;
					}
					$valid_order[] = array(
						'slug'         => sanitize_text_field( $item['slug'] ),
						'visible'      => empty( $item['visible'] ) ? 0 : 1,
						'custom_label' => sanitize_text_field( $item['custom_label'] ?? '' ),
						'group'        => sanitize_text_field( $item['group'] ?? '' ),
					);
				}
				$out['menu_order'] = $valid_order;
			}
		}
		// If no menu_data submitted, keep existing values (no-op)
	}

	// Support
	$out['show_support']   = empty( $input['show_support'] ) ? 0 : 1;
	$out['support_title']  = sanitize_text_field( $input['support_title']  ?? $d['support_title'] );
	$out['support_text']   = sanitize_textarea_field( $input['support_text'] ?? $d['support_text'] );
	$out['support_button'] = sanitize_text_field( $input['support_button'] ?? $d['support_button'] );
	$out['support_url']    = esc_url_raw( $input['support_url'] ?? '' );
	// Login pagina branding
	$out['login_bg_id']        = absint( $input['login_bg_id'] ?? 0 );
	$out['login_welcome_text'] = sanitize_text_field( $input['login_welcome_text'] ?? '' );
	$out['login_card_style']   = empty( $input['login_card_style'] ) ? 0 : 1;
	// Media & Gebruikers modules
	$out['media_categories_enabled']  = empty( $input['media_categories_enabled'] )  ? 0 : 1;
	$out['media_replacement_enabled'] = empty( $input['media_replacement_enabled'] ) ? 0 : 1;
	$out['local_avatar_enabled']      = empty( $input['local_avatar_enabled'] )      ? 0 : 1;
	// Menu verbergen
	$out['hide_comments'] = empty( $input['hide_comments'] ) ? 0 : 1;
	$out['hide_tools']    = empty( $input['hide_tools'] )    ? 0 : 1;
	// WP branding
	$out['hide_wp_logo']  = empty( $input['hide_wp_logo'] )  ? 0 : 1;
	$out['hide_notices']  = empty( $input['hide_notices'] )  ? 0 : 1;
	$out['custom_footer'] = empty( $input['custom_footer'] ) ? 0 : 1;
	$out['footer_text']   = sanitize_text_field( $input['footer_text'] ?? $d['footer_text'] );
	// Toegang
	$ids                     = array_map( 'absint', (array) ( $input['manager_user_ids'] ?? array() ) );
	$out['manager_user_ids'] = array_values( array_unique( array_filter( $ids ) ) );

	// Snelle acties
	$valid_types     = array_keys( jbwp_type_options() );
	$valid_icons     = array_keys( jbwp_icon_options() );
	$valid_caps      = array( 'read', 'edit_posts', 'edit_pages', 'upload_files', 'moderate_comments', 'list_users', 'edit_theme_options', 'manage_options' );
	$no_target_types = jbwp_types_without_target();
	$actions         = array();
	foreach ( (array) ( $input['quick_actions'] ?? array() ) as $action ) {
		if ( ! is_array( $action ) ) {
			continue;
		}
		$title       = sanitize_text_field( $action['title'] ?? '' );
		$description = sanitize_text_field( $action['description'] ?? '' );
		$target      = sanitize_text_field( $action['target'] ?? '' );
		if ( '' === $title && '' === $description ) {
			continue;
		}
		$type = in_array( $action['action_type'] ?? '', $valid_types, true ) ? $action['action_type'] : 'admin_url';
		$icon = in_array( $action['icon'] ?? '', $valid_icons, true ) ? $action['icon'] : 'admin-links';
		$cap  = in_array( $action['capability'] ?? '', $valid_caps, true ) ? $action['capability'] : 'read';
		if ( in_array( $type, $no_target_types, true ) ) {
			$target = '';
		} elseif ( in_array( $type, array( 'new_post_type', 'list_post_type' ), true ) && '' === $target ) {
			$target = 'post';
		} elseif ( 'external_url' === $type && '' !== $target ) {
			$target = esc_url_raw( $target );
		}
		$actions[] = array(
			'enabled'     => empty( $action['enabled'] ) ? 0 : 1,
			'title'       => $title,
			'description' => $description,
			'icon'        => $icon,
			'action_type' => $type,
			'target'      => $target,
			'capability'  => $cap,
		);
	}
	$out['quick_actions'] = $actions;
	return $out;
}

// ── User capability sync ──────────────────────────────────────────────────────

function jbwp_sync_capabilities( $settings ) {
	if ( ! is_array( $settings ) ) {
		return;
	}
	$ids     = array_map( 'absint', (array) ( $settings['manager_user_ids'] ?? array() ) );
	$current = get_current_user_id();
	if ( $current ) {
		$ids[] = $current;
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );

	foreach ( get_users( array( 'fields' => 'ID' ) ) as $uid ) {
		$uid  = (int) $uid;
		$user = get_user_by( 'id', $uid );
		if ( ! $user instanceof WP_User ) {
			continue;
		}
		if ( in_array( $uid, $ids, true ) ) {
			if ( ! $user->has_cap( DWMCD_CAP ) ) {
				$user->add_cap( DWMCD_CAP );
			}
		} else {
			if ( $user->has_cap( DWMCD_CAP ) ) {
				$user->remove_cap( DWMCD_CAP );
			}
		}
	}
}
add_action( 'update_option_' . DWMCD_OPTION, function ( $old, $new ) { jbwp_sync_capabilities( $new ); }, 10, 2 );
add_action( 'add_option_' . DWMCD_OPTION,    function ( $opt, $val ) { jbwp_sync_capabilities( $val ); }, 10, 2 );

// ── Admin menu ────────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_dashboard_page(
		'Dashboard',
		'Dashboard',
		'read',
		'dwmcd-client-dashboard',
		'jbwp_render_dashboard'
	);

	if ( current_user_can( DWMCD_CAP ) ) {
		add_menu_page(
			'Website beheer',
			'Beheer',
			DWMCD_CAP,
			'dwmcd-settings',
			'jbwp_render_settings',
			'dashicons-admin-customizer',
			58
		);

		add_submenu_page(
			'dwmcd-settings',
			'Instellingen',
			'Instellingen',
			DWMCD_CAP,
			'dwmcd-settings'
		);

		add_submenu_page(
			'dwmcd-settings',
			'Roadmap — Website beheer',
			'Roadmap',
			DWMCD_CAP,
			'dwmcd-roadmap',
			'jbwp_render_roadmap'
		);
	}
}, 20 );

// ── Dashboard page ────────────────────────────────────────────────────────────

function jbwp_render_dashboard() {
	$settings = jbwp_get_settings();
	$user     = wp_get_current_user();
	if ( ! $user instanceof WP_User ) {
		return;
	}
	$actions = jbwp_resolved_actions();

	echo '<div class="wrap dwmcd-custom-dashboard">';

	if ( ! empty( $settings['announcement_enabled'] ) && ! empty( $settings['announcement_text'] ) ) {
		$a_type  = in_array( $settings['announcement_type'], array( 'info', 'warning', 'success' ), true )
			? $settings['announcement_type'] : 'info';
		$a_icons = array( 'info' => 'info', 'warning' => 'warning', 'success' => 'yes-alt' );
		echo '<div class="dwmcd-announcement dwmcd-announcement-' . esc_attr( $a_type ) . '">';
		echo '<span class="dashicons dashicons-' . esc_attr( $a_icons[ $a_type ] ) . '"></span>';
		echo '<p>' . esc_html( $settings['announcement_text'] ) . '</p>';
		echo '</div>';
	}

	echo '<div class="dwmcd-dashboard-hero">';
	echo '<div class="dwmcd-hero-content">';
	echo '<span class="dwmcd-eyebrow">Dashboard</span>';
	echo '<h1>Welkom, ' . esc_html( $user->display_name ) . '</h1>';
	$site_name = get_bloginfo( 'name' );
	if ( $site_name ) {
		echo '<div class="dwmcd-company-label">' . esc_html( $site_name ) . '</div>';
	}
	echo '<p>' . esc_html( $settings['welcome_text'] ) . '</p>';
	echo '</div>';

	if ( ! empty( $settings['logo_id'] ) ) {
		$logo_url = wp_get_attachment_image_url( (int) $settings['logo_id'], 'medium' );
		if ( $logo_url ) {
			$alt = get_bloginfo( 'name' );
			echo '<div class="dwmcd-hero-logo">';
			echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $alt ) . '">';
			echo '</div>';
		}
	}
	echo '</div>';

	if ( ! empty( $settings['show_stats'] ) ) {
		$post_count  = (int) ( wp_count_posts( 'post' )->publish ?? 0 );
		$page_count  = (int) ( wp_count_posts( 'page' )->publish ?? 0 );
		$media_count = array_sum( (array) wp_count_posts( 'attachment' ) );

		echo '<section class="dwmcd-section-block">';
		echo '<div class="dwmcd-stats-grid">';
		echo '<div class="dwmcd-stat-card"><span class="dashicons dashicons-welcome-write-blog"></span><strong>' . number_format_i18n( $post_count ) . '</strong><small>Berichten</small></div>';
		echo '<div class="dwmcd-stat-card"><span class="dashicons dashicons-admin-page"></span><strong>' . number_format_i18n( $page_count ) . '</strong><small>Pagina\'s</small></div>';
		echo '<div class="dwmcd-stat-card"><span class="dashicons dashicons-format-image"></span><strong>' . number_format_i18n( $media_count ) . '</strong><small>Mediabestanden</small></div>';
		echo '</div>';
		echo '</section>';
	}

	// GA4 dashboard widget — tijdelijk uitgeschakeld (Coming Soon)
	// if ( ! empty( $settings['ga4_enabled'] ) && ! empty( $settings['ga4_property_id'] ) ) { ... }

	// Webshop widgets — alleen tonen als is_webshop aan staat én WooCommerce actief is
	if ( jbwp_webshop_enabled() ) {
		if ( ! empty( $settings['webshop_show_stats'] ) ) {
			$ws_widget = jbwp_webshop_render_stats_widget();
			if ( $ws_widget ) {
				echo '<section class="dwmcd-section-block">';
				echo '<h2>Webshop statistieken</h2>';
				echo '<div class="dwmcd-ga4-card">';
				echo $ws_widget;
				echo '</div>';
				echo '</section>';
			}
		}
		if ( ! empty( $settings['webshop_show_stock'] ) ) {
			$stock_widget = jbwp_webshop_render_stock_widget();
			if ( $stock_widget ) {
				echo '<section class="dwmcd-section-block">';
				echo '<h2>Voorraad</h2>';
				echo '<div class="dwmcd-recent-box">';
				echo $stock_widget;
				echo '</div>';
				echo '</section>';
			}
		}
	}

	if ( ! empty( $actions ) ) {
		echo '<section class="dwmcd-section-block">';
		echo '<h2>Snelle acties</h2>';
		echo '<div class="dwmcd-actions-grid">';
		foreach ( $actions as $action ) {
			$new_tab  = ! empty( $action['new_tab'] );
			$tab_attr = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
			echo '<a class="dwmcd-action-card" href="' . esc_url( $action['url'] ) . '"' . $tab_attr . '>';
			echo '<span class="dashicons dashicons-' . esc_attr( $action['icon'] ) . '"></span>';
			echo '<strong>' . esc_html( $action['title'] ) . '</strong>';
			if ( ! empty( $action['description'] ) ) {
				echo '<small>' . esc_html( $action['description'] ) . '</small>';
			}
			echo '</a>';
		}
		echo '</div>';
		echo '</section>';
	}

	if ( ! empty( $settings['show_recent_content'] ) ) {
		$cpt_raw = $settings['article_cpt_slug'] ?? 'post';
		$cpts    = array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( ',', $cpt_raw ) ) ) );
		if ( empty( $cpts ) ) {
			$cpts = array( 'post' );
		}
		$types = array_unique( array_merge( $cpts, array( 'page' ) ) );
		$recent = get_posts( array(
			'post_type'      => $types,
			'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
			'posts_per_page' => max( 1, (int) $settings['recent_content_count'] ),
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );
		echo '<section class="dwmcd-section-block">';
		echo '<h2>Recent bewerkt</h2>';
		echo '<div class="dwmcd-recent-box">';
		if ( ! empty( $recent ) ) {
			$status_map = array(
				'publish' => 'Gepubliceerd',
				'draft'   => 'Concept',
				'pending' => 'In beoordeling',
				'future'  => 'Gepland',
			);
			echo '<ul class="dwmcd-recent-list">';
			foreach ( $recent as $item ) {
				$type_obj     = get_post_type_object( $item->post_type );
				$type_label   = $type_obj ? $type_obj->labels->singular_name : $item->post_type;
				$title        = get_the_title( $item->ID ) ?: '(Geen titel)';
				$status_label = $status_map[ $item->post_status ] ?? $item->post_status;
				echo '<li>';
				echo '<div class="dwmcd-recent-info">';
				echo '<a href="' . esc_url( (string) get_edit_post_link( $item->ID ) ) . '">' . esc_html( $title ) . '</a>';
				echo '<small>' . esc_html( $type_label ) . ' &bull; ' . esc_html( $status_label ) . ' &bull; gewijzigd ' . esc_html( get_the_modified_date( 'd M Y', $item->ID ) ) . '</small>';
				echo '</div>';
				echo '<a class="dwmcd-edit-btn" href="' . esc_url( (string) get_edit_post_link( $item->ID ) ) . '">Bewerken</a>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p class="dwmcd-empty-msg">Er is nog geen inhoud gevonden.</p>';
		}
		echo '</div>';
		echo '</section>';
	}

	if ( ! empty( $settings['show_support'] ) ) {
		echo '<section class="dwmcd-section-block">';
		echo '<div class="dwmcd-support-box">';
		echo '<div class="dwmcd-support-inner">';
		echo '<span class="dashicons dashicons-sos"></span>';
		echo '<div>';
		echo '<strong>' . esc_html( $settings['support_title'] ) . '</strong>';
		echo '<p>' . esc_html( $settings['support_text'] ) . '</p>';
		echo '</div>';
		echo '</div>';
		if ( ! empty( $settings['support_url'] ) ) {
			echo '<a class="button button-primary" href="' . esc_url( $settings['support_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $settings['support_button'] ) . '</a>';
		}
		echo '</div>';
		echo '</section>';
	}

	echo '</div>';
}

// ── Action card (settings) ────────────────────────────────────────────────────

function jbwp_render_action_card( $index, $action, $settings ) {
	$icons       = jbwp_icon_options();
	$types       = jbwp_type_options();
	$hints       = jbwp_type_hints();
	$no_target   = jbwp_types_without_target();
	$url         = jbwp_action_url( $action, $settings );
	$curr_icon   = $action['icon']        ?? 'admin-links';
	$curr_type   = $action['action_type'] ?? 'admin_url';
	$hide_target = in_array( $curr_type, $no_target, true );
	$is_tpl      = ( '__INDEX__' === (string) $index );
	?>
	<div class="dwmcd-action-admin-card<?php echo $is_tpl ? ' dwmcd-action-open' : ''; ?>" data-action-card>
		<div class="dwmcd-action-header" data-action-toggle>
			<div class="dwmcd-action-header-left">
				<span class="dwmcd-action-icon-preview dashicons dashicons-<?php echo esc_attr( $curr_icon ); ?>"></span>
				<div class="dwmcd-action-header-text">
					<strong class="dwmcd-action-title-preview"><?php echo esc_html( $action['title'] ?: 'Nieuwe knop' ); ?></strong>
					<span class="dwmcd-action-desc-preview"><?php echo esc_html( $action['description'] ?: '—' ); ?></span>
				</div>
				<?php if ( empty( $action['enabled'] ) && ! $is_tpl ) : ?>
					<span class="dwmcd-badge-inactive">Inactief</span>
				<?php endif; ?>
			</div>
			<div class="dwmcd-action-header-right">
				<button type="button" class="dwmcd-btn-icon" data-move="up"   title="Omhoog"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
				<button type="button" class="dwmcd-btn-icon" data-move="down" title="Omlaag"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
				<span class="dwmcd-chevron dashicons dashicons-arrow-down-alt2"></span>
			</div>
		</div>

		<div class="dwmcd-action-body">
			<div class="dwmcd-action-body-top">
				<label class="dwmcd-inline-check">
					<input type="checkbox"
						name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][enabled]"
						value="1" <?php checked( ! empty( $action['enabled'] ) ); ?>>
					Zichtbaar op dashboard
				</label>
				<button type="button" class="button-link-delete" data-remove-action>
					<span class="dashicons dashicons-trash"></span> Verwijderen
				</button>
			</div>
			<div class="dwmcd-grid two">
				<div class="dwmcd-field">
					<label>Naam</label>
					<input data-live-title type="text"
						name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][title]"
						value="<?php echo esc_attr( $action['title'] ?? '' ); ?>"
						placeholder="bijv. Nieuw bericht">
				</div>
				<div class="dwmcd-field">
					<label>Omschrijving <span class="dwmcd-optional">(optioneel)</span></label>
					<input data-live-description type="text"
						name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][description]"
						value="<?php echo esc_attr( $action['description'] ?? '' ); ?>"
						placeholder="bijv. Schrijf een nieuw bericht">
				</div>
			</div>
			<div class="dwmcd-grid two">
				<div class="dwmcd-field">
					<label>Icoon</label>
					<div class="dwmcd-icon-select-wrap">
						<span class="dwmcd-icon-inline-preview dashicons dashicons-<?php echo esc_attr( $curr_icon ); ?>"></span>
						<select data-live-icon name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][icon]">
							<?php foreach ( $icons as $k => $lbl ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $curr_icon, $k ); ?>>
									<?php echo esc_html( $lbl ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="dwmcd-field">
					<label>Actie</label>
					<select data-action-type name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][action_type]">
						<optgroup label="Post types (berichten, pagina's, CPT's)">
							<option value="new_post_type"  <?php selected( $curr_type, 'new_post_type' ); ?>>Nieuw item aanmaken</option>
							<option value="list_post_type" <?php selected( $curr_type, 'list_post_type' ); ?>>Overzicht openen</option>
						</optgroup>
						<optgroup label="Media">
							<option value="media_upload"  <?php selected( $curr_type, 'media_upload' ); ?>>Media uploaden</option>
							<option value="media_library" <?php selected( $curr_type, 'media_library' ); ?>>Mediabibliotheek openen</option>
						</optgroup>
						<optgroup label="WordPress / plugin pagina">
							<option value="admin_url"    <?php selected( $curr_type, 'admin_url' ); ?>>Admin pagina (reacties, formulieren, WooCommerce, etc.)</option>
						</optgroup>
						<optgroup label="Links">
							<option value="external_url" <?php selected( $curr_type, 'external_url' ); ?>>Externe link (nieuw tabblad)</option>
							<option value="site_url"     <?php selected( $curr_type, 'site_url' ); ?>>Website bezoeken (nieuw tabblad)</option>
						</optgroup>
					</select>
					<small class="dwmcd-help dwmcd-type-hint" data-type-hint><?php echo esc_html( $hints[ $curr_type ] ?? '' ); ?></small>
				</div>
			</div>
			<div class="dwmcd-grid two">
				<?php $placeholders = jbwp_type_placeholders(); ?>
				<div class="dwmcd-field dwmcd-target-field"<?php echo $hide_target ? ' style="display:none"' : ''; ?>>
					<label>Doel / URL</label>
					<input data-action-target type="text"
						name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][target]"
						value="<?php echo esc_attr( $action['target'] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $placeholders[ $curr_type ] ?? '' ); ?>">
				</div>
				<div class="dwmcd-field">
					<label>Zichtbaar voor</label>
					<select name="dwmcd_settings[quick_actions][<?php echo esc_attr( $index ); ?>][capability]">
						<?php
						$caps = array(
							'read'               => 'Iedereen (ook klant)',
							'edit_posts'         => 'Kan berichten bewerken',
							'edit_pages'         => "Kan pagina's bewerken",
							'upload_files'       => 'Kan media uploaden',
							'moderate_comments'  => 'Kan reacties modereren',
							'list_users'         => 'Kan gebruikers zien',
							'edit_theme_options' => "Kan menu's/thema bewerken",
							'manage_options'     => 'Alleen beheerder',
						);
						foreach ( $caps as $k => $lbl ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $action['capability'] ?? 'read', $k ); ?>>
								<?php echo esc_html( $lbl ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="dwmcd-url-preview">
				<span class="dashicons dashicons-admin-links"></span>
				<code data-url-preview><?php echo esc_html( $url ?: 'Nog niet compleet' ); ?></code>
			</div>
		</div>
	</div>
	<?php
}

// ── Settings page ─────────────────────────────────────────────────────────────

function jbwp_render_settings() {
	if ( ! current_user_can( DWMCD_CAP ) ) {
		wp_die( esc_html__( 'U heeft geen toegang tot deze pagina.' ) );
	}
	// Show save confirmation
	if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
		echo '<div class="dwmcd-save-notice" id="dwmcd-save-notice">'
			. '<span class="dashicons dashicons-yes-alt"></span>'
			. '<span>Instellingen opgeslagen!</span>'
			. '</div>';
	}
	$settings   = jbwp_get_settings();
	$users      = get_users( array( 'orderby' => 'display_name', 'order' => 'ASC' ) );
	$saved_ids  = array_map( 'absint', (array) $settings['manager_user_ids'] );
	$logo_url   = '';
	if ( ! empty( $settings['logo_id'] ) ) {
		$logo_url = wp_get_attachment_image_url( (int) $settings['logo_id'], 'medium' ) ?: '';
	}
	$icon_url = '';
	if ( ! empty( $settings['site_icon_id'] ) ) {
		$icon_url = wp_get_attachment_image_url( (int) $settings['site_icon_id'], 'thumbnail' ) ?: '';
	}
	$menu_config = jbwp_build_menu_config( $settings );
	?>
	<div class="wrap dwmcd-settings-wrap">
		<div class="dwmcd-settings-shell">

			<div class="dwmcd-settings-header">
				<h1>Website beheer</h1>
				<p>Beheer het klantdashboard &mdash; versie <?php echo esc_html( DWMCD_VERSION ); ?></p>
			</div>

			<div class="dwmcd-tabs" role="tablist" aria-label="Instellingentabs">
				<button type="button" class="dwmcd-tab-btn" data-tab="dashboard" role="tab" aria-selected="false" aria-controls="dwmcd-panel-dashboard" id="dwmcd-tab-dashboard"><span class="dashicons dashicons-dashboard"></span> Dashboard</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="actions" role="tab" aria-selected="false" aria-controls="dwmcd-panel-actions" id="dwmcd-tab-actions"><span class="dashicons dashicons-grid-view"></span> Snelle acties</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="menu" role="tab" aria-selected="false" aria-controls="dwmcd-panel-menu" id="dwmcd-tab-menu"><span class="dashicons dashicons-menu"></span> Menu</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="analytics" role="tab" aria-selected="false" aria-controls="dwmcd-panel-analytics" id="dwmcd-tab-analytics"><span class="dashicons dashicons-chart-area"></span> Analytics</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="webshop" role="tab" aria-selected="false" aria-controls="dwmcd-panel-webshop" id="dwmcd-tab-webshop"><span class="dashicons dashicons-cart"></span> Webshop</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="media" role="tab" aria-selected="false" aria-controls="dwmcd-panel-media" id="dwmcd-tab-media"><span class="dashicons dashicons-plugins-checked"></span> Functies</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="settings" role="tab" aria-selected="false" aria-controls="dwmcd-panel-settings" id="dwmcd-tab-settings"><span class="dashicons dashicons-admin-settings"></span> Instellingen</button>
				<button type="button" class="dwmcd-tab-btn" data-tab="access" role="tab" aria-selected="false" aria-controls="dwmcd-panel-access" id="dwmcd-tab-access"><span class="dashicons dashicons-admin-users"></span> Toegang</button>
			</div>

			<form method="post" action="options.php" id="dwmcd-settings-form">
				<?php settings_fields( 'dwmcd_group' ); ?>

				<!-- ══ TAB: DASHBOARD ════════════════════════════════════════ -->
				<div data-tab-panel="dashboard" role="tabpanel" id="dwmcd-panel-dashboard" aria-labelledby="dwmcd-tab-dashboard">

					<div class="dwmcd-card">
						<h2>Welkomst</h2>
						<div class="dwmcd-field">
							<label>Welkomstekst</label>
							<textarea name="dwmcd_settings[welcome_text]" rows="3"><?php echo esc_textarea( $settings['welcome_text'] ); ?></textarea>
							<small class="dwmcd-help">De naam van de website (<?php echo esc_html( get_bloginfo( 'name' ) ); ?>) wordt automatisch als badge getoond.</small>
						</div>
					</div>

					<!-- Logo -->
					<div class="dwmcd-card">
						<h2>Logo</h2>
						<p class="dwmcd-muted" style="margin-bottom:16px">Verschijnt rechts in het welkomstblok. Aanbevolen: transparante PNG, max. 400&nbsp;px breed.</p>
						<div class="dwmcd-logo-uploader">
							<div class="dwmcd-logo-preview<?php echo $logo_url ? '' : ' dwmcd-logo-empty'; ?>" id="dwmcd-logo-preview">
								<?php if ( $logo_url ) : ?>
									<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo preview">
								<?php else : ?>
									<span class="dashicons dashicons-format-image"></span>
									<span>Geen logo geselecteerd</span>
								<?php endif; ?>
							</div>
							<div class="dwmcd-logo-actions">
								<input type="hidden" name="dwmcd_settings[logo_id]" id="dwmcd-logo-id" value="<?php echo esc_attr( $settings['logo_id'] ); ?>">
								<button type="button" class="button button-secondary" id="dwmcd-logo-select">
									<span class="dashicons dashicons-upload"></span> Logo kiezen
								</button>
								<button type="button" class="button-link-delete<?php echo empty( $settings['logo_id'] ) ? ' hidden' : ''; ?>" id="dwmcd-logo-remove">
									Verwijderen
								</button>
							</div>
						</div>
					</div>

					<!-- Site-icoon -->
					<div class="dwmcd-card">
						<h2>Site-icoon</h2>
						<p class="dwmcd-muted" style="margin-bottom:16px">Vervangt het WordPress-logo in de adminbar, de loginpagina en de browsertab. Gebruik een vierkant PNG, minimaal 64&times;64&nbsp;px.</p>
						<div class="dwmcd-logo-uploader">
							<div class="dwmcd-logo-preview dwmcd-icon-preview<?php echo $icon_url ? '' : ' dwmcd-logo-empty'; ?>" id="dwmcd-icon-preview">
								<?php if ( $icon_url ) : ?>
									<img src="<?php echo esc_url( $icon_url ); ?>" alt="Icoon preview">
								<?php else : ?>
									<span class="dashicons dashicons-wordpress"></span>
									<span>Geen icoon geselecteerd</span>
								<?php endif; ?>
							</div>
							<div class="dwmcd-logo-actions">
								<input type="hidden" name="dwmcd_settings[site_icon_id]" id="dwmcd-icon-id" value="<?php echo esc_attr( $settings['site_icon_id'] ); ?>">
								<button type="button" class="button button-secondary" id="dwmcd-icon-select">
									<span class="dashicons dashicons-upload"></span> Icoon kiezen
								</button>
								<button type="button" class="button-link-delete<?php echo empty( $settings['site_icon_id'] ) ? ' hidden' : ''; ?>" id="dwmcd-icon-remove">
									Verwijderen
								</button>
							</div>
						</div>
					</div>

					<!-- Huisstijl kleuren -->
					<div class="dwmcd-card">
						<h2>Huisstijl kleuren</h2>
						<p class="dwmcd-muted" style="margin-bottom:16px">Pas de interface aan op de huisstijl van de klant. De preview wordt direct bijgewerkt.</p>
						<div class="dwmcd-grid two">
							<div class="dwmcd-field">
								<label>Accentkleur <span class="dwmcd-optional">(knoppen, iconen, links)</span></label>
								<div class="dwmcd-color-field">
									<input type="color" id="dwmcd-accent-color" name="dwmcd_settings[accent_color]" value="<?php echo esc_attr( $settings['accent_color'] ); ?>">
									<input type="text"  id="dwmcd-accent-hex"   class="dwmcd-hex-input" value="<?php echo esc_attr( $settings['accent_color'] ); ?>" maxlength="7" placeholder="#2952ff">
								</div>
							</div>
							<div class="dwmcd-field">
								<label>Zijbalk achtergrond <span class="dwmcd-optional">(menu &amp; adminbar)</span></label>
								<div class="dwmcd-color-field">
									<input type="color" id="dwmcd-sidebar-bg"  name="dwmcd_settings[sidebar_bg]" value="<?php echo esc_attr( $settings['sidebar_bg'] ); ?>">
									<input type="text"  id="dwmcd-sidebar-hex" class="dwmcd-hex-input" value="<?php echo esc_attr( $settings['sidebar_bg'] ); ?>" maxlength="7" placeholder="#ffffff">
								</div>
							</div>
						</div>
						<div style="margin-top:12px">
							<button type="button" class="button button-secondary" id="dwmcd-reset-colors">Standaard kleuren herstellen</button>
						</div>
					</div>

					<!-- Aankondiging -->
					<div class="dwmcd-card">
						<h2>Aankondiging</h2>
						<p class="dwmcd-muted" style="margin-bottom:16px">Toon een tijdelijk bericht bovenaan het klantdashboard. Handig voor updates, onderhoud of andere mededelingen.</p>
						<div class="dwmcd-switches" style="margin-bottom:14px">
							<label><input type="checkbox" name="dwmcd_settings[announcement_enabled]" value="1" <?php checked( ! empty( $settings['announcement_enabled'] ) ); ?>> Aankondiging tonen</label>
						</div>
						<div class="dwmcd-grid two">
							<div class="dwmcd-field">
								<label>Type</label>
								<select name="dwmcd_settings[announcement_type]">
									<option value="info"    <?php selected( $settings['announcement_type'], 'info' ); ?>>ℹ Informatief (blauw)</option>
									<option value="warning" <?php selected( $settings['announcement_type'], 'warning' ); ?>>⚠ Waarschuwing (oranje)</option>
									<option value="success" <?php selected( $settings['announcement_type'], 'success' ); ?>>✓ Positief (groen)</option>
								</select>
							</div>
							<div class="dwmcd-field" style="grid-column:1/-1">
								<label>Tekst</label>
								<textarea name="dwmcd_settings[announcement_text]" rows="2" placeholder="bijv. Wij zijn momenteel bezig met het bijwerken van uw website."><?php echo esc_textarea( $settings['announcement_text'] ); ?></textarea>
							</div>
						</div>
					</div>

					<!-- Support blok -->
					<div class="dwmcd-card">
						<h2>Support blok</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Contactblok onderaan het klantdashboard.</p>
						<div class="dwmcd-switches" style="margin-bottom:14px">
							<label><input type="checkbox" name="dwmcd_settings[show_support]" value="1" <?php checked( ! empty( $settings['show_support'] ) ); ?>> Support blok tonen</label>
						</div>
						<div class="dwmcd-grid three">
							<div class="dwmcd-field"><label>Titel</label><input type="text" name="dwmcd_settings[support_title]" value="<?php echo esc_attr( $settings['support_title'] ); ?>"></div>
							<div class="dwmcd-field"><label>Knoptekst</label><input type="text" name="dwmcd_settings[support_button]" value="<?php echo esc_attr( $settings['support_button'] ); ?>"></div>
							<div class="dwmcd-field"><label>URL</label><input type="url" name="dwmcd_settings[support_url]" value="<?php echo esc_attr( $settings['support_url'] ); ?>" placeholder="https://..."></div>
						</div>
						<div class="dwmcd-field" style="margin-top:12px">
							<label>Tekst</label>
							<textarea name="dwmcd_settings[support_text]" rows="2"><?php echo esc_textarea( $settings['support_text'] ); ?></textarea>
						</div>
					</div>

				</div><!-- /dashboard panel -->

				<!-- ══ TAB: SNELLE ACTIES ════════════════════════════════════ -->
				<div data-tab-panel="actions" class="hidden" role="tabpanel" id="dwmcd-panel-actions" aria-labelledby="dwmcd-tab-actions">
					<div class="dwmcd-card">
						<div class="dwmcd-card-header-row">
							<div>
								<h2>Snelle acties</h2>
								<p class="dwmcd-muted">Knoppen op het klantdashboard. Klik om te bewerken, pijlen om te herordenen.</p>
							</div>
							<div style="display:flex;gap:8px;align-items:center">
								<select id="dwmcd-preset-select" class="dwmcd-preset-select">
									<option value="">Voorbeeld toevoegen...</option>
									<optgroup label="Inhoud">
										<option value="new_post">Nieuw bericht</option>
										<option value="list_pages">Pagina's beheren</option>
									</optgroup>
									<optgroup label="Media">
										<option value="media_upload">Media uploaden</option>
									</optgroup>
									<optgroup label="WordPress">
										<option value="comments">Reacties beheren</option>
										<option value="users">Gebruikers</option>
										<option value="menus">Menu's bewerken</option>
										<option value="customizer">Customizer</option>
									</optgroup>
									<optgroup label="Formulieren">
										<option value="forms_gf">Gravity Forms inzendingen</option>
										<option value="forms_wpforms">WPForms inzendingen</option>
										<option value="forms_cf7">Contact Form 7</option>
									</optgroup>
									<optgroup label="WooCommerce">
										<option value="woo_new_product">Nieuw product</option>
										<option value="woo_products">Producten beheren</option>
										<option value="woo_orders">Bestellingen</option>
										<option value="woo_customers">Klanten</option>
										<option value="woo_coupons">Kortingsbonnen</option>
										<option value="woo_reports">Webshop rapporten</option>
										<option value="woo_reviews">Productreviews</option>
										<option value="woo_settings">WooCommerce instellingen</option>
									</optgroup>
									<optgroup label="Links">
										<option value="visit_site">Website bekijken</option>
									</optgroup>
								</select>
								<button type="button" class="button button-primary" id="dwmcd-add-action">
									<span class="dashicons dashicons-plus-alt2"></span> Lege knop
								</button>
							</div>
						</div>
						<div class="dwmcd-actions-empty<?php echo empty( $settings['quick_actions'] ) ? '' : ' hidden'; ?>" id="dwmcd-actions-empty">
							<span class="dashicons dashicons-grid-view"></span>
							<p>Nog geen snelle acties. Voeg een eerste knop toe.</p>
						</div>
						<div class="dwmcd-actions-list" id="dwmcd-actions-grid">
							<?php
							$da = array( 'enabled' => 1, 'title' => '', 'description' => '', 'icon' => 'admin-links', 'action_type' => 'admin_url', 'target' => '', 'capability' => 'read' );
							foreach ( (array) $settings['quick_actions'] as $i => $action ) {
								jbwp_render_action_card( $i, wp_parse_args( $action, $da ), $settings );
							}
							?>
						</div>
					</div>
				</div><!-- /actions panel -->

				<!-- ══ TAB: MENU ══════════════════════════════════════════════ -->
				<div data-tab-panel="menu" class="hidden" role="tabpanel" id="dwmcd-panel-menu" aria-labelledby="dwmcd-tab-menu">
					<div class="dwmcd-card">
						<div class="dwmcd-card-header-row">
							<div>
								<h2>Menu-organizer</h2>
								<p class="dwmcd-muted">Maak groepen aan en sleep menu-items erin. De volgorde van de groepen bepaalt de volgorde in de zijbalk.</p>
							</div>
							<button type="button" class="dwmcd-btn-reset" id="dwmcd-menu-reset">
								<span class="dashicons dashicons-undo"></span> Reset naar standaard
							</button>
						</div>
						<div class="dwmcd-switches" style="margin-bottom:20px">
							<label><input type="checkbox" name="dwmcd_settings[menu_organizer_enabled]" value="1" <?php checked( ! empty( $settings['menu_organizer_enabled'] ) ); ?>> Menu-organizer inschakelen</label>
						</div>

						<!-- Hidden fields: populated by JS on submit -->
						<input type="hidden" name="dwmcd_settings[menu_order_reset]" id="dwmcd-menu-order-reset" value="0">
						<input type="hidden" name="dwmcd_settings[menu_data]" id="dwmcd-menu-data" value="">

						<!-- Groups editor -->
						<div id="dwmcd-groups-area">
							<?php
							$groups = $menu_config['groups'];
							$items  = $menu_config['items'];

							// Build per-group item lists
							$grouped   = array(); // group_id => [items]
							$ungrouped = array();
							foreach ( $items as $item ) {
								$gid = $item['group'] ?? '';
								if ( $gid ) {
									$grouped[ $gid ][] = $item;
								} else {
									$ungrouped[] = $item;
								}
							}

							// Render user-defined groups
							foreach ( $groups as $grp ) :
								$gid      = $grp['id'];
								$gname    = $grp['name'];
								$g_items  = $grouped[ $gid ] ?? array();
							?>
							<div class="dwmcd-menu-group" data-group-id="<?php echo esc_attr( $gid ); ?>">
								<div class="dwmcd-group-header" draggable="true">
									<span class="dwmcd-group-drag dashicons dashicons-move" title="Groep verslepen"></span>
									<span class="dashicons dashicons-category" style="color:var(--dwmcd-accent);font-size:16px;width:16px;height:16px"></span>
									<input type="text" class="dwmcd-group-name" value="<?php echo esc_attr( $gname ); ?>" placeholder="Groepsnaam...">
									<button type="button" class="dwmcd-btn-icon dwmcd-group-delete" title="Groep verwijderen" aria-label="Groep verwijderen"><span class="dashicons dashicons-trash"></span></button>
								</div>
								<div class="dwmcd-group-items" data-group-drop>
									<?php foreach ( $g_items as $item ) : ?>
										<?php jbwp_render_menu_chip( $item ); ?>
									<?php endforeach; ?>
									<div class="dwmcd-drop-placeholder"></div>
								</div>
							</div>
							<?php endforeach; ?>

							<!-- "Ungrouped" bucket — always last, no delete button -->
							<div class="dwmcd-menu-group dwmcd-group-ungrouped" data-group-id="">
								<div class="dwmcd-group-header">
									<span class="dashicons dashicons-menu" style="color:var(--dwmcd-muted);font-size:16px;width:16px;height:16px"></span>
									<span class="dwmcd-group-name-static">Overig (geen groep)</span>
								</div>
								<div class="dwmcd-group-items" data-group-drop>
									<?php foreach ( $ungrouped as $item ) : ?>
										<?php jbwp_render_menu_chip( $item ); ?>
									<?php endforeach; ?>
									<div class="dwmcd-drop-placeholder"></div>
								</div>
							</div>
						</div><!-- /groups-area -->

						<div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap">
							<button type="button" class="button button-secondary" id="dwmcd-add-group">
								<span class="dashicons dashicons-plus-alt2"></span> Nieuwe groep toevoegen
							</button>
							<button type="button" class="button button-secondary" id="dwmcd-ai-sort" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:#6366f1">
								<span class="dashicons dashicons-admin-generic" style="font-size:14px;width:14px;height:14px;margin-top:3px"></span> AI sorteren
							</button>
						</div>

						<div class="dwmcd-info-box" style="margin-top:16px">
							<span class="dashicons dashicons-info"></span>
							<div>
								<p>Sleep items naar een groep. Volgorde en labels gelden voor iedereen. Verborgen items zijn alleen onzichtbaar voor gebruikers <strong>zonder beheerderstoegang</strong>.</p>
							</div>
						</div>
					</div>
				</div><!-- /menu panel -->

				<!-- ══ TAB: ANALYTICS ════════════════════════════════════════ -->
				<div data-tab-panel="analytics" class="hidden" role="tabpanel" id="dwmcd-panel-analytics" aria-labelledby="dwmcd-tab-analytics">
					<div class="dwmcd-card" style="text-align:center;padding:48px 24px">
						<span class="dashicons dashicons-chart-area" style="font-size:48px;width:48px;height:48px;color:var(--dwmcd-accent);opacity:.6;margin-bottom:16px"></span>
						<h2 style="margin:0 0 8px">Analytics — Coming Soon</h2>
						<p class="dwmcd-muted" style="max-width:460px;margin:0 auto;line-height:1.6">De Google Analytics (GA4) integratie wordt momenteel verbeterd en is tijdelijk uitgeschakeld. In een toekomstige versie kun je hier bezoekersstatistieken direct op het dashboard bekijken.</p>
					</div>
				</div><!-- /analytics panel -->

				<!-- ══ TAB: WEBSHOP ═════════════════════════════════════════ -->
				<div data-tab-panel="webshop" class="hidden" role="tabpanel" id="dwmcd-panel-webshop" aria-labelledby="dwmcd-tab-webshop">

					<div class="dwmcd-card">
						<h2>Webshop modus</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Schakel webshop-functies in als je een WooCommerce webshop beheert. Dit voegt webshop-specifieke widgets toe aan het dashboard zonder andere functies te beïnvloeden.</p>

						<?php if ( ! jbwp_woo_active() ) : ?>
							<div class="dwmcd-announcement dwmcd-announcement-warning" style="margin-bottom:14px">
								<span class="dashicons dashicons-warning"></span>
								<p>WooCommerce is niet geactiveerd op deze website. Activeer eerst WooCommerce om de webshop-functies te kunnen gebruiken.</p>
							</div>
						<?php else : ?>
							<div class="dwmcd-announcement dwmcd-announcement-success" style="margin-bottom:14px">
								<span class="dashicons dashicons-yes-alt"></span>
								<p>WooCommerce is actief en gedetecteerd. Je kunt webshop-functies inschakelen.</p>
							</div>
						<?php endif; ?>

						<div class="dwmcd-switches">
							<label>
								<input type="checkbox" name="dwmcd_settings[is_webshop]" value="1" <?php checked( ! empty( $settings['is_webshop'] ) ); ?>>
								<strong>Dit is een webshop</strong> &mdash; activeer alle webshop-functies
							</label>
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>Webshop widgets op het dashboard</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Kies welke webshop-widgets getoond worden op het klantdashboard. Werkt alleen als &ldquo;Dit is een webshop&rdquo; is ingeschakeld.</p>

						<div class="dwmcd-switches">
							<label>
								<input type="checkbox" name="dwmcd_settings[webshop_show_stats]" value="1" <?php checked( ! empty( $settings['webshop_show_stats'] ) ); ?>>
								Webshop statistieken tonen (omzet, bestellingen, gemiddelde orderwaarde)
							</label>
							<label>
								<input type="checkbox" name="dwmcd_settings[webshop_show_stock]" value="1" <?php checked( ! empty( $settings['webshop_show_stock'] ) ); ?>>
								Voorraad-alerts tonen (lage voorraad en uitverkochte producten)
							</label>
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>Voorraad instellingen</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Stel in vanaf welke voorraadhoeveelheid een waarschuwing getoond wordt.</p>

						<div class="dwmcd-grid two">
							<div class="dwmcd-field">
								<label>Drempel lage voorraad</label>
								<input type="number" min="0" max="999"
									name="dwmcd_settings[webshop_low_stock_limit]"
									value="<?php echo esc_attr( $settings['webshop_low_stock_limit'] ?? 5 ); ?>">
								<small class="dwmcd-help">Producten met deze voorraad of minder worden als &ldquo;laag&rdquo; getoond.</small>
							</div>
							<div class="dwmcd-field">
								<label>Aantal items in lijst</label>
								<input type="number" min="1" max="20"
									name="dwmcd_settings[webshop_stock_count]"
									value="<?php echo esc_attr( $settings['webshop_stock_count'] ?? 5 ); ?>">
								<small class="dwmcd-help">Hoeveel producten getoond worden in de lijst op het dashboard.</small>
							</div>
						</div>
					</div>

					<div class="dwmcd-info-box">
						<span class="dashicons dashicons-info"></span>
						<p>Tip: bij het tabblad <strong>Snelle acties</strong> vind je voorbeeld-knoppen voor WooCommerce zoals &ldquo;Nieuw product&rdquo;, &ldquo;Bestellingen&rdquo;, &ldquo;Klanten&rdquo; en meer onder de optgroup &ldquo;WooCommerce&rdquo;.</p>
					</div>

				</div><!-- /webshop panel -->

				<!-- ══ TAB: INSTELLINGEN ════════════════════════════════════ -->
				<!-- ══ TAB: MEDIA & GEBRUIKERS ═══════════════════════════════ -->
				<div data-tab-panel="media" class="hidden" role="tabpanel" id="dwmcd-panel-media" aria-labelledby="dwmcd-tab-media">

					<p class="dwmcd-muted" style="margin-bottom:20px">Schakel extra functies in of uit. Elke functie werkt onafhankelijk — activeer alleen wat je nodig hebt.</p>

					<?php
					$features = array(
						array(
							'key'         => 'media_categories_enabled',
							'icon'        => 'category',
							'title'       => 'Mediamappen',
							'description' => 'Organiseer de mediabibliotheek met mappen. Sidebar met drag-and-drop, inline folder beheer en filters.',
							'settings'    => false,
						),
						array(
							'key'         => 'media_replacement_enabled',
							'icon'        => 'update',
							'title'       => 'Media Replacement',
							'description' => 'Vervang mediabestanden met behoud van de ID, URL en alle koppelingen.',
							'settings'    => false,
						),
						array(
							'key'         => 'local_avatar_enabled',
							'icon'        => 'admin-users',
							'title'       => 'Lokale Avatar',
							'description' => 'Gebruik een afbeelding uit de mediabibliotheek als profielfoto — geen Gravatar nodig.',
							'settings'    => false,
						),
						array(
							'key'         => 'login_card_style',
							'icon'        => 'lock',
							'title'       => 'Custom Login Page',
							'description' => 'Pas de WordPress login pagina aan met achtergrondafbeelding, welkomsttekst en huisstijl-kleuren.',
							'settings'    => 'login',
						),
					);
					foreach ( $features as $f ) :
						$enabled = ! empty( $settings[ $f['key'] ] );
					?>
					<div class="dwmcd-card dwmcd-feature-card<?php echo $enabled ? ' dwmcd-feature-active' : ''; ?>">
						<div class="dwmcd-feature-header">
							<div class="dwmcd-feature-info">
								<span class="dashicons dashicons-<?php echo esc_attr( $f['icon'] ); ?> dwmcd-feature-icon"></span>
								<div>
									<h3 class="dwmcd-feature-title"><?php echo esc_html( $f['title'] ); ?></h3>
									<p class="dwmcd-feature-desc"><?php echo esc_html( $f['description'] ); ?></p>
								</div>
							</div>
							<div class="dwmcd-feature-actions">
								<?php if ( $f['settings'] ) : ?>
									<button type="button" class="button button-small dwmcd-feature-settings-btn" data-target="dwmcd-feature-settings-<?php echo esc_attr( $f['settings'] ); ?>" title="Instellingen" <?php echo $enabled ? '' : 'style="display:none"'; ?>>
										<span class="dashicons dashicons-admin-generic"></span> Instellingen
									</button>
								<?php endif; ?>
								<label class="dwmcd-toggle">
									<input type="checkbox" name="dwmcd_settings[<?php echo esc_attr( $f['key'] ); ?>]" value="1" <?php checked( $enabled ); ?> class="dwmcd-feature-toggle" data-settings="<?php echo $f['settings'] ? 'dwmcd-feature-settings-' . esc_attr( $f['settings'] ) : ''; ?>">
									<span class="dwmcd-toggle-slider"></span>
								</label>
							</div>
						</div>
						<?php if ( 'login' === $f['settings'] ) : ?>
						<div class="dwmcd-feature-settings" id="dwmcd-feature-settings-login" style="display:<?php echo $enabled ? 'block' : 'none'; ?>">
							<div class="dwmcd-feature-settings-inner">
								<div class="dwmcd-field" style="margin-bottom:14px">
									<label>Welkomsttekst <span class="dwmcd-optional">(boven het formulier)</span></label>
									<input type="text" name="dwmcd_settings[login_welcome_text]" value="<?php echo esc_attr( $settings['login_welcome_text'] ); ?>" placeholder="bijv. Welkom terug!" style="width:100%">
								</div>
								<div class="dwmcd-field">
									<label>Achtergrondafbeelding</label>
									<?php
									$login_bg_url = '';
									if ( ! empty( $settings['login_bg_id'] ) ) {
										$login_bg_url = wp_get_attachment_image_url( (int) $settings['login_bg_id'], 'large' );
									}
									?>
									<div class="dwmcd-logo-uploader">
										<div class="dwmcd-logo-preview<?php echo $login_bg_url ? '' : ' dwmcd-logo-empty'; ?>" id="dwmcd-login-bg-preview" style="width:100%;max-width:400px;height:120px;border-radius:10px;overflow:hidden">
											<?php if ( $login_bg_url ) : ?>
												<img src="<?php echo esc_url( $login_bg_url ); ?>" alt="Login achtergrond" style="width:100%;height:100%;object-fit:cover">
											<?php else : ?>
												<span class="dashicons dashicons-format-image"></span>
												<span>Geen achtergrond geselecteerd</span>
											<?php endif; ?>
										</div>
										<div class="dwmcd-logo-actions" style="margin-top:8px">
											<input type="hidden" name="dwmcd_settings[login_bg_id]" id="dwmcd-login-bg-id" value="<?php echo esc_attr( $settings['login_bg_id'] ); ?>">
											<button type="button" class="button button-secondary" id="dwmcd-login-bg-select">
												<span class="dashicons dashicons-upload"></span> Afbeelding kiezen
											</button>
											<button type="button" class="button-link-delete<?php echo empty( $settings['login_bg_id'] ) ? ' hidden' : ''; ?>" id="dwmcd-login-bg-remove">
												Verwijderen
											</button>
										</div>
									</div>
								</div>
								<p class="dwmcd-muted" style="margin-top:12px;font-size:12px">De <strong>accentkleur</strong> en het <strong>site-icoon</strong> uit het Dashboard-tabblad worden automatisch meegenomen op de login pagina.</p>
							</div>
						</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>

				</div><!-- /functies panel -->

				<div data-tab-panel="settings" class="hidden" role="tabpanel" id="dwmcd-panel-settings" aria-labelledby="dwmcd-tab-settings">

					<div class="dwmcd-card">
						<h2>Site statistieken &amp; inhoud</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Kies wat er getoond wordt op het klantdashboard.</p>
						<div class="dwmcd-switches">
							<label><input type="checkbox" name="dwmcd_settings[show_stats]"          value="1" <?php checked( ! empty( $settings['show_stats'] ) ); ?>> Site statistieken tonen (berichten, pagina's, media)</label>
							<label><input type="checkbox" name="dwmcd_settings[show_recent_content]" value="1" <?php checked( ! empty( $settings['show_recent_content'] ) ); ?>> Recent bewerkt tonen</label>
						</div>
						<div class="dwmcd-field" style="margin-top:14px;max-width:160px">
							<label>Aantal recente items</label>
							<input type="number" min="1" max="20" name="dwmcd_settings[recent_content_count]" value="<?php echo esc_attr( $settings['recent_content_count'] ); ?>">
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>Menu-items verbergen</h2>
						<div class="dwmcd-info-box">
							<span class="dashicons dashicons-info"></span>
							<p>Deze items worden verborgen voor gebruikers <strong>zonder beheerderstoegang</strong> (bijv. redacteuren en klanten). Als beheerder blijft u ze altijd zien. Tip: gebruik de <strong>Menu-organizer</strong> voor meer controle over zichtbaarheid per item.</p>
						</div>
						<div class="dwmcd-switches" style="margin-top:14px">
							<label><input type="checkbox" name="dwmcd_settings[hide_comments]" value="1" <?php checked( ! empty( $settings['hide_comments'] ) ); ?>> Verberg Reacties</label>
							<label><input type="checkbox" name="dwmcd_settings[hide_tools]"    value="1" <?php checked( ! empty( $settings['hide_tools'] ) ); ?>> Verberg Gereedschap</label>
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>Inhoud post types</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Bepaalt welke post types getoond worden in het "Recent bewerkt" blok op het dashboard. Pagina's worden altijd meegenomen.</p>
						<div class="dwmcd-field" style="max-width:420px">
							<label>Post type slugs <span class="dwmcd-optional">(kommagescheiden)</span></label>
							<input type="text" name="dwmcd_settings[article_cpt_slug]" value="<?php echo esc_attr( $settings['article_cpt_slug'] ); ?>" placeholder="post">
							<small class="dwmcd-help">Standaard: <code>post</code>. Meerdere post types scheiden met komma's, bijv: <code>post,nieuws,vacature,product</code>. Pagina's worden altijd getoond ongeacht deze instelling.</small>
						</div>
					</div>

				</div><!-- /settings panel -->

				<!-- ══ TAB: TOEGANG ══════════════════════════════════════════ -->
				<div data-tab-panel="access" class="hidden" role="tabpanel" id="dwmcd-panel-access" aria-labelledby="dwmcd-tab-access">

					<div class="dwmcd-card">
						<h2>Plugintoegang</h2>
						<p class="dwmcd-muted" style="margin-bottom:14px">Kies wie de instellingen van dit dashboard mag beheren. De huidige ingelogde gebruiker behoudt altijd toegang.</p>
						<div class="dwmcd-user-grid">
							<?php foreach ( $users as $u ) : ?>
								<label class="dwmcd-user-card">
									<input type="checkbox" name="dwmcd_settings[manager_user_ids][]"
										value="<?php echo esc_attr( $u->ID ); ?>"
										<?php checked( in_array( (int) $u->ID, $saved_ids, true ) ); ?>>
									<span class="dwmcd-user-name"><?php echo esc_html( $u->display_name ); ?></span>
									<small><?php echo esc_html( $u->user_email ); ?></small>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>WordPress branding</h2>
						<div class="dwmcd-switches">
							<label><input type="checkbox" name="dwmcd_settings[hide_wp_logo]"  value="1" <?php checked( ! empty( $settings['hide_wp_logo'] ) ); ?>> Verberg WordPress logo in de adminbar <span class="dwmcd-optional">(vervang door site-icoon als ingesteld)</span></label>
							<label><input type="checkbox" name="dwmcd_settings[hide_notices]"  value="1" <?php checked( ! empty( $settings['hide_notices'] ) ); ?>> Verberg plugin-meldingen voor redacteuren</label>
						</div>
					</div>

					<div class="dwmcd-card">
						<h2>Voettekst</h2>
						<div class="dwmcd-switches" style="margin-bottom:14px">
							<label><input type="checkbox" name="dwmcd_settings[custom_footer]" value="1" <?php checked( ! empty( $settings['custom_footer'] ) ); ?>> Aangepaste voettekst gebruiken</label>
						</div>
						<div class="dwmcd-field">
							<label>Tekst</label>
							<input type="text" name="dwmcd_settings[footer_text]" value="<?php echo esc_attr( $settings['footer_text'] ); ?>" placeholder="Website beheerd door...">
						</div>
					</div>

				</div><!-- /access panel -->

				<div class="dwmcd-submit">
					<?php submit_button( 'Instellingen opslaan', 'primary', 'submit', false ); ?>
					<button type="button" id="dwmcd-reset-settings" class="button button-secondary" style="margin-left:12px;color:#d63638;border-color:#d63638;">Reset alle instellingen</button>
				</div>
			</form>
		</div>
	</div>

	<template id="dwmcd-action-template">
		<?php
		ob_start();
		jbwp_render_action_card(
			'__INDEX__',
			array( 'enabled' => 1, 'title' => '', 'description' => '', 'icon' => 'admin-links', 'action_type' => 'admin_url', 'target' => '', 'capability' => 'read' ),
			$settings
		);
		echo trim( ob_get_clean() );
		?>
	</template>
	<?php
}

/**
 * Renders a single draggable menu chip.
 */
function jbwp_render_menu_chip( $item ) {
	$slug    = $item['slug'] ?? '';
	$label   = $item['label'] ?? $slug;
	$visible = isset( $item['visible'] ) ? (int) $item['visible'] : 1;
	$custom  = $item['custom_label'] ?? '';
	$icon    = $item['icon'] ?? '';

	// Dashicon or generic fallback
	$icon_html = '';
	if ( $icon && 0 === strpos( $icon, 'dashicons-' ) ) {
		$icon_html = '<span class="dashicons ' . esc_attr( $icon ) . ' dwmcd-chip-dashicon"></span>';
	} elseif ( $icon && 0 === strpos( $icon, 'data:image' ) ) {
		$icon_html = '<img src="' . esc_url( $icon ) . '" class="dwmcd-chip-img-icon" alt="">';
	} else {
		$icon_html = '<span class="dashicons dashicons-menu dwmcd-chip-dashicon"></span>';
	}
	?>
	<div class="dwmcd-menu-chip<?php echo $visible ? '' : ' is-hidden'; ?>"
		draggable="true"
		data-slug="<?php echo esc_attr( $slug ); ?>">
		<span class="dwmcd-chip-drag dashicons dashicons-move"></span>
		<?php echo $icon_html; ?>
		<span class="dwmcd-chip-label"><?php echo esc_html( $label ); ?></span>
		<input type="text" class="dwmcd-chip-custom-label" value="<?php echo esc_attr( $custom ); ?>" placeholder="Aanpassen..." title="Aangepaste naam">
		<button type="button" class="dwmcd-chip-move-btn" data-chip-move="up" title="Omhoog" aria-label="Omhoog verplaatsen"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
		<button type="button" class="dwmcd-chip-move-btn" data-chip-move="down" title="Omlaag" aria-label="Omlaag verplaatsen"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
		<button type="button" class="dwmcd-chip-visibility" data-visible="<?php echo esc_attr( $visible ); ?>" title="<?php echo $visible ? 'Zichtbaar — klik om te verbergen' : 'Verborgen — klik om te tonen'; ?>" aria-label="<?php echo $visible ? 'Zichtbaar — klik om te verbergen' : 'Verborgen — klik om te tonen'; ?>">
			<span class="dashicons <?php echo $visible ? 'dashicons-visibility' : 'dashicons-hidden'; ?>"></span>
		</button>
	</div>
	<?php
}

// ── Scripts & styles ──────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	// CSS loads everywhere (branding, sidebar, adminbar, notices)
	wp_enqueue_style( 'dwmcd-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), DWMCD_VERSION );

	// Detect settings page and dashboard page
	$on_settings  = ( 'toplevel_page_dwmcd-settings' === $hook );
	$on_dashboard = ( 'dashboard_page_dwmcd-client-dashboard' === $hook );
	if ( ! $on_settings && ! $on_dashboard ) {
		$screen = get_current_screen();
		if ( $screen ) {
			$on_settings  = $on_settings  || false !== strpos( $screen->id, 'dwmcd-settings' );
			$on_dashboard = $on_dashboard || false !== strpos( $screen->id, 'dwmcd-client-dashboard' );
		}
	}

	// JS only loads on plugin pages (settings + dashboard)
	if ( $on_settings || $on_dashboard ) {
		wp_enqueue_script( 'dwmcd-admin', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array(), DWMCD_VERSION, true );

		$js_data = array(
			'typeHints'        => jbwp_type_hints(),
			'typePlaceholders' => jbwp_type_placeholders(),
			'noTargetTypes'    => jbwp_types_without_target(),
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'dwmcd_nonce' ),
			'presets'          => array(
				'new_post'       => array( 'title' => 'Nieuw bericht',           'description' => 'Schrijf een nieuw bericht of artikel',       'icon' => 'edit',            'action_type' => 'new_post_type', 'target' => 'post',                          'capability' => 'edit_posts' ),
				'list_pages'     => array( 'title' => "Pagina's beheren",        'description' => "Bekijk en bewerk alle pagina's",              'icon' => 'admin-page',      'action_type' => 'list_post_type','target' => 'page',                          'capability' => 'edit_pages' ),
				'media_upload'   => array( 'title' => 'Media uploaden',          'description' => "Upload foto's en bestanden",                  'icon' => 'format-image',    'action_type' => 'media_upload',  'target' => '',                              'capability' => 'upload_files' ),
				'comments'       => array( 'title' => 'Reacties beheren',        'description' => 'Bekijk en beheer reacties op berichten',      'icon' => 'admin-comments',  'action_type' => 'admin_url',     'target' => 'edit-comments.php',             'capability' => 'moderate_comments' ),
				'users'          => array( 'title' => 'Gebruikers',              'description' => 'Beheer websitegebruikers',                    'icon' => 'groups',          'action_type' => 'admin_url',     'target' => 'users.php',                     'capability' => 'list_users' ),
				'menus'          => array( 'title' => "Menu's bewerken",         'description' => 'Bewerk de navigatie van de website',           'icon' => 'menu',            'action_type' => 'admin_url',     'target' => 'nav-menus.php',                 'capability' => 'edit_theme_options' ),
				'customizer'     => array( 'title' => 'Customizer',              'description' => 'Pas het uiterlijk van de website aan',         'icon' => 'admin-customizer','action_type' => 'admin_url',     'target' => 'customize.php',                 'capability' => 'edit_theme_options' ),
				'forms_gf'       => array( 'title' => 'Formulierinzendingen',    'description' => 'Bekijk ingezonden formulieren (Gravity Forms)','icon' => 'feedback',        'action_type' => 'admin_url',     'target' => 'admin.php?page=gf_entries',     'capability' => 'edit_posts' ),
				'forms_wpforms'  => array( 'title' => 'Formulierinzendingen',    'description' => 'Bekijk ingezonden formulieren (WPForms)',      'icon' => 'feedback',        'action_type' => 'admin_url',     'target' => 'admin.php?page=wpforms-entries','capability' => 'edit_posts' ),
				'forms_cf7'      => array( 'title' => 'Contactformulieren',      'description' => 'Beheer formulieren (Contact Form 7)',          'icon' => 'email',           'action_type' => 'admin_url',     'target' => 'admin.php?page=wpcf7',          'capability' => 'edit_posts' ),
				'woo_new_product'=> array( 'title' => 'Nieuw product',            'description' => 'Voeg een nieuw WooCommerce product toe',       'icon' => 'cart',            'action_type' => 'new_post_type', 'target' => 'product',                       'capability' => 'edit_posts' ),
				'woo_products'   => array( 'title' => 'Producten beheren',       'description' => 'Bekijk en bewerk WooCommerce producten',       'icon' => 'products',        'action_type' => 'list_post_type','target' => 'product',                       'capability' => 'edit_posts' ),
				'woo_orders'     => array( 'title' => 'Bestellingen',            'description' => 'Bekijk WooCommerce bestellingen',              'icon' => 'list-view',       'action_type' => 'admin_url',     'target' => 'admin.php?page=wc-orders',      'capability' => 'edit_posts' ),
				'woo_customers'  => array( 'title' => 'Klanten',                 'description' => 'Bekijk WooCommerce klanten',                   'icon' => 'groups',          'action_type' => 'admin_url',     'target' => 'admin.php?page=wc-admin&path=/customers', 'capability' => 'list_users' ),
				'woo_coupons'    => array( 'title' => 'Kortingsbonnen',          'description' => 'Beheer kortingsbonnen en acties',              'icon' => 'tickets-alt',     'action_type' => 'admin_url',     'target' => 'edit.php?post_type=shop_coupon','capability' => 'edit_posts' ),
				'woo_reports'    => array( 'title' => 'Webshop rapporten',       'description' => 'Bekijk verkooprapporten en analytics',         'icon' => 'chart-bar',       'action_type' => 'admin_url',     'target' => 'admin.php?page=wc-reports',     'capability' => 'edit_posts' ),
				'woo_reviews'    => array( 'title' => 'Productreviews',          'description' => 'Modereer productbeoordelingen',                'icon' => 'star-filled',     'action_type' => 'admin_url',     'target' => 'edit-comments.php?comment_type=review', 'capability' => 'moderate_comments' ),
				'woo_settings'   => array( 'title' => 'WooCommerce instellingen','description' => 'Configureer WooCommerce',                      'icon' => 'admin-settings',  'action_type' => 'admin_url',     'target' => 'admin.php?page=wc-settings',    'capability' => 'manage_options' ),
				'visit_site'     => array( 'title' => 'Website bekijken',        'description' => 'Open de live website',                         'icon' => 'external',        'action_type' => 'site_url',      'target' => '',                              'capability' => 'read' ),
			),
		);
		wp_add_inline_script( 'dwmcd-admin', 'window.DWMCD = ' . wp_json_encode( $js_data ) . ';', 'before' );

		if ( $on_settings ) {
			wp_enqueue_media();
		}
	}
} );

// ── Site icon: filter WP's eigen output via officiële hook ───────────────────

add_filter( 'get_site_icon_url', function ( $url, $size, $blog_id ) {
	$s = jbwp_get_settings();
	if ( empty( $s['site_icon_id'] ) ) {
		return $url;
	}
	$icon_id = (int) $s['site_icon_id'];
	// Serve the closest available size instead of always the full image.
	$sized = wp_get_attachment_image_url( $icon_id, array( $size, $size ) );
	if ( $sized ) {
		return $sized;
	}
	$custom = wp_get_attachment_url( $icon_id );
	return $custom ?: $url;
}, 10, 3 );

// Replace WP's default favicon meta tags with our own (properly sized)
add_filter( 'site_icon_meta_tags', function ( $tags ) {
	$s = jbwp_get_settings();
	if ( empty( $s['site_icon_id'] ) ) {
		return $tags;
	}
	$icon_id  = (int) $s['site_icon_id'];
	$fallback = wp_get_attachment_url( $icon_id );
	if ( ! $fallback ) {
		return $tags;
	}
	$url_32  = wp_get_attachment_image_url( $icon_id, array( 32, 32 ) )   ?: $fallback;
	$url_192 = wp_get_attachment_image_url( $icon_id, array( 192, 192 ) ) ?: $fallback;
	$url_180 = wp_get_attachment_image_url( $icon_id, array( 180, 180 ) ) ?: $fallback;
	return array(
		'<link rel="icon" href="' . esc_url( $url_32 ) . '" sizes="32x32">',
		'<link rel="icon" href="' . esc_url( $url_192 ) . '" sizes="192x192">',
		'<link rel="apple-touch-icon" href="' . esc_url( $url_180 ) . '">',
	);
} );

// ── Branding CSS ──────────────────────────────────────────────────────────────

add_action( 'admin_head', function () {
	$s          = jbwp_get_settings();
	$accent     = sanitize_hex_color( $s['accent_color'] ) ?: '#2952ff';
	$sidebar_bg = sanitize_hex_color( $s['sidebar_bg'] )   ?: '#ffffff';

	$rgb = sscanf( $accent, '#%02x%02x%02x' );
	$r   = isset( $rgb[0] ) ? (int) $rgb[0] : 41;
	$g   = isset( $rgb[1] ) ? (int) $rgb[1] : 82;
	$b   = isset( $rgb[2] ) ? (int) $rgb[2] : 255;

	$sb_colors = jbwp_parse_sidebar_colors( $sidebar_bg );

	echo '<style id="dwmcd-branding">:root{'
		. '--dwmcd-accent:'        . esc_attr( $accent )     . ';'
		. '--dwmcd-accent-soft:rgba(' . $r . ',' . $g . ',' . $b . ',0.09);'
		. '--dwmcd-sidebar-bg:'    . esc_attr( $sidebar_bg ) . ';'
		. '--dwmcd-sidebar-text:'  . esc_attr( $sb_colors['text'] )  . ';'
		. '--dwmcd-sidebar-muted:' . esc_attr( $sb_colors['muted'] ) . ';'
		. '}</style>';

	// Admin favicon — only output if WP doesn't have its own site icon
	// (if WP has one, our site_icon_meta_tags filter already handles it)
	if ( ! empty( $s['site_icon_id'] ) && ! has_site_icon() ) {
		jbwp_favicon_tags( (int) $s['site_icon_id'] );
	}
}, 999 );

// ── Frontend topbar styling ───────────────────────────────────────────────────

add_action( 'wp_head', function () {
	if ( ! is_admin_bar_showing() ) {
		return;
	}
	$s          = jbwp_get_settings();
	$accent     = sanitize_hex_color( $s['accent_color'] ) ?: '#2952ff';
	$sidebar_bg = sanitize_hex_color( $s['sidebar_bg'] )   ?: '#ffffff';
	$c          = jbwp_parse_sidebar_colors( $sidebar_bg );

	echo '<style id="dwmcd-frontend-bar">'
		// Main bar background
		. '#wpadminbar{'
		. 'background:' . esc_attr( $sidebar_bg ) . ' !important;'
		. 'border-bottom:1px solid ' . esc_attr( $c['sub_border'] ) . ' !important;'
		. 'box-shadow:0 1px 8px rgba(0,0,0,.07) !important;'
		. '}'

		// All text / labels / icons in bar
		. '#wpadminbar .ab-item,'
		. '#wpadminbar a.ab-item,'
		. '#wpadminbar .ab-item:before,'
		. '#wpadminbar .ab-icon,'
		. '#wpadminbar .ab-icon:before,'
		. '#wpadminbar .ab-label,'
		. '#wpadminbar li#wp-admin-bar-my-account.with-avatar>a span.ab-label,'
		. '#wpadminbar #wp-admin-bar-my-account .ab-item{'
		. 'color:' . esc_attr( $c['text'] ) . ' !important;'
		. '}'

		// Hover state for top-level items
		. '#wpadminbar .ab-top-menu>li:hover>.ab-item,'
		. '#wpadminbar .ab-top-menu>li.hover>.ab-item,'
		. '#wpadminbar .ab-top-menu>li:focus>.ab-item{'
		. 'background:' . esc_attr( $c['hover'] ) . ' !important;'
		. 'color:' . esc_attr( $accent ) . ' !important;'
		. '}'

		// Hover: icon color follows accent
		. '#wpadminbar .ab-top-menu>li:hover>.ab-item:before,'
		. '#wpadminbar .ab-top-menu>li.hover>.ab-item:before,'
		. '#wpadminbar .ab-top-menu>li:hover .ab-icon:before,'
		. '#wpadminbar .ab-top-menu>li.hover .ab-icon:before{'
		. 'color:' . esc_attr( $accent ) . ' !important;'
		. '}'

		// Sub-menu background
		. '#wpadminbar .menupop .ab-sub-wrapper,'
		. '#wpadminbar .shortlink-input{'
		. 'background:' . esc_attr( $c['sub'] ) . ' !important;'
		. 'border:1px solid ' . esc_attr( $c['sub_border'] ) . ' !important;'
		. 'border-top:none !important;'
		. 'box-shadow:0 4px 16px rgba(0,0,0,.12) !important;'
		. '}'

		// Sub-menu items text
		. '#wpadminbar .menupop .ab-sub-wrapper .ab-item,'
		. '#wpadminbar .menupop .ab-sub-wrapper a.ab-item,'
		. '#wpadminbar .menupop .ab-sub-wrapper .ab-item:before{'
		. 'color:' . esc_attr( $c['text'] ) . ' !important;'
		. 'background:transparent !important;'
		. '}'

		// Sub-menu item hover
		. '#wpadminbar .menupop .ab-sub-wrapper li:hover>.ab-item,'
		. '#wpadminbar .menupop .ab-sub-wrapper li.hover>.ab-item{'
		. 'background:' . esc_attr( $c['hover'] ) . ' !important;'
		. 'color:' . esc_attr( $accent ) . ' !important;'
		. '}'

		// Site icon node — matches dashicon size (20px)
		. '#wpadminbar #wp-admin-bar-dwmcd-site-icon>a.ab-item{padding:0 7px !important;display:flex !important;align-items:center !important;justify-content:center !important;height:32px !important;min-width:36px !important;}'
		. '#wpadminbar #wp-admin-bar-dwmcd-site-icon img.dwmcd-adminbar-icon,#wpadminbar img.dwmcd-adminbar-icon{height:20px !important;width:20px !important;max-height:20px !important;max-width:20px !important;object-fit:contain !important;border-radius:3px !important;display:block !important;margin:0 !important;padding:0 !important;border:none !important;}'
		. '</style>';

	// Frontend favicon — only output if WP doesn't have its own site icon
	if ( ! empty( $s['site_icon_id'] ) && ! has_site_icon() ) {
		jbwp_favicon_tags( (int) $s['site_icon_id'] );
	}
} );

// ── Menu — volgorde ───────────────────────────────────────────────────────────
//
// Priority 9999: WooCommerce (and several other plugins) hook these same
// filters at the default priority (10) and aggressively reorder the admin
// menu — e.g. WC pins `woocommerce` and `edit.php?post_type=product` to
// fixed positions via its own `menu_order` callback. By running absolutely
// last we make sure JBWP's user-defined order is the final word and never
// gets overwritten by other plugins.

add_filter( 'custom_menu_order', function ( $enabled ) {
	$s = jbwp_get_settings();
	if ( ! empty( $s['menu_organizer_enabled'] ) && ! empty( $s['menu_order'] ) ) {
		return true;
	}
	// Don't disable custom ordering for other plugins (e.g. WooCommerce) if
	// the JBWP organizer itself is off.
	return $enabled;
}, 9999 );

add_filter( 'menu_order', function ( $order ) {
	$s = jbwp_get_settings();
	if ( empty( $s['menu_organizer_enabled'] ) || empty( $s['menu_order'] ) ) {
		return $order;
	}

	// Start with the user-defined order, then append any items present in the
	// live menu that the user hasn't placed yet (newly-installed plugins,
	// WooCommerce sub-pages, separators, etc.) so nothing disappears.
	$saved_slugs = array_values( array_filter( array_column( (array) $s['menu_order'], 'slug' ) ) );
	$new_order   = array();
	$seen        = array();
	foreach ( $saved_slugs as $slug ) {
		if ( isset( $seen[ $slug ] ) ) {
			continue;
		}
		$seen[ $slug ] = true;
		$new_order[]   = $slug;
	}
	foreach ( (array) $order as $slug ) {
		if ( ! isset( $seen[ $slug ] ) ) {
			$seen[ $slug ] = true;
			$new_order[]   = $slug;
		}
	}
	return $new_order;
}, 9999 );

// ── Menu — aangepaste labels ──────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	global $menu;
	$s = jbwp_get_settings();
	if ( empty( $s['menu_organizer_enabled'] ) || empty( $s['menu_order'] ) ) {
		return;
	}
	$label_map = array();
	foreach ( $s['menu_order'] as $item ) {
		if ( ! empty( $item['slug'] ) && ! empty( $item['custom_label'] ) ) {
			$label_map[ $item['slug'] ] = $item['custom_label'];
		}
	}
	if ( empty( $label_map ) ) {
		return;
	}
	foreach ( $menu as &$item ) {
		if ( empty( $item[2] ) || ! isset( $label_map[ $item[2] ] ) ) {
			continue;
		}
		$bubble = '';
		if ( preg_match( '/(<span\b[^>]*>.*?<\/span>)$/is', $item[0], $m ) ) {
			$bubble = $m[1];
		}
		$item[0] = esc_html( $label_map[ $item[2] ] ) . $bubble;
	}
	unset( $item );
}, 999 );

// ── Menu — verberg items voor niet-beheerders ─────────────────────────────────

add_action( 'admin_menu', function () {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = jbwp_get_settings();

	if ( ! empty( $s['hide_comments'] ) ) {
		remove_menu_page( 'edit-comments.php' );
	}
	if ( ! empty( $s['hide_tools'] ) ) {
		remove_menu_page( 'tools.php' );
	}

	if ( ! empty( $s['menu_organizer_enabled'] ) ) {
		foreach ( (array) $s['menu_order'] as $item ) {
			if ( empty( $item['visible'] ) && ! empty( $item['slug'] ) ) {
				remove_menu_page( $item['slug'] );
			}
		}
	}
}, 998 );

// ── Menu — groeplabels injecteren in admin sidebar via JS ─────────────────────
//
// We resolve each group's first slug to its EXACT <li> element id by reading
// the live $menu global. WordPress writes $item[5] (the "hookname") into the
// id attribute of the rendered <li>, sanitised with the same regex used here.
// This avoids the previous fragile href-matching, which broke for plugins
// where the parent menu slug doesn't appear literally in its href — the
// canonical example being WooCommerce, whose slug is `woocommerce` but whose
// landing href is `admin.php?page=wc-admin`.

add_action( 'admin_head', function () {
	global $menu;
	$s = jbwp_get_settings();
	if ( empty( $s['menu_organizer_enabled'] ) ) {
		return;
	}
	$groups = (array) ( $s['menu_groups'] ?? array() );
	$order  = (array) ( $s['menu_order']  ?? array() );
	if ( empty( $groups ) || empty( $order ) ) {
		return;
	}

	// group_id => display name
	$group_names = array();
	foreach ( $groups as $grp ) {
		$gid = $grp['id'] ?? '';
		if ( $gid ) {
			$group_names[ $gid ] = $grp['name'] ?? '';
		}
	}

	// group_id => first slug (in saved order)
	$group_first_slug = array();
	foreach ( $order as $item ) {
		$slug = $item['slug'] ?? '';
		$gid  = $item['group'] ?? '';
		if ( $slug && $gid && ! isset( $group_first_slug[ $gid ] ) ) {
			$group_first_slug[ $gid ] = $slug;
		}
	}

	if ( empty( $group_first_slug ) ) {
		return;
	}

	// slug => element id (built from $menu[$i][5] hookname, same way WP does it)
	$slug_to_id = array();
	foreach ( (array) $menu as $m_item ) {
		if ( empty( $m_item[2] ) || empty( $m_item[5] ) ) {
			continue;
		}
		$slug_to_id[ $m_item[2] ] = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $m_item[5] );
	}

	$inject = array();
	foreach ( $group_first_slug as $gid => $slug ) {
		$label = $group_names[ $gid ] ?? '';
		$id    = $slug_to_id[ $slug ] ?? '';
		if ( $label && $id ) {
			$inject[] = array( 'id' => $id, 'label' => $label );
		}
	}

	if ( empty( $inject ) ) {
		return;
	}

	// Use admin_footer to inject the labels — runs after the menu DOM exists.
	add_action( 'admin_footer', function () use ( $inject ) {
		echo '<script>(function(){var d=' . wp_json_encode( $inject ) . ';'
			. 'd.forEach(function(i){var li=document.getElementById(i.id);if(!li||!i.label)return;'
			. 'var l=document.createElement("li");l.className="dwmcd-menu-cat-label";l.textContent=i.label;'
			. 'li.parentNode.insertBefore(l,li);});})();</script>';
	} );
} );

// ── Admin bar — site-icoon & WP logo ─────────────────────────────────────────

add_action( 'admin_bar_menu', function ( $bar ) {
	$s = jbwp_get_settings();

	if ( ! empty( $s['site_icon_id'] ) ) {
		// Use a small thumbnail for the adminbar (64×64 max) instead of the full-size image.
		$icon_url = wp_get_attachment_image_url( (int) $s['site_icon_id'], array( 64, 64 ) );
		if ( ! $icon_url ) {
			$icon_url = wp_get_attachment_url( (int) $s['site_icon_id'] );
		}
		if ( $icon_url ) {
			$bar->remove_node( 'wp-logo' );
			$bar->add_node( array(
				'id'    => 'dwmcd-site-icon',
				'title' => '<img src="' . esc_url( $icon_url ) . '" class="dwmcd-adminbar-icon" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">',
				'href'  => home_url( '/' ),
				'meta'  => array( 'class' => 'dwmcd-site-icon-node', 'target' => '_blank' ),
			) );
			return;
		}
	}

	if ( ! empty( $s['hide_wp_logo'] ) ) {
		$bar->remove_node( 'wp-logo' );
	}
}, 11 );

// ── Login pagina — branding ──────────────────────────────────────────────────

add_action( 'login_head', function () {
	$s = jbwp_get_settings();

	// Favicon
	if ( ! empty( $s['site_icon_id'] ) ) {
		jbwp_favicon_tags( (int) $s['site_icon_id'] );
	}

	// Gather branding data
	$icon_url  = '';
	$bg_url    = '';
	$accent    = $s['accent_color'] ?? '#2952ff';
	$card      = ! empty( $s['login_card_style'] );

	if ( ! empty( $s['site_icon_id'] ) ) {
		$icon_url = wp_get_attachment_image_url( (int) $s['site_icon_id'], array( 180, 180 ) );
		if ( ! $icon_url ) {
			$icon_url = wp_get_attachment_url( (int) $s['site_icon_id'] );
		}
	}
	if ( ! empty( $s['login_bg_id'] ) ) {
		$bg_url = wp_get_attachment_image_url( (int) $s['login_bg_id'], 'full' );
		if ( ! $bg_url ) {
			$bg_url = wp_get_attachment_url( (int) $s['login_bg_id'] );
		}
	}

	// Nothing to customize? Exit.
	if ( ! $icon_url && ! $bg_url && ! $card ) {
		return;
	}

	echo '<style>';

	// Logo replacement
	if ( $icon_url ) {
		echo '.login h1 a{'
			. 'background-image:url(' . esc_url( $icon_url ) . ') !important;'
			. 'background-size:contain !important;'
			. 'width:80px !important;'
			. 'height:80px !important;'
			. 'background-repeat:no-repeat !important;'
			. 'background-position:center !important;'
			. '}';
	}

	// Background image
	if ( $bg_url ) {
		echo 'body.login{'
			. 'background:url(' . esc_url( $bg_url ) . ') center/cover no-repeat fixed !important;'
			. 'min-height:100vh;'
			. '}'
			. 'body.login::before{'
			. 'content:"";position:fixed;inset:0;'
			. 'background:rgba(0,0,0,.45);'
			. 'z-index:0;'
			. '}'
			. 'body.login > *{position:relative;z-index:1;}';
	}

	// Modern card style
	if ( $card ) {
		echo '#login{'
			. 'background:#fff;'
			. 'border-radius:16px;'
			. 'padding:32px 28px 24px !important;'
			. 'box-shadow:0 8px 40px rgba(0,0,0,.12);'
			. 'margin-top:6vh;'
			. 'max-width:380px;'
			. 'width:90%;'
			. '}'
			. '#loginform{'
			. 'border:none !important;'
			. 'box-shadow:none !important;'
			. 'padding:0 !important;'
			. 'margin:0 !important;'
			. 'background:transparent !important;'
			. '}'
			. '#loginform .input,#loginform input[type="text"],#loginform input[type="password"]{'
			. 'border:1px solid #e2e8f0 !important;'
			. 'border-radius:8px !important;'
			. 'padding:8px 12px !important;'
			. 'font-size:14px !important;'
			. 'transition:border-color .2s !important;'
			. '}'
			. '#loginform .input:focus,#loginform input[type="text"]:focus,#loginform input[type="password"]:focus{'
			. 'border-color:' . esc_attr( $accent ) . ' !important;'
			. 'box-shadow:0 0 0 2px ' . esc_attr( $accent ) . '22 !important;'
			. '}'
			. '#login h1{margin-bottom:8px;}'
			. '#login h1 a{margin-bottom:8px !important;}'
			. '#nav,#backtoblog{text-align:center;padding:0 !important;}'
			. '#nav a,#backtoblog a{color:#64748b !important;}'
			. '#nav a:hover,#backtoblog a:hover{color:' . esc_attr( $accent ) . ' !important;}'
			. '.login .message,.login .success,.login #login_error{'
			. 'border-radius:8px !important;'
			. 'border-left-width:4px !important;'
			. 'margin-bottom:16px !important;'
			. '}';
	}

	// Accent color on login button (always apply when set)
	echo '#wp-submit,.wp-core-ui .button-primary{'
		. 'background:' . esc_attr( $accent ) . ' !important;'
		. 'border-color:' . esc_attr( $accent ) . ' !important;'
		. 'border-radius:8px !important;'
		. 'padding:6px 24px !important;'
		. 'font-size:14px !important;'
		. 'font-weight:600 !important;'
		. 'height:auto !important;'
		. 'line-height:1.5 !important;'
		. 'transition:opacity .2s !important;'
		. '}'
		. '#wp-submit:hover,.wp-core-ui .button-primary:hover{'
		. 'opacity:.85 !important;'
		. '}'
		. '#wp-submit:focus,.wp-core-ui .button-primary:focus{'
		. 'box-shadow:0 0 0 2px #fff,0 0 0 4px ' . esc_attr( $accent ) . ' !important;'
		. '}';

	echo '</style>';
} );

// ── Login pagina — welkomsttekst ─────────────────────────────────────────────

add_filter( 'login_message', function ( $message ) {
	$s = jbwp_get_settings();
	if ( empty( $s['login_welcome_text'] ) ) {
		return $message;
	}
	$text = esc_html( $s['login_welcome_text'] );
	return '<p style="text-align:center;font-size:16px;font-weight:500;color:#334155;margin:0 0 12px;line-height:1.4">'
		. $text . '</p>' . $message;
} );

// ── Footer ────────────────────────────────────────────────────────────────────

add_filter( 'admin_footer_text', function ( $text ) {
	$s = jbwp_get_settings();
	return ! empty( $s['custom_footer'] ) ? esc_html( $s['footer_text'] ) : $text;
}, 999 );

// ── Meldingen verbergen voor redacteuren ──────────────────────────────────────

add_action( 'admin_head', function () {
	$s = jbwp_get_settings();
	if ( ! empty( $s['hide_notices'] ) && ! current_user_can( 'manage_options' ) ) {
		echo '<style>.notice,.update-nag,.fs-notice,.rank-math-notice,.redux-notice,.wp-pointer{display:none !important}</style>';
	}
} );

// ── Dashboard redirect ────────────────────────────────────────────────────────

add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'DOING_CRON' ) && DOING_CRON )
		|| ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	global $pagenow;
	if ( ! isset( $pagenow ) || 'index.php' !== $pagenow ) {
		return;
	}
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'dwmcd-client-dashboard' !== $page ) {
		wp_safe_redirect( admin_url( 'index.php?page=dwmcd-client-dashboard' ) );
		exit;
	}
} );

// ── AJAX: GA4 refresh ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_dwmcd_ga4_refresh', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( DWMCD_CAP ) ) {
		wp_send_json_error( array( 'message' => 'Geen toegang.' ) );
	}
	delete_transient( 'dwmcd_ga4_data' );
	$data = jbwp_ga4_fetch_fresh();
	wp_send_json_success( array( 'html' => jbwp_ga4_render_widget( $data ) ) );
} );

add_action( 'wp_ajax_dwmcd_ga4_test', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( DWMCD_CAP ) ) {
		wp_send_json_error( array( 'message' => 'Geen toegang.' ) );
	}
	$creds = jbwp_ga4_credentials();
	if ( ! $creds ) {
		wp_send_json_error( array( 'message' => 'Geen geldige service account JSON gevonden. Sla de instellingen eerst op.' ) );
	}
	if ( ! function_exists( 'openssl_sign' ) ) {
		wp_send_json_error( array( 'message' => 'openssl PHP-extensie niet beschikbaar op deze server.' ) );
	}
	$token = jbwp_ga4_access_token( $creds );
	if ( ! $token ) {
		wp_send_json_error( array( 'message' => 'Authenticatie mislukt. Controleer de JSON en het property ID.' ) );
	}
	wp_send_json_success( array( 'message' => 'Verbinding geslaagd! Het service account is correct geconfigureerd.' ) );
} );

add_action( 'wp_ajax_dwmcd_ga4_load', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	$data = jbwp_ga4_get_data();
	wp_send_json_success( array( 'html' => jbwp_ga4_render_widget( $data ) ) );
} );

// ── AJAX: Reset instellingen ────────────────────────────────────────────────

add_action( 'wp_ajax_dwmcd_reset_settings', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Geen toegang.' ) );
	}
	update_option( DWMCD_OPTION, jbwp_defaults() );
	wp_send_json_success( array( 'message' => 'Alle instellingen zijn teruggezet naar de standaardwaarden.' ) );
} );

// ══════════════════════════════════════════════════════════════════════════════
// ── MODULE: Media Categories ────────────────────────────────────────────────
// ══════════════════════════════════════════════════════════════════════════════
//
// CatFolders-style media organization. Registers a hierarchical taxonomy
// `media_category` on `attachment`, injects a sidebar folder tree into the
// media library page with:
//  • Real-time grid filtering (no page reload in grid view)
//  • Drag-and-drop media → folder assignment
//  • Inline folder CRUD (create, rename, delete)
//  • Dynamic item counts that update after every action
//  • "Uncategorized" pseudo-folder
//  • List-view dropdown filter fallback

// ── Taxonomy registration ───────────────────────────────────────────────────

add_action( 'init', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) ) {
		return;
	}
	register_taxonomy( 'media_category', 'attachment', array(
		'labels' => array(
			'name'              => 'Mediamappen',
			'singular_name'     => 'Mediamap',
			'search_items'      => 'Mappen zoeken',
			'all_items'         => 'Alle mappen',
			'edit_item'         => 'Map bewerken',
			'update_item'       => 'Map bijwerken',
			'add_new_item'      => 'Nieuwe map',
			'new_item_name'     => 'Nieuwe mapnaam',
			'menu_name'         => 'Mediamappen',
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => false,
		'update_count_callback' => '_update_generic_term_count',
	) );
} );

// ── Custom "Map" column in list view with inline assign ─────────────────────

add_filter( 'manage_media_columns', function ( $columns ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) ) {
		return $columns;
	}
	// Insert after 'author' column
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'author' === $key ) {
			$new['jbwp_media_folder'] = 'Map';
		}
	}
	return $new;
} );

add_action( 'manage_media_custom_column', function ( $column, $post_id ) {
	if ( 'jbwp_media_folder' !== $column ) {
		return;
	}
	$terms = wp_get_object_terms( $post_id, 'media_category' );
	$all   = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	if ( is_wp_error( $all ) ) {
		$all = array();
	}

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$names = array();
		foreach ( $terms as $t ) {
			$names[] = '<a href="' . esc_url( admin_url( 'upload.php?media_category=' . $t->slug ) ) . '">' . esc_html( $t->name ) . '</a>';
		}
		echo implode( ', ', $names );
		echo ' <button class="jbwp-list-assign-btn" data-id="' . esc_attr( $post_id ) . '" title="Map wijzigen" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:12px;vertical-align:middle">&#9998;</button>';
	} else {
		echo '<span style="color:#9ca3af">&mdash;</span> ';
		echo '<button class="jbwp-list-assign-btn" data-id="' . esc_attr( $post_id ) . '" style="background:none;border:none;cursor:pointer;color:var(--dwmcd-accent,#2952ff);font-size:12px">Toewijzen</button>';
	}

	// Hidden dropdown (shown on click)
	echo '<div class="jbwp-list-assign-dropdown" data-id="' . esc_attr( $post_id ) . '" style="display:none">';
	echo '<select class="jbwp-list-assign-select" data-id="' . esc_attr( $post_id ) . '">';
	echo '<option value="0">— Geen map —</option>';
	$current_ids = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'term_id' ) : array();
	foreach ( $all as $t ) {
		$sel = in_array( $t->term_id, $current_ids, true ) ? ' selected' : '';
		echo '<option value="' . esc_attr( $t->term_id ) . '"' . $sel . '>' . esc_html( $t->name ) . '</option>';
	}
	echo '</select></div>';
}, 10, 2 );

// ── Dropdown filter fallback for list view ──────────────────────────────────

add_action( 'restrict_manage_posts', function () {
	global $typenow;
	if ( 'attachment' !== $typenow ) {
		return;
	}
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) || ! taxonomy_exists( 'media_category' ) ) {
		return;
	}
	$terms = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	$selected = isset( $_GET['media_category'] ) ? sanitize_key( $_GET['media_category'] ) : '';
	echo '<select name="media_category" id="filter-by-media-category">';
	echo '<option value="">Alle mappen</option>';
	echo '<option value="__uncategorized"' . selected( $selected, '__uncategorized', false ) . '>Niet gecategoriseerd</option>';
	foreach ( $terms as $term ) {
		printf( '<option value="%s"%s>%s (%d)</option>', esc_attr( $term->slug ), selected( $selected, $term->slug, false ), esc_html( $term->name ), (int) $term->count );
	}
	echo '</select>';
} );

// ── Intercept __uncategorized before WP parses it as a term slug ────────────
// The taxonomy has query_var => true, so WP auto-parses ?media_category=X as
// a taxonomy query. Since "__uncategorized" is not a real term slug, WP would
// return zero results. We intercept it here and replace it with a custom flag.

add_filter( 'request', function ( $query_vars ) {
	if ( isset( $query_vars['media_category'] ) && '__uncategorized' === $query_vars['media_category'] ) {
		unset( $query_vars['media_category'] );
		$query_vars['jbwp_show_uncategorized'] = 1;
	}
	return $query_vars;
} );

// ── Query filter (list view + uncategorized support) ────────────────────────

add_action( 'pre_get_posts', function ( $query ) {
	global $pagenow;
	if ( 'upload.php' !== $pagenow || ! $query->is_main_query() ) {
		return;
	}
	// Check our custom flag (set by the request filter above)
	if ( ! empty( $query->get( 'jbwp_show_uncategorized' ) ) ) {
		$query->set( 'tax_query', array( array(
			'taxonomy' => 'media_category',
			'operator' => 'NOT EXISTS',
		) ) );
		return;
	}
	$cat = isset( $_GET['media_category'] ) ? sanitize_key( $_GET['media_category'] ) : '';
	if ( '__uncategorized' === $cat ) {
		$query->set( 'tax_query', array( array(
			'taxonomy' => 'media_category',
			'operator' => 'NOT EXISTS',
		) ) );
	} elseif ( '' !== $cat ) {
		$query->set( 'tax_query', array( array(
			'taxonomy' => 'media_category',
			'field'    => 'slug',
			'terms'    => $cat,
		) ) );
	}
} );

// ── AJAX filter for grid view ───────────────────────────────────────────────

add_filter( 'ajax_query_attachments_args', function ( $query ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) ) {
		return $query;
	}
	$cat = ! empty( $_REQUEST['query']['media_category'] ) ? sanitize_key( $_REQUEST['query']['media_category'] ) : '';
	if ( '__uncategorized' === $cat ) {
		$query['tax_query'] = array( array(
			'taxonomy' => 'media_category',
			'operator' => 'NOT EXISTS',
		) );
	} elseif ( '' !== $cat ) {
		$query['tax_query'] = array( array(
			'taxonomy' => 'media_category',
			'field'    => 'slug',
			'terms'    => $cat,
		) );
	}
	return $query;
} );

// ── Attachment details: category checkboxes ────────────────────────────────

add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) || ! taxonomy_exists( 'media_category' ) ) {
		return $fields;
	}
	$terms = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $fields;
	}
	$current = wp_get_object_terms( $post->ID, 'media_category', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $current ) ) {
		$current = array();
	}
	$html = '<div class="jbwp-media-cats">';
	foreach ( $terms as $t ) {
		$checked = in_array( $t->term_id, $current, true ) ? ' checked' : '';
		$html .= '<label style="display:block;margin:2px 0"><input type="checkbox" name="jbwp_media_cat[]" value="' . esc_attr( $t->term_id ) . '"' . $checked . '> ' . esc_html( $t->name ) . '</label>';
	}
	$html .= '</div>';
	$fields['media_category'] = array( 'label' => 'Map', 'input' => 'html', 'html' => $html );
	return $fields;
}, 10, 2 );

add_filter( 'attachment_fields_to_save', function ( $post, $attachment ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) || ! taxonomy_exists( 'media_category' ) ) {
		return $post;
	}
	$cat_ids = array();
	if ( ! empty( $_REQUEST['jbwp_media_cat'] ) && is_array( $_REQUEST['jbwp_media_cat'] ) ) {
		$cat_ids = array_map( 'absint', $_REQUEST['jbwp_media_cat'] );
	}
	wp_set_object_terms( $post['ID'], $cat_ids, 'media_category' );
	return $post;
}, 10, 2 );

// ── AJAX: folder CRUD + bulk assign + count refresh ────────────────────────

add_action( 'wp_ajax_jbwp_media_folder_create', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	if ( '' === $name ) {
		wp_send_json_error( array( 'message' => 'Naam is verplicht.' ) );
	}
	$result = wp_insert_term( $name, 'media_category' );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}
	$term = get_term( $result['term_id'], 'media_category' );
	wp_send_json_success( array( 'id' => $term->term_id, 'slug' => $term->slug, 'name' => $term->name, 'count' => 0 ) );
} );

add_action( 'wp_ajax_jbwp_media_folder_rename', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}
	$term_id = absint( $_POST['term_id'] ?? 0 );
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	if ( ! $term_id || '' === $name ) {
		wp_send_json_error();
	}
	$result = wp_update_term( $term_id, 'media_category', array( 'name' => $name ) );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}
	wp_send_json_success();
} );

add_action( 'wp_ajax_jbwp_media_folder_delete', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}
	$term_id = absint( $_POST['term_id'] ?? 0 );
	if ( ! $term_id ) {
		wp_send_json_error();
	}
	wp_delete_term( $term_id, 'media_category' );
	wp_send_json_success();
} );

add_action( 'wp_ajax_jbwp_media_folder_assign', function () {
	check_ajax_referer( 'dwmcd_nonce', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}
	$ids     = array_map( 'absint', (array) ( $_POST['attachment_ids'] ?? array() ) );
	$term_id = absint( $_POST['term_id'] ?? 0 );
	if ( empty( $ids ) ) {
		wp_send_json_error();
	}
	foreach ( $ids as $id ) {
		if ( $term_id ) {
			wp_set_object_terms( $id, array( $term_id ), 'media_category' );
		} else {
			wp_set_object_terms( $id, array(), 'media_category' );
		}
	}
	// Return updated counts so the sidebar can refresh without a page reload.
	$terms     = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	$counts    = array();
	$term_ids  = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$counts[ $t->slug ] = (int) $t->count;
			$term_ids[]         = $t->term_id;
		}
	}
	$total = (int) wp_count_posts( 'attachment' )->inherit;
	$uncat = $total;
	$uncat_q = new WP_Query( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array( array(
			'taxonomy' => 'media_category',
			'operator' => 'NOT EXISTS',
		) ),
	) );
	$uncat = $uncat_q->found_posts;
	wp_send_json_success( array( 'counts' => $counts, 'total' => $total, 'uncategorized' => (int) $uncat ) );
} );

// ── Inject folder filter into wp.media modal on ALL admin pages ─────────────
// This makes the folder dropdown appear when selecting images anywhere in WP
// (e.g. Elementor, page editor, plugin settings, etc.)

add_action( 'admin_footer', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) || ! taxonomy_exists( 'media_category' ) ) {
		return;
	}
	// Don't load on upload.php — the sidebar script there already handles this.
	global $pagenow;
	if ( 'upload.php' === $pagenow ) {
		return;
	}
	$terms = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}
	$folders = array();
	foreach ( $terms as $t ) {
		$folders[] = array( 'slug' => $t->slug, 'name' => $t->name );
	}
	?>
	<script>
	jQuery(function($){
		if (!window.wp || !wp.media || !wp.media.view || !wp.media.view.AttachmentFilters) return;
		var cats = <?php echo wp_json_encode( $folders ); ?>;
		var Orig = wp.media.view.AttachmentFilters.All;
		wp.media.view.AttachmentFilters.All = Orig.extend({
			createFilters: function(){
				Orig.prototype.createFilters.call(this);
				var f = this.filters;
				cats.forEach(function(c){
					f['media_category_'+c.slug] = {
						text: c.name,
						props: { media_category: c.slug },
						priority: 60
					};
				});
			}
		});
	});
	</script>
	<?php
} );

// ── Enqueue jQuery UI for drag-and-drop on media page ──────────────────────

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'upload.php' !== $hook ) {
		return;
	}
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) ) {
		return;
	}
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
} );

// ── Sidebar injection (grid + list view) ───────────────────────────────────

add_action( 'admin_footer-upload.php', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['media_categories_enabled'] ) || ! taxonomy_exists( 'media_category' ) ) {
		return;
	}
	$terms = get_terms( array( 'taxonomy' => 'media_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	// Count uncategorized
	$all_term_ids = wp_list_pluck( $terms, 'term_id' );
	$uncat_count  = 0;
	$uncat_q = new WP_Query( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array( array(
			'taxonomy' => 'media_category',
			'operator' => 'NOT EXISTS',
		) ),
	) );
	$uncat_count = $uncat_q->found_posts;
	$total_count = wp_count_posts( 'attachment' )->inherit;

	$folders_data = array();
	foreach ( $terms as $t ) {
		$folders_data[] = array( 'id' => $t->term_id, 'slug' => $t->slug, 'name' => $t->name, 'count' => (int) $t->count );
	}
	$current = isset( $_GET['media_category'] ) ? sanitize_key( $_GET['media_category'] ) : '';
	$is_grid = ! isset( $_GET['mode'] ) || 'grid' === ( $_GET['mode'] ?? '' );
	?>
	<style>
	/* ── Media folder sidebar ─────────────────────────────────────────── */
	.jbwp-media-wrap { display:flex; gap:0; min-height:calc(100vh - 100px); }
	.jbwp-media-sidebar {
		width:240px; min-width:200px; flex-shrink:0;
		background:var(--dwmcd-panel,#fff); border-right:1px solid var(--dwmcd-border,#e5e7eb);
		padding:14px 0; position:sticky; top:32px; align-self:flex-start;
		max-height:calc(100vh - 60px); overflow-y:auto;
		font-size:13px; box-shadow:1px 0 0 rgba(0,0,0,.03);
	}
	.jbwp-sidebar-header {
		display:flex; align-items:center; justify-content:space-between;
		padding:0 14px 10px; border-bottom:1px solid var(--dwmcd-border,#e5e7eb);
		margin-bottom:8px;
	}
	.jbwp-sidebar-header h3 {
		font-size:11px; font-weight:700; text-transform:uppercase;
		letter-spacing:.06em; color:var(--dwmcd-muted,#6b7280); margin:0;
	}
	.jbwp-sidebar-add-btn {
		background:none; border:none; cursor:pointer; padding:2px 4px;
		color:var(--dwmcd-muted,#6b7280); font-size:18px; line-height:1;
		border-radius:4px; transition:all .15s;
	}
	.jbwp-sidebar-add-btn:hover { color:var(--dwmcd-accent,#2952ff); background:var(--dwmcd-accent-soft,rgba(41,82,255,.08)); }
	.jbwp-folder-item {
		display:flex; align-items:center; padding:7px 14px;
		cursor:pointer; transition:all .12s; gap:8px;
		border-left:3px solid transparent; user-select:none;
		position:relative;
	}
	.jbwp-folder-item:hover { background:var(--dwmcd-hover,#f5f8ff); }
	.jbwp-folder-item.active {
		background:var(--dwmcd-accent-soft,#eef2ff);
		border-left-color:var(--dwmcd-accent,#2952ff); font-weight:600;
	}
	.jbwp-folder-item.drop-hover {
		background:#dbeafe; border-left-color:#3b82f6;
		box-shadow:inset 0 0 0 1px #93c5fd;
	}
	.jbwp-folder-icon {
		font-size:16px; color:var(--dwmcd-sidebar-muted,#9ca3af);
		flex-shrink:0; width:20px; text-align:center;
	}
	.jbwp-folder-item.active .jbwp-folder-icon { color:var(--dwmcd-accent,#2952ff); }
	.jbwp-folder-name {
		flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
		color:var(--dwmcd-sidebar-text,#35435d);
	}
	.jbwp-folder-count {
		font-size:10px; color:var(--dwmcd-sidebar-muted,#9ca3af);
		background:rgba(0,0,0,.04); padding:1px 7px; border-radius:10px;
		min-width:20px; text-align:center; font-weight:500;
		transition:all .2s;
	}
	.jbwp-folder-count.updated { background:var(--dwmcd-accent-soft,rgba(41,82,255,.1)); color:var(--dwmcd-accent,#2952ff); }
	.jbwp-folder-actions {
		opacity:0; display:flex; gap:1px; transition:opacity .15s;
		position:absolute; right:8px; top:50%; transform:translateY(-50%);
		background:var(--dwmcd-panel,#fff); border-radius:4px;
		box-shadow:0 1px 3px rgba(0,0,0,.08); padding:1px;
	}
	.jbwp-folder-item:hover .jbwp-folder-actions { opacity:1; }
	.jbwp-folder-actions button {
		background:none; border:none; cursor:pointer; padding:3px 5px;
		color:var(--dwmcd-sidebar-muted,#9ca3af); font-size:13px; line-height:1;
		border-radius:3px; transition:all .12s;
	}
	.jbwp-folder-actions button:hover { color:var(--dwmcd-accent,#2952ff); background:var(--dwmcd-accent-soft,rgba(41,82,255,.08)); }
	.jbwp-folder-actions .jbwp-delete:hover { color:#ef4444; background:rgba(239,68,68,.08); }
	.jbwp-folder-sep { height:1px; background:var(--dwmcd-border,#e5e7eb); margin:6px 14px; }
	/* Inline folder creation input */
	.jbwp-folder-create-row {
		display:flex; align-items:center; padding:4px 14px; gap:6px;
	}
	.jbwp-folder-create-input {
		flex:1; border:1px solid #93c5fd; border-radius:6px; padding:5px 10px;
		font-size:13px; outline:none; background:#fff;
		box-shadow:0 0 0 2px rgba(59,130,246,.15);
	}
	.jbwp-folder-create-input:focus { border-color:var(--dwmcd-accent,#2952ff); box-shadow:0 0 0 2px rgba(41,82,255,.2); }
	.jbwp-folder-create-actions { display:flex; gap:3px; }
	.jbwp-folder-create-actions button {
		border:none; cursor:pointer; padding:4px 6px; border-radius:4px;
		font-size:14px; line-height:1;
	}
	.jbwp-create-ok { background:var(--dwmcd-accent,#2952ff); color:#fff; }
	.jbwp-create-ok:hover { opacity:.85; }
	.jbwp-create-cancel { background:#f3f4f6; color:#6b7280; }
	.jbwp-create-cancel:hover { background:#e5e7eb; }
	.jbwp-folder-rename-input {
		border:1px solid #93c5fd; border-radius:5px; padding:3px 8px;
		font-size:13px; width:100%; outline:none;
		box-shadow:0 0 0 2px rgba(59,130,246,.15);
	}
	.jbwp-media-main { flex:1; min-width:0; }
	/* Drag helper for grid attachments */
	.jbwp-drag-helper {
		background:var(--dwmcd-accent,#2952ff); color:#fff; padding:6px 14px;
		border-radius:8px; font-size:12px; font-weight:600;
		box-shadow:0 4px 16px rgba(0,0,0,.2); z-index:99999; white-space:nowrap;
		display:flex; align-items:center; gap:6px;
	}
	.jbwp-drag-helper .dashicons { font-size:14px; width:14px; height:14px; }
	/* Toast notification */
	.jbwp-toast {
		position:fixed; bottom:24px; right:24px; z-index:100001;
		background:#1e293b; color:#fff; padding:10px 18px; border-radius:10px;
		font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(0,0,0,.2);
		opacity:0; transform:translateY(8px); transition:all .25s;
	}
	.jbwp-toast.show { opacity:1; transform:translateY(0); }
	/* Loading overlay on sidebar items */
	.jbwp-folder-item.loading::after {
		content:''; position:absolute; inset:0; background:rgba(255,255,255,.5);
		border-radius:0; pointer-events:none;
	}
	/* List view inline assign dropdown */
	.jbwp-list-assign-dropdown {
		position:absolute; z-index:100; background:#fff;
		border:1px solid var(--dwmcd-border,#e3e9f2); border-radius:8px;
		padding:6px; box-shadow:0 4px 16px rgba(0,0,0,.12);
		margin-top:4px;
	}
	.jbwp-list-assign-select {
		min-width:140px; padding:4px 8px; border:1px solid #d1d5db;
		border-radius:6px; font-size:13px; cursor:pointer;
	}
	.column-jbwp_media_folder { position:relative; }
	</style>

	<script>
	jQuery(function($){
		'use strict';
		var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
		var nonce   = '<?php echo esc_js( wp_create_nonce( 'dwmcd_nonce' ) ); ?>';
		var folders = <?php echo wp_json_encode( $folders_data ); ?>;
		var currentSlug = '<?php echo esc_js( $current ); ?>';
		var totalCount  = <?php echo (int) $total_count; ?>;
		var uncatCount  = <?php echo (int) $uncat_count; ?>;
		var isGrid      = <?php echo $is_grid ? 'true' : 'false'; ?>;

		var wrap = $('.wrap');
		if (!wrap.length) return;

		// ── Toast helper ────────────────────────────────────────────────
		var toastTimer;
		function toast(msg) {
			var t = $('.jbwp-toast');
			if (!t.length) { t = $('<div class="jbwp-toast"></div>').appendTo('body'); }
			clearTimeout(toastTimer);
			t.text(msg).addClass('show');
			toastTimer = setTimeout(function(){ t.removeClass('show'); }, 2400);
		}

		// ── Build sidebar ───────────────────────────────────────────────
		var sidebar = $('<div class="jbwp-media-sidebar"></div>');
		sidebar.append(
			'<div class="jbwp-sidebar-header">' +
				'<h3>Mappen</h3>' +
				'<button class="jbwp-sidebar-add-btn" title="Nieuwe map">+</button>' +
			'</div>'
		);

		function esc(str) { return $('<span>').text(str).html(); }

		function folderHtml(slug, name, count, id, icon) {
			var active = slug === currentSlug ? ' active' : '';
			icon = icon || (slug === '__uncategorized' ? 'portfolio' : 'category');
			var h = '<div class="jbwp-folder-item'+active+'" data-slug="'+esc(slug)+'" data-id="'+(id||0)+'">';
			h += '<span class="jbwp-folder-icon dashicons dashicons-'+icon+'"></span>';
			h += '<span class="jbwp-folder-name">'+esc(name)+'</span>';
			h += '<span class="jbwp-folder-count" data-slug="'+esc(slug)+'">'+count+'</span>';
			if (id) {
				h += '<span class="jbwp-folder-actions">';
				h += '<button class="jbwp-rename" title="Hernoemen">&#9998;</button>';
				h += '<button class="jbwp-delete" title="Verwijderen">&#10005;</button>';
				h += '</span>';
			}
			h += '</div>';
			return h;
		}

		// Built-in items
		sidebar.append(folderHtml('', 'Alle media', totalCount, 0, 'images-alt2'));
		sidebar.append(folderHtml('__uncategorized', 'Niet gecategoriseerd', uncatCount, 0, 'portfolio'));
		sidebar.append('<div class="jbwp-folder-sep"></div>');
		// User folders
		$.each(folders, function(i,f) { sidebar.append(folderHtml(f.slug, f.name, f.count, f.id)); });

		// Inject sidebar layout
		var main = $('<div class="jbwp-media-main"></div>');
		wrap.children().not('h1,.page-title-action,.subsubsub,.search-box').wrapAll(main);
		main = wrap.find('.jbwp-media-main');
		wrap.append($('<div class="jbwp-media-wrap"></div>').append(sidebar).append(main));

		// ── Update sidebar counts from server data ──────────────────────
		function updateCounts(data) {
			if (!data) return;
			// Total
			sidebar.find('.jbwp-folder-count[data-slug=""]').text(data.total).addClass('updated');
			// Uncategorized
			sidebar.find('.jbwp-folder-count[data-slug="__uncategorized"]').text(data.uncategorized).addClass('updated');
			// Per-folder
			if (data.counts) {
				$.each(data.counts, function(slug, cnt) {
					sidebar.find('.jbwp-folder-count[data-slug="'+slug+'"]').text(cnt).addClass('updated');
				});
			}
			setTimeout(function(){ sidebar.find('.jbwp-folder-count').removeClass('updated'); }, 1200);
		}

		// ── Click folder — real-time in grid, page nav in list ─────────
		sidebar.on('click', '.jbwp-folder-item', function(e) {
			if ($(e.target).closest('.jbwp-folder-actions').length) return;
			if ($(e.target).is('input')) return;
			var slug = $(this).data('slug');

			// In grid mode, filter via wp.media backbone (no reload)
			if (isGrid && window.wp && wp.media && wp.media.frame) {
				sidebar.find('.jbwp-folder-item').removeClass('active');
				$(this).addClass('active');
				currentSlug = slug;
				var props = wp.media.frame.content.get().collection.props;
				if (slug) {
					props.set({ media_category: slug });
				} else {
					props.unset('media_category');
				}
				// Update URL without reload
				var url = new URL(window.location);
				if (slug) { url.searchParams.set('media_category', slug); }
				else { url.searchParams.delete('media_category'); }
				window.history.replaceState({}, '', url.toString());
				return;
			}

			// List view: navigate
			var url = new URL(window.location);
			if (slug) { url.searchParams.set('media_category', slug); }
			else { url.searchParams.delete('media_category'); }
			url.searchParams.delete('paged');
			window.location = url.toString();
		});

		// ── Inline folder creation ──────────────────────────────────────
		function showCreateInput() {
			if (sidebar.find('.jbwp-folder-create-row').length) return;
			var row = $(
				'<div class="jbwp-folder-create-row">' +
					'<input class="jbwp-folder-create-input" type="text" placeholder="Mapnaam…" maxlength="50">' +
					'<div class="jbwp-folder-create-actions">' +
						'<button class="jbwp-create-ok" title="Aanmaken">&#10003;</button>' +
						'<button class="jbwp-create-cancel" title="Annuleren">&#10005;</button>' +
					'</div>' +
				'</div>'
			);
			sidebar.append(row);
			var input = row.find('input');
			input.focus();

			function doCreate() {
				var name = input.val().trim();
				if (!name) { row.remove(); return; }
				input.prop('disabled', true);
				$.post(ajaxurl, { action:'jbwp_media_folder_create', nonce:nonce, name:name }, function(r) {
					row.remove();
					if (r.success) {
						var f = r.data;
						folders.push(f);
						var last = sidebar.find('.jbwp-folder-item').last();
						$(folderHtml(f.slug, f.name, 0, f.id)).insertAfter(last);
						initDroppable();
						toast('Map "'+f.name+'" aangemaakt');
					} else {
						toast(r.data && r.data.message ? r.data.message : 'Fout bij aanmaken');
					}
				}).fail(function(){ row.remove(); toast('Netwerkfout'); });
			}

			input.on('keydown', function(ev) {
				if (ev.key === 'Enter') { ev.preventDefault(); doCreate(); }
				if (ev.key === 'Escape') { row.remove(); }
			});
			row.find('.jbwp-create-ok').on('click', doCreate);
			row.find('.jbwp-create-cancel').on('click', function(){ row.remove(); });
		}

		sidebar.on('click', '.jbwp-sidebar-add-btn', function(e) {
			e.stopPropagation();
			showCreateInput();
		});

		// ── Rename folder (inline) ──────────────────────────────────────
		sidebar.on('click', '.jbwp-rename', function(e) {
			e.stopPropagation();
			var item = $(this).closest('.jbwp-folder-item');
			var nameEl = item.find('.jbwp-folder-name');
			var old = nameEl.text();
			var input = $('<input class="jbwp-folder-rename-input" type="text" maxlength="50">').val(old);
			nameEl.replaceWith(input);
			input.focus().select();

			function save() {
				var val = input.val().trim();
				if (!val) val = old;
				if (val !== old) {
					$.post(ajaxurl, { action:'jbwp_media_folder_rename', nonce:nonce, term_id:item.data('id'), name:val }, function(r) {
						if (r.success) { toast('Map hernoemd'); }
					});
					// Update local folders array
					$.each(folders, function(i,f) {
						if (f.id == item.data('id')) { f.name = val; return false; }
					});
				}
				input.replaceWith('<span class="jbwp-folder-name">'+esc(val)+'</span>');
			}
			input.on('blur', save).on('keydown', function(ev) {
				if (ev.key === 'Enter') { ev.preventDefault(); input.off('blur'); save(); }
				if (ev.key === 'Escape') { input.off('blur'); input.replaceWith('<span class="jbwp-folder-name">'+esc(old)+'</span>'); }
			});
		});

		// ── Delete folder ───────────────────────────────────────────────
		sidebar.on('click', '.jbwp-delete', function(e) {
			e.stopPropagation();
			var item = $(this).closest('.jbwp-folder-item');
			var name = item.find('.jbwp-folder-name').text();
			if (!confirm('Map "'+name+'" verwijderen?\nBestanden blijven behouden.')) return;
			item.addClass('loading');
			$.post(ajaxurl, { action:'jbwp_media_folder_delete', nonce:nonce, term_id:item.data('id') }, function(r) {
				if (r.success) {
					var slug = item.data('slug');
					folders = folders.filter(function(f){ return f.slug !== slug; });
					item.slideUp(200, function(){ item.remove(); });
					toast('Map "'+name+'" verwijderd');
					// If we deleted the active folder, show all
					if (item.hasClass('active')) {
						sidebar.find('.jbwp-folder-item').first().trigger('click');
					}
				}
			}).fail(function(){ item.removeClass('loading'); });
		});

		// ── Drag-and-drop ───────────────────────────────────────────────
		function initDraggable() {
			var items = $('.attachments-browser .attachment, .wp-list-table .type-attachment');
			if (!items.length) return;
			items.not('.ui-draggable').draggable({
				helper: function() {
					var sel = $('.attachments-browser .attachment.selected');
					var count = sel.length || 1;
					return $('<div class="jbwp-drag-helper">')
						.html('<span class="dashicons dashicons-move"></span> ' + count + ' bestand' + (count > 1 ? 'en' : ''));
				},
				appendTo: 'body',
				cursor: 'grabbing',
				distance: 8,
				zIndex: 99999,
				revert: 'invalid',
				cursorAt: { left:24, top:14 },
				start: function() { sidebar.find('.jbwp-folder-item[data-id]').addClass('drop-ready'); },
				stop: function() { sidebar.find('.jbwp-folder-item').removeClass('drop-ready'); }
			});
		}

		function initDroppable() {
			sidebar.find('.jbwp-folder-item[data-id]').not('.ui-droppable').droppable({
				hoverClass: 'drop-hover',
				tolerance: 'pointer',
				drop: function(event, ui) {
					var termId = $(this).data('id');
					var targetItem = $(this);
					var ids = [];

					var sel = $('.attachments-browser .attachment.selected');
					if (sel.length) {
						sel.each(function(){ ids.push($(this).data('id')); });
					} else {
						var id = ui.draggable.data('id');
						if (id) ids.push(id);
					}
					if (!ids.length) return;

					targetItem.addClass('loading');
					$.post(ajaxurl, {
						action: 'jbwp_media_folder_assign',
						nonce: nonce,
						attachment_ids: ids,
						term_id: termId
					}, function(r) {
						targetItem.removeClass('loading');
						if (r.success) {
							updateCounts(r.data);
							toast(ids.length + ' bestand' + (ids.length > 1 ? 'en' : '') + ' verplaatst');
							// In grid mode, refresh the collection
							if (isGrid && window.wp && wp.media && wp.media.frame) {
								wp.media.frame.content.get().collection.props.set({ jbwp_refresh: Date.now() });
								wp.media.frame.content.get().collection.props.unset('jbwp_refresh');
							}
						}
					});
				}
			});

			// Also make "Niet gecategoriseerd" droppable (removes all categories)
			sidebar.find('.jbwp-folder-item[data-slug="__uncategorized"]').not('.ui-droppable').droppable({
				hoverClass: 'drop-hover',
				tolerance: 'pointer',
				drop: function(event, ui) {
					var targetItem = $(this);
					var ids = [];
					var sel = $('.attachments-browser .attachment.selected');
					if (sel.length) {
						sel.each(function(){ ids.push($(this).data('id')); });
					} else {
						var id = ui.draggable.data('id');
						if (id) ids.push(id);
					}
					if (!ids.length) return;
					targetItem.addClass('loading');
					$.post(ajaxurl, {
						action: 'jbwp_media_folder_assign',
						nonce: nonce,
						attachment_ids: ids,
						term_id: 0
					}, function(r) {
						targetItem.removeClass('loading');
						if (r.success) {
							updateCounts(r.data);
							toast(ids.length + ' bestand' + (ids.length > 1 ? 'en' : '') + ' uit map gehaald');
							if (isGrid && window.wp && wp.media && wp.media.frame) {
								wp.media.frame.content.get().collection.props.set({ jbwp_refresh: Date.now() });
								wp.media.frame.content.get().collection.props.unset('jbwp_refresh');
							}
						}
					});
				}
			});
		}

		// Init drag-drop after grid renders (async)
		setTimeout(function(){ initDraggable(); initDroppable(); }, 500);
		$(document).ajaxComplete(function(){ setTimeout(initDraggable, 300); });

		// ── Grid view: inject folder filter into wp.media backbone ──────
		if (window.wp && wp.media && wp.media.view && wp.media.view.AttachmentFilters) {
			var cats = [{slug:'',name:'Alle mappen'},{slug:'__uncategorized',name:'Niet gecategoriseerd'}];
			$.each(folders, function(i,f){ cats.push({slug:f.slug,name:f.name}); });
			var OrigFilters = wp.media.view.AttachmentFilters.All;
			wp.media.view.AttachmentFilters.All = OrigFilters.extend({
				createFilters: function(){
					OrigFilters.prototype.createFilters.call(this);
					var self = this;
					$.each(cats, function(i,c) {
						if (!c.slug) return;
						self.filters['media_category_'+c.slug] = {
							text: c.name,
							props: { media_category: c.slug },
							priority: 60
						};
					});
				}
			});
		}

		// ── Apply initial filter if URL has media_category param ─────────
		if (isGrid && currentSlug && window.wp && wp.media) {
			wp.media.ready(function() {
				setTimeout(function() {
					if (wp.media.frame && wp.media.frame.content && wp.media.frame.content.get()) {
						wp.media.frame.content.get().collection.props.set({ media_category: currentSlug });
					}
				}, 200);
			});
		}

		// ── List view: inline folder assignment ─────────────────────────
		if (!isGrid) {
			$(document).on('click', '.jbwp-list-assign-btn', function(e) {
				e.preventDefault();
				var btn = $(this);
				var id = btn.data('id');
				var dd = $('.jbwp-list-assign-dropdown[data-id="'+id+'"]');
				// Hide any other open dropdowns
				$('.jbwp-list-assign-dropdown').not(dd).hide();
				dd.toggle();
				if (dd.is(':visible')) {
					dd.find('select').focus();
				}
			});

			$(document).on('change', '.jbwp-list-assign-select', function() {
				var sel = $(this);
				var attachId = sel.data('id');
				var termId = parseInt(sel.val(), 10);
				var dd = sel.closest('.jbwp-list-assign-dropdown');
				dd.hide();

				$.post(ajaxurl, {
					action: 'jbwp_media_folder_assign',
					nonce: nonce,
					attachment_ids: [attachId],
					term_id: termId
				}, function(r) {
					if (r.success) {
						toast('Map toegewezen');
						// Reload to update the column
						window.location.reload();
					}
				});
			});

			// Close dropdown when clicking outside
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.jbwp-list-assign-btn, .jbwp-list-assign-dropdown').length) {
					$('.jbwp-list-assign-dropdown').hide();
				}
			});
		}
	});
	</script>
	<?php
} );


// ══════════════════════════════════════════════════════════════════════════════
// ── MODULE: Media Replacement ───────────────────────────────────────────────
// ══════════════════════════════════════════════════════════════════════════════
//
// Adds a "Replace media" link in the media library list view row actions and
// a button in the attachment edit/modal view. Uploads a new file, swaps it
// into the existing attachment record (same ID, same post_date), regenerates
// thumbnails metadata, and — if the filename hasn't changed — preserves the
// exact same URL so no links break.

// Row action in list view
add_filter( 'media_row_actions', function ( $actions, $post ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_replacement_enabled'] ) ) {
		return $actions;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		return $actions;
	}
	$url = admin_url( 'upload.php?page=jbwp-replace-media&attachment_id=' . $post->ID );
	$actions['jbwp_replace'] = '<a href="' . esc_url( $url ) . '">Vervang bestand</a>';
	return $actions;
}, 10, 2 );

// Register the replacement page (hidden from menu)
add_action( 'admin_menu', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['media_replacement_enabled'] ) ) {
		return;
	}
	add_submenu_page(
		null, // hidden from menu
		'Vervang mediabestand',
		'Vervang mediabestand',
		'upload_files',
		'jbwp-replace-media',
		'jbwp_replace_media_page'
	);
} );

function jbwp_replace_media_page() {
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_die( 'Geen toegang.' );
	}
	$attachment_id = isset( $_GET['attachment_id'] ) ? absint( $_GET['attachment_id'] ) : 0;
	if ( ! $attachment_id || ! get_post( $attachment_id ) ) {
		wp_die( 'Ongeldig mediabestand.' );
	}
	$attachment = get_post( $attachment_id );
	$filename   = basename( get_attached_file( $attachment_id ) );
	$thumb_url  = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

	// Handle upload
	if ( ! empty( $_FILES['jbwp_replacement_file']['name'] ) ) {
		check_admin_referer( 'jbwp_replace_media_' . $attachment_id );
		$result = jbwp_do_replace_media( $attachment_id );
		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>Bestand succesvol vervangen!</p></div>';
			// Refresh data
			$filename  = basename( get_attached_file( $attachment_id ) );
			$thumb_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
		}
	}
	?>
	<div class="wrap">
		<h1>Vervang mediabestand</h1>
		<div class="dwmcd-card" style="max-width:600px;margin-top:20px;background:#fff;padding:24px;border:1px solid #ddd;border-radius:8px">
			<h2 style="margin-top:0">Huidig bestand</h2>
			<div style="display:flex;gap:16px;align-items:center;margin-bottom:20px">
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #eee">
				<?php endif; ?>
				<div>
					<strong><?php echo esc_html( $attachment->post_title ); ?></strong><br>
					<code style="font-size:12px"><?php echo esc_html( $filename ); ?></code><br>
					<small class="description"><?php echo esc_html( get_post_mime_type( $attachment_id ) ); ?></small>
				</div>
			</div>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'jbwp_replace_media_' . $attachment_id ); ?>
				<div style="margin-bottom:16px">
					<label for="jbwp_replacement_file"><strong>Nieuw bestand uploaden</strong></label><br>
					<input type="file" name="jbwp_replacement_file" id="jbwp_replacement_file" required style="margin-top:6px">
					<p class="description">Het bestaande media-ID, publicatiedatum en alle interne links blijven behouden.</p>
				</div>
				<button type="submit" class="button button-primary">Bestand vervangen</button>
				<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" class="button">Annuleren</a>
			</form>
		</div>
	</div>
	<?php
}

function jbwp_do_replace_media( $attachment_id ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$file = $_FILES['jbwp_replacement_file'] ?? null;
	if ( ! $file || empty( $file['tmp_name'] ) ) {
		return new WP_Error( 'no_file', 'Geen bestand geüpload.' );
	}

	// Validate file type
	$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
	if ( ! $filetype['type'] ) {
		return new WP_Error( 'invalid_type', 'Dit bestandstype is niet toegestaan.' );
	}

	$old_file = get_attached_file( $attachment_id );
	$upload_dir = wp_upload_dir();

	// Delete old file and its thumbnails
	$old_meta = wp_get_attachment_metadata( $attachment_id );
	if ( is_array( $old_meta ) && ! empty( $old_meta['sizes'] ) ) {
		$old_dir = dirname( $old_file );
		foreach ( $old_meta['sizes'] as $size_data ) {
			$size_file = $old_dir . '/' . $size_data['file'];
			if ( file_exists( $size_file ) ) {
				wp_delete_file( $size_file );
			}
		}
	}
	if ( file_exists( $old_file ) ) {
		wp_delete_file( $old_file );
	}

	// Move new file to same directory
	$target_dir = dirname( $old_file );
	if ( ! file_exists( $target_dir ) ) {
		wp_mkdir_p( $target_dir );
	}
	$new_filename = wp_unique_filename( $target_dir, $file['name'] );
	$new_file     = $target_dir . '/' . $new_filename;

	if ( ! move_uploaded_file( $file['tmp_name'], $new_file ) ) {
		return new WP_Error( 'move_failed', 'Bestand kon niet worden verplaatst.' );
	}

	// Update attachment record
	$relative = str_replace( $upload_dir['basedir'] . '/', '', $new_file );
	update_attached_file( $attachment_id, $relative );

	$new_mime = $filetype['type'];
	wp_update_post( array(
		'ID'             => $attachment_id,
		'post_mime_type' => $new_mime,
	) );

	// Regenerate metadata (thumbnails, dimensions, etc.)
	if ( wp_attachment_is_image( $attachment_id ) ) {
		$metadata = wp_generate_attachment_metadata( $attachment_id, $new_file );
		wp_update_attachment_metadata( $attachment_id, $metadata );
	} else {
		wp_update_attachment_metadata( $attachment_id, array() );
	}

	return true;
}

// Add "Replace" button in attachment edit modal (grid view)
add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
	$s = jbwp_get_settings();
	if ( empty( $s['media_replacement_enabled'] ) ) {
		return $fields;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		return $fields;
	}
	$url = admin_url( 'upload.php?page=jbwp-replace-media&attachment_id=' . $post->ID );
	$fields['jbwp_replace'] = array(
		'label' => '',
		'input' => 'html',
		'html'  => '<a href="' . esc_url( $url ) . '" class="button button-small" target="_blank" style="margin-top:4px"><span class="dashicons dashicons-update" style="vertical-align:middle;margin-right:4px;font-size:16px;line-height:24px"></span>Vervang bestand</a>',
	);
	return $fields;
}, 20, 2 );


// ══════════════════════════════════════════════════════════════════════════════
// ── MODULE: Local User Avatar ───────────────────────────────────────────────
// ══════════════════════════════════════════════════════════════════════════════
//
// Lets users pick any image from the WP Media Library as their avatar.
// The attachment ID is stored in user meta `jbwp_user_avatar`. We hook
// `pre_get_avatar_data` to serve the local image instead of Gravatar.

// Filter avatar data to use local image
add_filter( 'pre_get_avatar_data', function ( $args, $id_or_email ) {
	$s = jbwp_get_settings();
	if ( empty( $s['local_avatar_enabled'] ) ) {
		return $args;
	}

	$user_id = 0;
	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		if ( $user ) {
			$user_id = $user->ID;
		}
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = (int) $id_or_email->post_author;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		if ( $id_or_email->user_id ) {
			$user_id = (int) $id_or_email->user_id;
		}
	}

	if ( ! $user_id ) {
		return $args;
	}

	$avatar_id = (int) get_user_meta( $user_id, 'jbwp_user_avatar', true );
	if ( ! $avatar_id ) {
		return $args;
	}

	$url = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );
	if ( ! $url ) {
		return $args;
	}

	$args['url']          = $url;
	$args['found_avatar'] = true;

	return $args;
}, 10, 2 );

// Add avatar picker to user profile
add_action( 'show_user_profile', 'jbwp_user_avatar_field' );
add_action( 'edit_user_profile', 'jbwp_user_avatar_field' );

function jbwp_user_avatar_field( $user ) {
	$s = jbwp_get_settings();
	if ( empty( $s['local_avatar_enabled'] ) ) {
		return;
	}
	$avatar_id  = (int) get_user_meta( $user->ID, 'jbwp_user_avatar', true );
	$avatar_url = $avatar_id ? wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) : '';
	wp_enqueue_media();
	?>
	<h2>Profielfoto</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label>Avatar</label></th>
			<td>
				<div id="jbwp-avatar-preview" style="margin-bottom:10px">
					<?php if ( $avatar_url ) : ?>
						<img src="<?php echo esc_url( $avatar_url ); ?>" style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:2px solid #ddd">
					<?php else : ?>
						<?php echo get_avatar( $user->ID, 96 ); ?>
					<?php endif; ?>
				</div>
				<input type="hidden" name="jbwp_user_avatar" id="jbwp-user-avatar-id" value="<?php echo esc_attr( $avatar_id ); ?>">
				<button type="button" class="button" id="jbwp-avatar-select">Afbeelding kiezen</button>
				<?php if ( $avatar_id ) : ?>
					<button type="button" class="button" id="jbwp-avatar-remove" style="color:#d63638">Verwijderen</button>
				<?php else : ?>
					<button type="button" class="button" id="jbwp-avatar-remove" style="color:#d63638;display:none">Verwijderen</button>
				<?php endif; ?>
				<p class="description">Kies een afbeelding uit de Mediabibliotheek als je avatar. Als er geen afbeelding is gekozen wordt Gravatar gebruikt.</p>
				<script>
				jQuery(function($){
					var frame;
					$('#jbwp-avatar-select').on('click', function(e){
						e.preventDefault();
						if (frame) { frame.open(); return; }
						frame = wp.media({
							title: 'Kies avatar',
							button: { text: 'Gebruik als avatar' },
							multiple: false,
							library: { type: 'image' }
						});
						frame.on('select', function(){
							var a = frame.state().get('selection').first().toJSON();
							var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
							$('#jbwp-user-avatar-id').val(a.id);
							$('#jbwp-avatar-preview').html('<img src="'+url+'" style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:2px solid #ddd">');
							$('#jbwp-avatar-remove').show();
						});
						frame.open();
					});
					$('#jbwp-avatar-remove').on('click', function(e){
						e.preventDefault();
						$('#jbwp-user-avatar-id').val('');
						$('#jbwp-avatar-preview').html('<?php echo esc_js( get_avatar( $user->ID, 96 ) ); ?>');
						$(this).hide();
					});
				});
				</script>
			</td>
		</tr>
	</table>
	<?php
}

// Save avatar on profile update
add_action( 'personal_options_update', 'jbwp_save_user_avatar' );
add_action( 'edit_user_profile_update', 'jbwp_save_user_avatar' );

function jbwp_save_user_avatar( $user_id ) {
	$s = jbwp_get_settings();
	if ( empty( $s['local_avatar_enabled'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( ! isset( $_POST['jbwp_user_avatar'] ) ) {
		return;
	}
	$avatar_id = absint( $_POST['jbwp_user_avatar'] );
	if ( $avatar_id ) {
		update_user_meta( $user_id, 'jbwp_user_avatar', $avatar_id );
	} else {
		delete_user_meta( $user_id, 'jbwp_user_avatar' );
	}
}


// ── Roadmap / Coming Soon page ──────────────────────────────────────────────

function jbwp_roadmap_features() {
	return array(
		'security' => array(
			'label' => 'Beveiliging & Toegang',
			'icon'  => 'shield-alt',
			'color' => '#ef4444',
			'items' => array(
				array(
					'title'       => 'Change Login URL',
					'description' => 'Maak de login-URL veiliger en beter onthoudbaar door deze aan te passen.',
					'icon'        => 'admin-network',
					'bullets'     => array(
						'Aangepaste login-URL instellen',
						'Automatische redirect bij toegang tot standaard login-URL\'s zoals /wp-login.php',
					),
				),
				array(
					'title'       => 'Password Protection',
					'description' => 'Beveilig de gehele website met een wachtwoord om content te verbergen voor publiek en zoekmachines.',
					'icon'        => 'lock',
					'bullets'     => array(
						'IP-whitelisting',
						'Optie om voorkeurs-header voor IP-adres in te stellen',
						'Bypass via URL-parameter',
						'Automatisch design overnemen van de Login Page Customizer module',
						'Ingelogde beheerders behouden volledige toegang',
					),
				),
				array(
					'title'       => 'Maintenance Mode',
					'description' => 'Toon een aanpasbare onderhoudspagina op de frontend terwijl je aan de website werkt.',
					'icon'        => 'admin-tools',
					'bullets'     => array(
						'Aanpasbare paginatitel in de browsertab',
						'WYSIWYG-editor voor heading en beschrijving',
						'Afbeelding of effen kleur als achtergrond',
						'Optie om custom CSS toe te voegen',
						'Optie om een bestaande pagina als onderhoudspagina te gebruiken',
						'Ingelogde beheerders kunnen de site gewoon bekijken',
					),
				),
			),
		),
		'code' => array(
			'label' => 'Code & Snippets',
			'icon'  => 'editor-code',
			'color' => '#2952ff',
			'items' => array(
				array(
					'title'       => 'Code Snippets Manager',
					'description' => 'Voeg eenvoudig CSS/SCSS, JS, HTML en PHP code snippets toe om je site aan te passen.',
					'icon'        => 'editor-code',
					'bullets'     => array(
						'Code editor met syntax highlighting, code folding, zoek-en-vervang en fullscreen modus',
						'Code-revisies bijhouden',
						'Flexibel instellen hoe, waar en wanneer snippets worden geladen',
						'Automatische Safe Mode als een PHP snippet een fatal error veroorzaakt',
					),
				),
				array(
					'title'       => 'Insert <head>, <body> en <footer> Code',
					'description' => 'Plaats eenvoudig extra code in de head, body of footer van je website.',
					'icon'        => 'html',
					'bullets'     => array(
						'<meta>, <link>, <script> en <style> tags invoegen',
						'Google Analytics, Tag Manager, AdSense, Ads Conversion en Optimize code',
						'Facebook, TikTok en Twitter pixels',
					),
				),
				array(
					'title'       => 'Redirect Manager',
					'description' => 'Beheer eenvoudig verschillende soorten redirects en redirections.',
					'icon'        => 'randomize',
					'bullets'     => array(
						'Wildcards en regular expressions (regex) ondersteuning',
						'3xx HTTP statuscodes (301, 302, 303, 304, 307, 308)',
						'Foutmeldingen met 4xx (400, 401, 403, 404, 410) en 5xx (500, 501, 503) statuscodes',
						'Aanpasbare foutmeldingen',
						'Optie om URL/query-parameters niet door te geven',
						'Groepen voor overzichtelijke organisatie',
						'Notities per redirect voor context',
						'Gebouwd met performance in gedachten via Transients API caching',
						'Loop-detectie om oneindige redirects te voorkomen',
					),
				),
			),
		),
		'webshop' => array(
			'label' => 'Webshop & WooCommerce',
			'icon'  => 'cart',
			'color' => '#10b981',
			'items' => array(
				array(
					'title'       => 'Bestellingen die aandacht nodig hebben',
					'description' => 'Een widget die direct op het dashboard toont welke bestellingen je nog moet verwerken.',
					'icon'        => 'list-view',
					'bullets'     => array(
						'Bestellingen met status "te verwerken" en "in afwachting"',
						'Recente bestellingen lijst met klantnaam, bedrag en status badge',
						'Refund-aanvragen die aandacht nodig hebben',
						'Direct doorklikken naar de bestelling',
					),
				),
				array(
					'title'       => 'Top performers',
					'description' => 'Inzicht in welke producten goed (en slecht) presteren.',
					'icon'        => 'awards',
					'bullets'     => array(
						'Best verkopende producten van deze maand',
						'Slechtst presterende producten die mogelijk promotie nodig hebben',
						'Hoogste recente bestellingen',
						'Percentage terugkerende klanten',
					),
				),
				array(
					'title'       => 'Reviews & feedback overzicht',
					'description' => 'Beheer productbeoordelingen direct vanuit het dashboard.',
					'icon'        => 'star-filled',
					'bullets'     => array(
						'Aantal reviews dat moderatie nodig heeft',
						'Gemiddelde rating over alle producten',
						'Recente reviews met sterren en quote',
					),
				),
				array(
					'title'       => 'Marketing & promoties',
					'description' => 'Overzicht van actieve kortingsbonnen en marketingacties.',
					'icon'        => 'tickets-alt',
					'bullets'     => array(
						'Actieve kortingsbonnen met geldigheidsperiode',
						'Coupon performance: hoe vaak gebruikt en omzet',
						'Verlaten winkelwagens (vereist extra plugin)',
						'Snelle link naar e-mail customizer',
					),
				),
				array(
					'title'       => 'Checkout health check',
					'description' => 'Controleer of je checkout volledig werkt en geconfigureerd is.',
					'icon'        => 'shield',
					'bullets'     => array(
						'iDEAL en andere betaalmethoden test',
						'SSL-certificaat geldigheid',
						'Test-mode waarschuwing',
						'Verzendmethoden status',
					),
				),
				array(
					'title'       => 'PostNL / DHL koppeling',
					'description' => 'Tracking-overzicht als deze plugins actief zijn.',
					'icon'        => 'location',
					'bullets'     => array(
						'Verzonden pakketten met tracking-status',
						'Direct labels printen vanuit het dashboard',
					),
				),
				array(
					'title'       => 'BTW overzicht',
					'description' => 'Handig overzicht van BTW per periode rondom aangifte-tijd.',
					'icon'        => 'media-spreadsheet',
					'bullets'     => array(
						'BTW per kwartaal of maand',
						'Onderverdeling per BTW-tarief',
						'Export voor de boekhouder',
					),
				),
				array(
					'title'       => 'Voorraadwaarde dashboard',
					'description' => 'Inzicht in de totale waarde van je inventaris.',
					'icon'        => 'chart-pie',
					'bullets'     => array(
						'Totale voorraadwaarde over alle producten',
						'Per productcategorie uitgesplitst',
						'Trends over tijd',
					),
				),
			),
		),
		// Media & Gebruikers — verwijderd uit roadmap, gebouwd in v4.1.0
	);
}

function jbwp_render_roadmap() {
	if ( ! current_user_can( DWMCD_CAP ) ) {
		wp_die( esc_html__( 'U heeft geen toegang tot deze pagina.' ) );
	}

	$sections = jbwp_roadmap_features();
	$total    = 0;
	foreach ( $sections as $sec ) {
		$total += count( $sec['items'] );
	}
	?>
	<div class="wrap dwmcd-custom-dashboard dwmcd-roadmap-wrap">

		<div class="dwmcd-dashboard-hero">
			<div class="dwmcd-hero-content">
				<span class="dwmcd-eyebrow">Roadmap</span>
				<h1>Binnenkort beschikbaar</h1>
				<p>Ontdek welke functies er op de planning staan. Deze features worden stap voor stap toegevoegd aan de plugin.</p>
			</div>
		</div>

		<div class="dwmcd-roadmap-stats">
			<?php foreach ( $sections as $sec ) : ?>
				<div class="dwmcd-roadmap-stat">
					<span class="dwmcd-roadmap-stat-num"><?php echo count( $sec['items'] ); ?></span>
					<span class="dwmcd-roadmap-stat-label"><?php echo esc_html( $sec['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
			<div class="dwmcd-roadmap-stat">
				<span class="dwmcd-roadmap-stat-num"><?php echo esc_html( $total ); ?></span>
				<span class="dwmcd-roadmap-stat-label">Totaal</span>
			</div>
		</div>

		<?php foreach ( $sections as $key => $section ) : ?>
			<section class="dwmcd-section-block">
				<div class="dwmcd-roadmap-section-header">
					<span class="dwmcd-roadmap-badge" style="background: <?php echo esc_attr( $section['color'] ); ?>15; color: <?php echo esc_attr( $section['color'] ); ?>; border-color: <?php echo esc_attr( $section['color'] ); ?>30;">
						<span class="dashicons dashicons-<?php echo esc_attr( $section['icon'] ); ?>"></span>
						<?php echo esc_html( $section['label'] ); ?>
					</span>
					<span class="dwmcd-roadmap-count"><?php echo count( $section['items'] ); ?> feature<?php echo count( $section['items'] ) !== 1 ? 's' : ''; ?></span>
				</div>
				<div class="dwmcd-roadmap-grid">
					<?php foreach ( $section['items'] as $feature ) : ?>
						<div class="dwmcd-roadmap-card">
							<div class="dwmcd-roadmap-card-icon" style="background: <?php echo esc_attr( $section['color'] ); ?>12; color: <?php echo esc_attr( $section['color'] ); ?>;">
								<span class="dashicons dashicons-<?php echo esc_attr( $feature['icon'] ); ?>"></span>
							</div>
							<div class="dwmcd-roadmap-card-body">
								<strong><?php echo esc_html( $feature['title'] ); ?></strong>
								<p><?php echo esc_html( $feature['description'] ); ?></p>
								<?php if ( ! empty( $feature['bullets'] ) ) : ?>
									<ul class="dwmcd-roadmap-bullets">
										<?php foreach ( $feature['bullets'] as $bullet ) : ?>
											<li><?php echo esc_html( $bullet ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>

		<div class="dwmcd-roadmap-footer">
			<span class="dashicons dashicons-info"></span>
			<p>Deze roadmap geeft een indicatie van geplande features. Prioriteiten kunnen wijzigen op basis van feedback en vraag. Versie <?php echo esc_html( DWMCD_VERSION ); ?>.</p>
		</div>

	</div>
	<?php
}
