<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class RPS_Shortcodes {
	public function __construct() {
		add_shortcode( 'rps_prices', [ $this, 'render_prices_mount' ] );
		add_shortcode( 'dms_prices', [ $this, 'render_prices_mount' ] );
	}

	public function render_prices_mount() {
		return '<div class="dms_prices" data-dms-prices></div>';
	}
}