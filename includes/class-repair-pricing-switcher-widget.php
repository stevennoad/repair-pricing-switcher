<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( class_exists( 'RPS_Repair_Pricing_Switcher_Widget' ) ) {
	return;
}

class RPS_Repair_Pricing_Switcher_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dms_device_model_switcher';
	}

	public function get_title() {
		return __( 'Repair Pricing Switcher', 'repair-pricing-switcher' );
	}

	public function get_icon() {
		return 'eicon-select';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_style_depends() {
		return [ 'rps-css' ];
	}

	public function get_script_depends() {
		return [ 'rps-js' ];
	}

	protected function register_controls() {

		// 1) Mappings at top, renamed to Content
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'repair-pricing-switcher' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'device',
			[
				'label'       => __( 'Device', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'iPhone Air',
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'model',
			[
				'label'       => __( 'Model', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'iPhone Air',
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'rows_text',
			[
				'label'       => __( 'Pricing Rows', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'label_block' => true,
				'default'     => "Battery service |  | £109\nBack glass damage |  | £145\nRear camera damage |  | £269\nScreen damage |  | £389\nScreen and back glass damage |  | £469\nOther damage |  | £795",
			]
		);

		$this->add_control(
			'mappings',
			[
				'label'       => __( 'Device → Model', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [],
				'title_field' => '{{{ device }}} → {{{ model }}}',
			]
		);

		$this->end_controls_section();

		// 2) General renamed to Panel Template
		$this->start_controls_section(
			'section_panel',
			[
				'label' => __( 'Panel Template', 'repair-pricing-switcher' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'panel_template_id',
			[
				'label'   => __( 'Panel Template', 'repair-pricing-switcher' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_elementor_templates_options(),
				'default'     => '',
				'description' => __( "Create a new Elementor Template and add a 'Shortcode' widget with the shortcode: [rps_prices]", 'repair-pricing-switcher' ),
			]
		);


		$this->end_controls_section();

		// 3+4) Dropdown layout moved here; rename Dropdown text -> Dropdown
		$this->start_controls_section(
			'section_dropdown',
			[
				'label' => __( 'Dropdown', 'repair-pricing-switcher' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'dropdown_layout',
			[
				'label'   => __( 'Layout', 'repair-pricing-switcher' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => [
					'inline' => __( 'Side by side', 'repair-pricing-switcher' ),
					'stack'  => __( 'Stacked', 'repair-pricing-switcher' ),
				],
			]
		);

		$this->add_control(
			'auto_select_first_model',
			[
				'label'        => __( 'Auto select first model', 'repair-pricing-switcher' ),
				'description'  => __( 'When you change Device, automatically select the first Model option.', 'repair-pricing-switcher' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'repair-pricing-switcher' ),
				'label_off'    => __( 'No', 'repair-pricing-switcher' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'device_label',
			[
				'label'       => __( 'Device Label', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Device Type', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'model_label',
			[
				'label'       => __( 'Model Label', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Model', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'placeholder_device',
			[
				'label'       => __( 'Device Placeholder', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Select…', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'placeholder_model',
			[
				'label'       => __( 'Model Placeholder', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Select…', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->end_controls_section();

		// 5) Move default selection into Pricing table tab
		$this->start_controls_section(
			'section_pricing',
			[
				'label' => __( 'Pricing table', 'repair-pricing-switcher' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'default_device',
			[
				'label'       => __( 'Default Device (exact match)', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'default_model',
			[
				'label'       => __( 'Default Model (exact match)', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'col_label_applecare',
			[
				'label'       => __( 'Column Label: AppleCare+', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'AppleCare+', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'col_label_price',
			[
				'label'       => __( 'Column Label: Price', 'repair-pricing-switcher' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Price', 'repair-pricing-switcher' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'hide_applecare_when_empty',
			[
				'label'        => __( 'Hide AppleCare+ column when empty', 'repair-pricing-switcher' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'repair-pricing-switcher' ),
				'label_off'    => __( 'No', 'repair-pricing-switcher' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_empty_message',
			[
				'label'        => __( 'Show "No pricing rows" message', 'repair-pricing-switcher' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'repair-pricing-switcher' ),
				'label_off'    => __( 'No', 'repair-pricing-switcher' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		// Style
		$this->start_controls_section(
			'section_style_dropdowns',
			[
				'label' => __( 'Dropdowns', 'repair-pricing-switcher' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'dropdown_width',
			[
				'label' => __( 'Width', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 80, 'max' => 1200 ],
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'vw' => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_height',
			[
				'label' => __( 'Height', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 30, 'max' => 120 ],
				],
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_padding',
			[
				'label' => __( 'Padding', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_margin',
			[
				'label' => __( 'Margin', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .dms__field' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dropdown_bg_color',
			[
				'label' => __( 'Background Color', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dropdown_text_color',
			[
				'label' => __( 'Text Color', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dropdown_border_color',
			[
				'label' => __( 'Border Color', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			[
				'label' => __( 'Border Radius', 'repair-pricing-switcher' ),
				'type'  => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .dms__select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function get_elementor_templates_options() {
		static $cached = null;

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$cached = [
			'' => __( '— Select a template —', 'repair-pricing-switcher' ),
		];

		$templates = get_posts(
			[
				'post_type'      => 'elementor_library',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			]
		);

		if ( empty( $templates ) ) {
			return $cached;
		}

		foreach ( $templates as $template ) {
			$cached[ (string) $template->ID ] = $template->post_title;
		}

		return $cached;
	}

	private function parse_rows_text( $rows_text ) {
		$rows_text = (string) $rows_text;
		$rows_text = str_replace( "\r\n", "\n", $rows_text );
		$rows_text = str_replace( "\r", "\n", $rows_text );

		$lines = explode( "\n", $rows_text );
		$rows  = [];

		foreach ( $lines as $line ) {
			$line = trim( (string) $line );

			if ( $line === '' ) {
				continue;
			}

			$parts = array_map( 'trim', explode( '|', $line ) );

			$service = isset( $parts[0] ) ? sanitize_text_field( (string) $parts[0] ) : '';
			$ac      = isset( $parts[1] ) ? sanitize_text_field( (string) $parts[1] ) : '';
			$price   = isset( $parts[2] ) ? sanitize_text_field( (string) $parts[2] ) : '';

			if ( $service === '' ) {
				continue;
			}

			$rows[] = [
				'service'   => $service,
				'applecare' => $ac,
				'price'     => $price,
			];
		}

		return $rows;
	}

	private function build_index( $mappings ) {
		$index = [];

		if ( empty( $mappings ) ) {
			return $index;
		}

		foreach ( $mappings as $row ) {
			$device    = isset( $row['device'] ) ? sanitize_text_field( trim( (string) $row['device'] ) ) : '';
			$model     = isset( $row['model'] ) ? sanitize_text_field( trim( (string) $row['model'] ) ) : '';
			$rows_text = isset( $row['rows_text'] ) ? (string) $row['rows_text'] : '';

			if ( strlen( $rows_text ) > 20000 ) {
				$rows_text = substr( $rows_text, 0, 20000 );
			}

			if ( $device === '' ) {
				continue;
			}

			if ( $model === '' ) {
				continue;
			}

			if ( ! isset( $index[ $device ] ) ) {
				$index[ $device ] = [];
			}

			$index[ $device ][ $model ] = $this->parse_rows_text( $rows_text );
		}

		return $index;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$index    = $this->build_index( isset( $settings['mappings'] ) ? $settings['mappings'] : [] );

		wp_enqueue_style( 'rps-css' );
		wp_enqueue_script( 'rps-js' );

		$config = [
			'index'                      => $index,
			'placeholder_device'         => sanitize_text_field( (string) $settings['placeholder_device'] ),
			'placeholder_model'          => sanitize_text_field( (string) $settings['placeholder_model'] ),
			'col_label_applecare'        => sanitize_text_field( (string) $settings['col_label_applecare'] ),
			'col_label_price'            => sanitize_text_field( (string) $settings['col_label_price'] ),
			'hide_applecare_when_empty'  => ( isset( $settings['hide_applecare_when_empty'] ) && $settings['hide_applecare_when_empty'] === 'yes' ) ? 'yes' : 'no',
			'show_empty_message'         => ( isset( $settings['show_empty_message'] ) && $settings['show_empty_message'] === 'yes' ) ? 'yes' : 'no',
			'default_device'             => isset( $settings['default_device'] ) ? sanitize_text_field( (string) $settings['default_device'] ) : '',
			'default_model'              => isset( $settings['default_model'] ) ? sanitize_text_field( (string) $settings['default_model'] ) : '',
			'auto_select_first_model'    => ( isset( $settings['auto_select_first_model'] ) && $settings['auto_select_first_model'] === 'yes' ) ? 'yes' : 'no',
			'labels' => [
				'device' => sanitize_text_field( (string) $settings['device_label'] ),
				'model'  => sanitize_text_field( (string) $settings['model_label'] ),
			],
		];

		$config_json = wp_json_encode( $config );
		if ( ! $config_json ) {
			$config_json = '{}';
		}

		$layout_class = ( isset( $settings['dropdown_layout'] ) && $settings['dropdown_layout'] === 'stack' ) ? ' dms--stack' : ' dms--inline';

		echo '<div class="dms' . esc_attr( $layout_class ) . '" data-dms-config="' . esc_attr( $config_json ) . '">';
			echo '<div class="dms__controls">';

				$this->render_dropdown_field(
					'device',
					sanitize_text_field( (string) $settings['device_label'] ),
					sanitize_text_field( (string) $settings['placeholder_device'] ),
					'dms__select--device',
					true
				);

				$this->render_dropdown_field(
					'model',
					sanitize_text_field( (string) $settings['model_label'] ),
					sanitize_text_field( (string) $settings['placeholder_model'] ),
					'dms__select--model',
					false
				);

			echo '</div>';

			$this->render_template_panel( isset( $settings['panel_template_id'] ) ? (int) $settings['panel_template_id'] : 0 );
		echo '</div>';
	}

	private function render_dropdown_field( $key, $label, $placeholder, $select_class, $enabled ) {
		$id = $key . '-' . $this->get_id();

		echo '<div class="dms__field">';
			echo '<select class="dms__select ' . esc_attr( $select_class ) . '" aria-labelledby="' . esc_attr( $id ) . '"' . ( $enabled ? '' : ' disabled' ) . '>';
				echo '<option value="">' . esc_html( $placeholder ) . '</option>';
			echo '</select>';
			echo '<span class="dms__chevron" aria-hidden="true"></span>';
			echo '<span class="dms__floating_label" id="' . esc_attr( $id ) . '" aria-hidden="true">' . esc_html( $label ) . '</span>';
		echo '</div>';
	}

	private function render_template_panel( $template_id ) {
		if ( $template_id <= 0 ) {
			echo '<div class="dms__empty">';
				echo esc_html__( 'Select a Panel Template and add device/model rows.', 'repair-pricing-switcher' );
			echo '</div>';
			return;
		}

		$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
		if ( ! $content ) {
			echo '<div class="dms__empty">';
				echo esc_html__( 'Template content could not be rendered.', 'repair-pricing-switcher' );
			echo '</div>';
			return;
		}

		echo '<div class="dms__panel_template">';
			echo $content;
		echo '</div>';
	}
}
