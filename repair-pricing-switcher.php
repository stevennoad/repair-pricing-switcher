<?php
/**
 * Plugin Name: Repair Pricing Switcher
 * Plugin URI: https://github.com/stevennoad/repair-pricing-switcher
 * Update URI: https://github.com/stevennoad/repair-pricing-switcher
 * Description: Elementor widget: dependent Device -> Model dropdowns that update a dynamic pricing table inside a single Elementor Template.
 * Version: 1.0.4
 * Author: Steve Noad
 * Text Domain: repair-pricing-switcher
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

final class RPS_Elementor_Plugin {
	const VERSION = '1.0.4';

	private $update_checker = null;

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		load_plugin_textdomain( 'repair-pricing-switcher', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Updates + "Check updates" link (admin only)
		if ( is_admin() ) {
			$this->setup_updates();
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		require_once __DIR__ . '/includes/class-repair-pricing-switcher-shortcodes.php';
		new RPS_Shortcodes();

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	private function setup_updates() {
		$puc_path = __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
		if ( ! file_exists( $puc_path ) ) {
			add_action( 'admin_notices', [ $this, 'puc_missing_notice' ] );
			return;
		}

		// Load PUC once.
		if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
			require_once $puc_path;
		}

		if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
			add_action( 'admin_notices', [ $this, 'puc_missing_notice' ] );
			return;
		}

		$this->update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			'https://github.com/stevennoad/repair-pricing-switcher/',
			__FILE__,
			'repair-pricing-switcher'
		);

		// Use GitHub Release Assets (upload a ZIP asset per release).
		$this->update_checker->getVcsApi()->enableReleaseAssets();
	}

	public function puc_missing_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'plugins' ) {
			return;
		}

		echo '<div class="notice notice-error"><p><strong>Repair Pricing Switcher:</strong> Auto-updates are not available because the update library is missing. Please reinstall the plugin using the full release ZIP (it should include <code>vendor/plugin-update-checker/</code>).</p></div>';
	}

	public function add_plugin_action_links( $links ) {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return $links;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'rps_action' => 'check_updates',
				],
				admin_url( 'plugins.php' )
			),
			'rps_check_updates_action'
		);

		$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Check for updates', 'repair-pricing-switcher' ) . '</a>';

		return $links;
	}

	public function handle_check_updates_action() {
		if ( ! isset( $_GET['rps_action'] ) || $_GET['rps_action'] !== 'check_updates' ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		check_admin_referer( 'rps_check_updates_action' );

		// Clear WP plugin update caches and re-check
		delete_site_transient( 'update_plugins' );
		wp_clean_plugins_cache( true );

		// Trigger PUC check if available
		if ( $this->update_checker && method_exists( $this->update_checker, 'checkForUpdates' ) ) {
			$this->update_checker->checkForUpdates();
		}

		// Trigger core update check
		wp_update_plugins();

		wp_safe_redirect(
			add_query_arg(
				[
					'rps_checked_updates' => '1',
				],
				admin_url( 'plugins.php' )
			)
		);
		exit;
	}

	public function maybe_show_checked_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'plugins' ) {
			return;
		}

		if ( ! isset( $_GET['rps_checked_updates'] ) || $_GET['rps_checked_updates'] !== '1' ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Repair Pricing Switcher: update check triggered. Refresh the page if needed.', 'repair-pricing-switcher' ) . '</p></div>';
	}

	public function register_assets() {
		$base_url = plugin_dir_url( __FILE__ );

		wp_register_style(
			'rps-css',
			$base_url . 'assets/repair-pricing-switcher.css',
			[],
			self::VERSION
		);

		wp_register_script(
			'rps-js',
			$base_url . 'assets/repair-pricing-switcher.js',
			[ 'jquery' ],
			self::VERSION,
			true
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/includes/class-repair-pricing-switcher-widget.php';
		$widgets_manager->register( new \RPS_Repair_Pricing_Switcher_Widget() );
	}
}

new RPS_Elementor_Plugin();
