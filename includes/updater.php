<?php
/**
 * GitHub auto-updater bootstrap.
 *
 * Wraps the YahnisElsts plugin-update-checker library so this plugin can
 * receive update notifications from GitHub Releases instead of (or in
 * addition to) wordpress.org.
 *
 * Design goals:
 *  - Defensive: never throws a fatal if the library is missing.
 *  - Idempotent: safe to call multiple times; only initialises once.
 *  - Extensible: keeps a single function/hook surface so we can later
 *    swap GitHub for a private update server, an authenticated repo,
 *    or a self-hosted JSON manifest without touching the rest of the plugin.
 *
 * @package JB_WP_Beheer_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'jbwp_bootstrap_updater' ) ) {
	/**
	 * Initialise the GitHub-backed auto-updater.
	 *
	 * Reads the repository URL and branch from the JBWP_GITHUB_* constants
	 * defined in the main plugin file. Returns early and silently if the
	 * vendored update-checker library is missing or fails to initialise.
	 *
	 * @return mixed|null The update checker instance, or null on failure.
	 */
	function jbwp_bootstrap_updater() {
		static $instance = null;
		if ( null !== $instance ) {
			return $instance;
		}

		// Required identity constants must exist (set in main plugin file).
		if ( ! defined( 'JBWP_PLUGIN_FILE' ) || ! defined( 'JBWP_PLUGIN_SLUG' )
			|| ! defined( 'JBWP_GITHUB_REPO' ) || ! defined( 'JBWP_GITHUB_BRANCH' ) ) {
			return null;
		}

		// Locate the vendored library. If it's missing the plugin should
		// continue to function normally — we just won't get auto-updates.
		$loader = plugin_dir_path( JBWP_PLUGIN_FILE ) . 'lib/plugin-update-checker/plugin-update-checker.php';
		if ( ! file_exists( $loader ) ) {
			return null;
		}

		require_once $loader;

		// The library exposes a stable major-version alias
		// (\YahnisElsts\PluginUpdateChecker\v5\PucFactory) which forwards to
		// whatever minor version is bundled. We use the alias so we can
		// upgrade the library in-place without changing this code.
		$factory_class = '\YahnisElsts\PluginUpdateChecker\v5\PucFactory';
		if ( ! class_exists( $factory_class ) ) {
			return null;
		}

		try {
			/** @var object $checker */
			$checker = call_user_func(
				array( $factory_class, 'buildUpdateChecker' ),
				JBWP_GITHUB_REPO,
				JBWP_PLUGIN_FILE,
				JBWP_PLUGIN_SLUG
			);

			if ( ! is_object( $checker ) ) {
				return null;
			}

			// Track the configured branch (main by default). Tags still take
			// precedence over branch commits — see Release process below.
			if ( method_exists( $checker, 'setBranch' ) ) {
				$checker->setBranch( JBWP_GITHUB_BRANCH );
			}

			// Prefer release ZIP assets over the auto-generated source archive,
			// so any custom build (e.g. trimmed vendor folder) ships intact.
			if ( method_exists( $checker, 'getVcsApi' ) ) {
				$vcs = $checker->getVcsApi();
				if ( is_object( $vcs ) && method_exists( $vcs, 'enableReleaseAssets' ) ) {
					$vcs->enableReleaseAssets();
				}
			}

			/**
			 * Filter: jbwp_update_checker
			 *
			 * Lets future code (e.g. a private-repo extension) post-configure
			 * the update checker, set authentication tokens, switch backends,
			 * change the branch per-environment, etc.
			 *
			 * @param object $checker The plugin-update-checker instance.
			 */
			if ( function_exists( 'apply_filters' ) ) {
				$checker = apply_filters( 'jbwp_update_checker', $checker );
			}

			$instance = $checker;
			return $instance;
		} catch ( \Throwable $e ) {
			// Never let an updater failure take down the site.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
				error_log( '[JB WP Beheer Plugin] Updater init failed: ' . $e->getMessage() );
			}
			return null;
		}
	}
}
