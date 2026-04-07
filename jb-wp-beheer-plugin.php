<?php
/**
 * Plugin Name:       JB WP Beheer Plugin
 * Plugin URI:        https://github.com/joshuabink/jb-wp-beheer-plugin
 * Description:       Professioneel klantdashboard voor WordPress websites.
 * Version:           4.0.1
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
define( 'JBWP_PLUGIN_VERSION', '4.0.1' );
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

	if ( ! empty( $settings['ga4_enabled'] ) && ! empty( $settings['ga4_property_id'] ) ) {
		echo '<section class="dwmcd-section-block">';
		echo '<h2>Website statistieken</h2>';
		echo '<div class="dwmcd-ga4-card">';
		$cached = get_transient( 'dwmcd_ga4_data' );
		if ( false !== $cached ) {
			echo jbwp_ga4_render_widget( $cached );
		} else {
			echo '<div class="dwmcd-ga4-loading" id="dwmcd-ga4-widget">';
			echo '<span class="spinner is-active" style="float:none;margin:0"></span>';
			echo '<span>Analytics laden&hellip;</span>';
			echo '</div>';
		}
		echo '</div>';
		echo '</section>';
	}

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

						<div style="margin-top:16px">
							<button type="button" class="button button-secondary" id="dwmcd-add-group">
								<span class="dashicons dashicons-plus-alt2"></span> Nieuwe groep toevoegen
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
					<div class="dwmcd-card">
						<h2>Google Analytics (GA4)</h2>
						<p class="dwmcd-muted" style="margin-bottom:16px">Koppel Google Analytics om bezoekersstatistieken direct op het klantdashboard te tonen — net als Google Site Kit.</p>
						<div class="dwmcd-switches" style="margin-bottom:16px">
							<label><input type="checkbox" name="dwmcd_settings[ga4_enabled]" value="1" <?php checked( ! empty( $settings['ga4_enabled'] ) ); ?>> Analytics widget inschakelen</label>
						</div>
						<div class="dwmcd-grid two">
							<div class="dwmcd-field">
								<label>GA4 Property ID</label>
								<input type="text" name="dwmcd_settings[ga4_property_id]"
									value="<?php echo esc_attr( $settings['ga4_property_id'] ); ?>"
									placeholder="bijv. 123456789">
								<small class="dwmcd-help">Terug te vinden in Google Analytics &rarr; Beheer &rarr; Property-instellingen.</small>
							</div>
						</div>
						<div class="dwmcd-field" style="margin-top:14px">
							<label>Service Account JSON</label>
							<?php if ( defined( 'DWMCD_GA4_JSON' ) && '' !== DWMCD_GA4_JSON ) : ?>
								<div class="dwmcd-info-box" style="background:#f0fdf4;border-color:#bbf7d0">
									<span class="dashicons dashicons-yes-alt" style="color:#16a34a"></span>
									<p>Service account is veilig geconfigureerd via <code>wp-config.php</code> (<code>DWMCD_GA4_JSON</code>). Dit veld is uitgeschakeld.</p>
								</div>
								<input type="hidden" name="dwmcd_settings[ga4_service_account]" value="">
							<?php else : ?>
								<textarea name="dwmcd_settings[ga4_service_account]" rows="6"
									placeholder='Plak hier de inhoud van uw service account JSON-sleutelbestand...'
									style="font-family:monospace;font-size:12px"
									aria-describedby="dwmcd-ga4-json-help"><?php echo esc_textarea( $settings['ga4_service_account'] ); ?></textarea>
								<small class="dwmcd-help" id="dwmcd-ga4-json-help">Maak een service account aan in Google Cloud Console, geef het <strong>Viewer</strong>-toegang tot uw GA4-property en download het JSON-sleutelbestand.</small>
								<div class="dwmcd-info-box" style="margin-top:10px;background:#fffbeb;border-color:#fde68a">
									<span class="dashicons dashicons-shield" style="color:#d97706"></span>
									<p>Voor betere beveiliging: definieer <code>DWMCD_GA4_JSON</code> in <code>wp-config.php</code> in plaats van de JSON hier te plakken.</p>
								</div>
							<?php endif; ?>
						</div>
						<div style="margin-top:14px;display:flex;align-items:center;gap:12px">
							<button type="button" class="button button-secondary" id="dwmcd-ga4-test">
								<span class="dashicons dashicons-update"></span> Verbinding testen
							</button>
							<button type="button" class="button button-secondary" id="dwmcd-ga4-refresh">
								<span class="dashicons dashicons-database-remove"></span> Cache wissen
							</button>
							<span id="dwmcd-ga4-status" class="dwmcd-ga4-status"></span>
						</div>
						<?php if ( ! function_exists( 'openssl_sign' ) ) : ?>
						<div class="dwmcd-info-box" style="margin-top:14px;background:#fff8f0;border-color:#fde68a">
							<span class="dashicons dashicons-warning" style="color:#d97706"></span>
							<p>De <code>openssl</code> PHP-extensie is niet beschikbaar op deze server. GA4-authenticatie vereist openssl. Neem contact op met uw hostingprovider.</p>
						</div>
						<?php endif; ?>
					</div>

					<div class="dwmcd-card">
						<h2>Hoe in te stellen</h2>
						<ol style="margin:0;padding-left:20px;line-height:2;color:var(--dwmcd-muted);font-size:13.5px">
							<li>Ga naar <strong>console.cloud.google.com</strong> en selecteer uw project (of maak een nieuw aan).</li>
							<li>Activeer de <strong>Google Analytics Data API</strong> via &ldquo;API's en services&rdquo;.</li>
							<li>Ga naar &ldquo;Referenties&rdquo; en maak een <strong>Service account</strong> aan.</li>
							<li>Download het <strong>JSON-sleutelbestand</strong> en plak de inhoud hierboven.</li>
							<li>Kopieer het <strong>e-mailadres</strong> van het service account (eindigt op <code>@...gserviceaccount.com</code>).</li>
							<li>Ga naar <strong>Google Analytics</strong> &rarr; Beheer &rarr; Property &rarr; Toegangsbeheer en voeg het e-mailadres toe als <strong>Viewer</strong>.</li>
						</ol>
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

				<div class="dwmcd-submit"><?php submit_button( 'Instellingen opslaan', 'primary', 'submit', false ); ?></div>
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

add_action( 'admin_head', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['menu_organizer_enabled'] ) ) {
		return;
	}
	$groups = (array) ( $s['menu_groups'] ?? array() );
	$order  = (array) ( $s['menu_order']  ?? array() );
	if ( empty( $groups ) && empty( $order ) ) {
		return;
	}

	// Build first-slug-per-group map for label injection
	$group_first = array(); // group_id => first slug
	$group_names = array(); // group_id => name
	foreach ( $groups as $grp ) {
		$gid = $grp['id'] ?? '';
		if ( $gid ) {
			$group_names[ $gid ] = $grp['name'] ?? '';
		}
	}
	foreach ( $order as $item ) {
		$slug = $item['slug'] ?? '';
		$gid  = $item['group'] ?? '';
		if ( $slug && $gid && ! isset( $group_first[ $gid ] ) ) {
			$group_first[ $gid ] = $slug;
		}
	}

	$inject = array();
	foreach ( $group_first as $gid => $slug ) {
		$inject[] = array(
			'slug'  => $slug,
			'label' => $group_names[ $gid ] ?? '',
		);
	}

	if ( ! empty( $inject ) ) {
		// Use admin_footer to inject the menu label script, because dwmcd-admin JS
		// may not be enqueued on all pages (conditional loading).
		add_action( 'admin_footer', function () use ( $inject ) {
			echo '<script>window.DWMCDMenuInject=' . wp_json_encode( $inject ) . ';'
				. '(function(){var m=document.getElementById("adminmenu");if(!m)return;'
				. 'function f(s){var a=m.querySelectorAll("a.menu-top");for(var i=0;i<a.length;i++){'
				. 'var h=(a[i].getAttribute("href")||"").replace(/^.*\\/wp-admin\\//,"");'
				. 'if(s.indexOf(".php")!==-1&&h===s)return a[i].closest("li.menu-top");'
				. 'if(h.indexOf("page="+s)!==-1)return a[i].closest("li.menu-top");}return null;}'
				. 'window.DWMCDMenuInject.forEach(function(i){var li=f(i.slug);if(!li||!i.label)return;'
				. 'var l=document.createElement("li");l.className="dwmcd-menu-cat-label";l.textContent=i.label;'
				. 'li.parentNode.insertBefore(l,li);});})();</script>';
		} );
	}
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

// ── Login pagina — icoon & favicon ────────────────────────────────────────────

add_action( 'login_head', function () {
	$s = jbwp_get_settings();
	if ( empty( $s['site_icon_id'] ) ) {
		return;
	}
	$icon_id  = (int) $s['site_icon_id'];
	$icon_url = wp_get_attachment_image_url( $icon_id, array( 180, 180 ) );
	if ( ! $icon_url ) {
		$icon_url = wp_get_attachment_url( $icon_id );
	}
	if ( ! $icon_url ) {
		return;
	}
	jbwp_favicon_tags( $icon_id );
	echo '<style>.login h1 a{'
		. 'background-image:url(' . esc_url( $icon_url ) . ') !important;'
		. 'background-size:contain !important;'
		. 'width:80px !important;'
		. 'height:80px !important;'
		. 'background-repeat:no-repeat !important;'
		. 'background-position:center !important;'
		. '}</style>';
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
		'media' => array(
			'label' => 'Media & Gebruikers',
			'icon'  => 'format-gallery',
			'color' => '#8b5cf6',
			'items' => array(
				array(
					'title'       => 'Media Categories',
					'description' => 'Voeg categorieën toe aan de mediabibliotheek voor betere organisatie.',
					'icon'        => 'category',
					'bullets'     => array(
						'Drag-and-drop categorisering van media-items',
						'Filteren op categorie bij het invoegen van media in content',
					),
				),
				array(
					'title'       => 'Media Replacement',
					'description' => 'Vervang eenvoudig elk type mediabestand met een nieuw bestand.',
					'icon'        => 'update',
					'bullets'     => array(
						'Behoudt bestaande media-ID, publicatiedatum en bestandsnaam',
						'Geen bestaande links breken',
						'Vervangen vanuit zowel de grid- als lijstweergave van de mediabibliotheek',
					),
				),
				array(
					'title'       => 'Local User Avatar',
					'description' => 'Gebruik elke afbeelding uit de WordPress Mediabibliotheek als gebruikersavatar.',
					'icon'        => 'admin-users',
					'bullets'     => array(
						'Kies een avatar rechtstreeks uit de Mediabibliotheek',
						'Geen externe diensten zoals Gravatar nodig',
					),
				),
			),
		),
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
