<?php
/**
 * Plugin Name: Repair Pricing Switcher
 * Plugin URI: https://github.com/stevennoad/repair-pricing-switcher
 * Update URI: https://github.com/stevennoad/repair-pricing-switcher
 * Description: Elementor widget: dependent Device -> Model dropdowns that update a dynamic pricing table inside a single Elementor Template.
 * Version: 1.0.2
 * Author: Steve Noad
 * Text Domain: repair-pricing-switcher
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Composer autoloader (required for GitHub auto-updates via Plugin Update Checker)
$autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

final class RPS_Elementor_Plugin {
	const VERSION = '1.0.2';

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		load_plugin_textdomain( 'repair-pricing-switcher', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$this->setup_updates();

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
		if ( ! is_admin() ) {
			return;
		}

		if ( ! class_exists( 'Puc_v5_Factory' ) ) {
			return;
		}

		$update_checker = Puc_v5_Factory::buildUpdateChecker(
			'https://github.com/stevennoad/repair-pricing-switcher/',
			__FILE__,
			'repair-pricing-switcher'
		);

		// Use GitHub Release Assets (upload a built ZIP that includes /vendor).
		$update_checker->getVcsApi()->enableReleaseAssets();
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
