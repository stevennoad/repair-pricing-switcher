<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( class_exists( 'RPS_Repair_Pricing_Switcher_Admin' ) ) {
	return;
}

class RPS_Repair_Pricing_Switcher_Admin {
	const PAGE_SLUG = 'repair-pricing-switcher-tools';
	const WIDGET_NAME = 'dms_device_model_switcher';

	private $plugin_file = '';

	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_head', [ $this, 'output_admin_styles' ] );
		add_action( 'admin_post_rps_export_csv', [ $this, 'handle_export' ] );
		add_action( 'admin_post_rps_import_csv', [ $this, 'handle_import' ] );
	}

	public function add_admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Repair Pricing Switcher', 'repair-pricing-switcher' ),
			__( 'Repair Pricing Switcher', 'repair-pricing-switcher' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_admin_page' ]
		);
	}

	public function output_admin_styles() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'settings_page_' . self::PAGE_SLUG !== $screen->id ) {
			return;
		}

		echo '<style>
		.rps-admin-shell{margin-top:20px;max-width:none;margin-right:20px}
		.rps-admin-intro{margin:0 0 18px;color:#64748b;font-size:14px;line-height:1.6}
		.rps-admin-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:0 0 18px}
		.rps-admin-stat{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px 20px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
		.rps-admin-stat strong{display:block;font-size:24px;line-height:1.2;color:#0f172a}
		.rps-admin-stat span{display:block;margin-top:6px;color:#64748b}
		.rps-admin-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 2px rgba(15,23,42,.04);overflow:hidden}
		.rps-admin-card-header{padding:18px 20px;border-bottom:1px solid #e2e8f0;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
		.rps-admin-card-header h2{margin:0;font-size:18px;line-height:1.35;color:#0f172a}
		.rps-admin-card-header p{margin:6px 0 0;color:#64748b}
		.rps-admin-table-wrap{padding:12px;background:#f8fafc}
		.rps-admin-table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#fff}
		.rps-admin-table col:nth-child(1){width:28%}.rps-admin-table col:nth-child(2){width:24%}.rps-admin-table col:nth-child(3){width:12%}.rps-admin-table col:nth-child(4){width:14%}.rps-admin-table col:nth-child(5){width:22%}
		.rps-admin-table thead th{padding:13px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;text-align:left}
		.rps-admin-table tbody td{padding:18px 16px;border-bottom:1px solid #eef2f7;vertical-align:top;background:#fff}
		.rps-admin-table tbody tr:last-child td{border-bottom:0}
		.rps-admin-table tbody tr:nth-child(even) td{background:#fcfdff}
		.rps-admin-table tbody tr:hover td{background:#f8fbff}
		.rps-admin-name{margin:0 0 8px;font-size:14px;font-weight:600;line-height:1.45;color:#0f172a}
		.rps-admin-subtle{margin:0;color:#64748b;line-height:1.5}
		.rps-admin-subtle code{display:inline-block;margin-left:4px;padding:2px 6px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;font-size:12px;color:#334155}
		.rps-admin-badges{display:flex;flex-wrap:wrap;gap:8px;margin:0}
		.rps-admin-pill{display:inline-flex;align-items:center;padding:5px 10px;border:1px solid #dbe4f0;border-radius:999px;background:#f8fafc;color:#334155;font-size:12px;font-weight:600}
		.rps-admin-page-title{display:block;font-weight:600;margin:0 0 4px;color:#0f172a}
		.rps-admin-page-slug{display:block;margin:0 0 10px;color:#64748b;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace;font-size:12px}
		.rps-admin-link{display:inline-block;margin-top:2px;text-decoration:none;font-weight:500}
		.rps-admin-link:focus,.rps-admin-link:hover{text-decoration:underline}
		.rps-admin-export-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 8px}
		.rps-admin-export-form .button,.rps-admin-import-form .button{min-height:36px;border-radius:8px}
		.rps-admin-import-box{display:flex;flex-direction:column;gap:10px;padding:14px;border:1px solid #dbe4f0;border-radius:12px;background:#f8fafc;min-width:0}
		.rps-admin-import-form{display:flex;align-items:flex-start;gap:10px;flex-wrap:wrap;width:100%;min-width:0}
		.rps-admin-import-form input[type=file]{display:block;width:100%;max-width:100%;min-width:0;padding:6px 8px;border:1px solid #dbe4f0;border-radius:8px;background:#fff;box-sizing:border-box;overflow:hidden}
		.rps-admin-empty{padding:20px}
		.rps-admin-empty p{margin:0;color:#64748b}
		.rps-admin-note{display:block;color:#64748b;font-size:12px;line-height:1.45}.rps-admin-import-form .button{flex:0 0 auto}
		@media (max-width: 960px){.rps-admin-stats{grid-template-columns:1fr}.rps-admin-table-wrap{padding:0;background:transparent}.rps-admin-table,.rps-admin-table thead,.rps-admin-table tbody,.rps-admin-table th,.rps-admin-table td,.rps-admin-table tr,.rps-admin-table colgroup{display:block}.rps-admin-table{border-radius:14px}.rps-admin-table thead,.rps-admin-table colgroup{display:none}.rps-admin-table tbody tr{padding:14px 16px;border-bottom:1px solid #eef2f7;background:#fff}.rps-admin-table tbody td{padding:8px 0;border:0;background:transparent}.rps-admin-table tbody tr:nth-child(even) td,.rps-admin-table tbody tr:hover td{background:transparent}.rps-admin-import-box{padding:12px}.rps-admin-table tbody tr:last-child{border-bottom:0}}
		</style>';
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$widgets = $this->get_widget_index();
		$notice = $this->get_notice();

		$total_widgets = count( $widgets );
		$total_mappings = 0;
		$pages = [];
		foreach ( $widgets as $widget ) {
			$total_mappings += isset( $widget['settings']['mappings'] ) && is_array( $widget['settings']['mappings'] ) ? count( $widget['settings']['mappings'] ) : 0;
			$pages[ $widget['post_id'] ] = true;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Repair Pricing Switcher', 'repair-pricing-switcher' ) . '</h1>';
		echo '<div class="rps-admin-shell">';
		echo '<p class="rps-admin-intro">' . esc_html__( 'Choose a pricing table, export it to CSV for larger pricing updates, then import it back and keep using Elementor for smaller edits.', 'repair-pricing-switcher' ) . '</p>';

		if ( $notice ) {
			echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . ' is-dismissible"><p>' . esc_html( $notice['message'] ) . '</p></div>';
		}

		echo '<div class="rps-admin-stats">';
		echo '<div class="rps-admin-stat"><strong>' . esc_html( (string) $total_widgets ) . '</strong><span>' . esc_html__( 'Pricing tables found', 'repair-pricing-switcher' ) . '</span></div>';
		echo '<div class="rps-admin-stat"><strong>' . esc_html( (string) count( $pages ) ) . '</strong><span>' . esc_html__( 'Pages using the widget', 'repair-pricing-switcher' ) . '</span></div>';
		echo '<div class="rps-admin-stat"><strong>' . esc_html( (string) $total_mappings ) . '</strong><span>' . esc_html__( 'Mappings across all tables', 'repair-pricing-switcher' ) . '</span></div>';
		echo '</div>';

		echo '<div class="rps-admin-card">';
		echo '<div class="rps-admin-card-header"><h2>' . esc_html__( 'Pricing tables', 'repair-pricing-switcher' ) . '</h2><p>' . esc_html__( 'Each row below is one live pricing table. Friendly names come from the Pricing Table Name field inside the Elementor widget.', 'repair-pricing-switcher' ) . '</p></div>';

		if ( empty( $widgets ) ) {
			echo '<div class="rps-admin-empty"><p>' . esc_html__( 'No Repair Pricing Switcher widgets were found in Elementor content yet. Add the widget to a page and save it once, then it will appear here automatically.', 'repair-pricing-switcher' ) . '</p></div>';
			echo '</div></div></div>';
			return;
		}

		echo '<div class="rps-admin-table-wrap">';
		echo '<table class="widefat fixed striped rps-admin-table">';
		echo '<colgroup><col><col><col><col><col></colgroup>';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Pricing table', 'repair-pricing-switcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Page', 'repair-pricing-switcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Mappings', 'repair-pricing-switcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Export', 'repair-pricing-switcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Import', 'repair-pricing-switcher' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $widgets as $widget ) {
			$target = $widget['post_id'] . ':' . $widget['element_id'];
			$edit_link = get_edit_post_link( $widget['post_id'] );
			$mappings_count = isset( $widget['settings']['mappings'] ) && is_array( $widget['settings']['mappings'] ) ? count( $widget['settings']['mappings'] ) : 0;
			$friendly_name = isset( $widget['settings']['pricing_table_name'] ) ? trim( (string) $widget['settings']['pricing_table_name'] ) : '';
			$display_name = '' !== $friendly_name ? $friendly_name : $widget['label'];

			echo '<tr>';
			echo '<td>';
			echo '<p class="rps-admin-name">' . esc_html( $display_name ) . '</p>';
			echo '<p class="rps-admin-subtle">';
			if ( '' === $friendly_name ) {
				echo esc_html__( 'No friendly name set yet. ', 'repair-pricing-switcher' );
			}
			echo esc_html__( 'Element ID:', 'repair-pricing-switcher' ) . ' <code>' . esc_html( $widget['element_id'] ) . '</code></p>';
			echo '</td>';

			echo '<td>';
			echo '<span class="rps-admin-page-title">' . esc_html( $widget['post_title'] ) . '</span>';
			if ( ! empty( $widget['post_slug'] ) ) {
				echo '<span class="rps-admin-page-slug">/ ' . esc_html( $widget['post_slug'] ) . '</span>';
			}
			if ( $edit_link ) {
				echo '<a class="rps-admin-link" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit in Elementor', 'repair-pricing-switcher' ) . '</a>';
			}
			echo '</td>';

			echo '<td>';
			echo '<div class="rps-admin-badges">';
			echo '<span class="rps-admin-pill">' . esc_html( sprintf( _n( '%d mapping', '%d mappings', $mappings_count, 'repair-pricing-switcher' ), $mappings_count ) ) . '</span>';
			echo '</div>';
			echo '</td>';

			echo '<td>';
			echo '<form class="rps-admin-export-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="rps_export_csv">';
			echo '<input type="hidden" name="target_widget" value="' . esc_attr( $target ) . '">';
			wp_nonce_field( 'rps_export_csv_' . $target );
			submit_button( __( 'Export CSV', 'repair-pricing-switcher' ), 'secondary', 'submit', false );
			echo '</form>';
			echo '<span class="rps-admin-note">' . esc_html__( 'Use before larger repricing work.', 'repair-pricing-switcher' ) . '</span>';
			echo '</td>';

			echo '<td>';
			echo '<div class="rps-admin-import-box">';
			echo '<form class="rps-admin-import-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
			echo '<input type="hidden" name="action" value="rps_import_csv">';
			echo '<input type="hidden" name="target_widget" value="' . esc_attr( $target ) . '">';
			wp_nonce_field( 'rps_import_csv_' . $target );
			echo '<input id="rps_csv_file_' . esc_attr( $widget['element_id'] ) . '" type="file" name="rps_csv_file" accept=".csv,text/csv" required>';
			submit_button( __( 'Import CSV', 'repair-pricing-switcher' ), 'primary', 'submit', false );
			echo '</form>';
			echo '<span class="rps-admin-note">' . esc_html__( 'Imports into this exact pricing table.', 'repair-pricing-switcher' ) . '</span>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}


	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export this CSV.', 'repair-pricing-switcher' ) );
		}

		$target = isset( $_POST['target_widget'] ) ? sanitize_text_field( wp_unslash( $_POST['target_widget'] ) ) : '';
		$this->verify_target_nonce( 'export', $target );

		$widget = $this->get_widget_by_target( $target );
		if ( ! $widget ) {
			wp_die( esc_html__( 'The selected pricing table could not be found.', 'repair-pricing-switcher' ) );
		}

		$filename_base = isset( $widget['settings']['pricing_table_name'] ) && trim( (string) $widget['settings']['pricing_table_name'] ) !== '' ? trim( (string) $widget['settings']['pricing_table_name'] ) : $widget['label'];
		$filename = sanitize_title( $widget['post_title'] . '-' . $filename_base ) . '.csv';
		$csv = $this->build_csv( $widget['settings'] );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to import this CSV.', 'repair-pricing-switcher' ) );
		}

		$target = isset( $_POST['target_widget'] ) ? sanitize_text_field( wp_unslash( $_POST['target_widget'] ) ) : '';
		$this->verify_target_nonce( 'import', $target );

		if ( empty( $_FILES['rps_csv_file']['tmp_name'] ) ) {
			$this->redirect_with_notice( 'error', __( 'Please choose a CSV file to import.', 'repair-pricing-switcher' ) );
		}

		$file = $_FILES['rps_csv_file'];
		if ( ! empty( $file['error'] ) ) {
			$this->redirect_with_notice( 'error', __( 'The uploaded CSV could not be read.', 'repair-pricing-switcher' ) );
		}

		$widget = $this->get_widget_by_target( $target );
		if ( ! $widget ) {
			$this->redirect_with_notice( 'error', __( 'The selected pricing table could not be found.', 'repair-pricing-switcher' ) );
		}

		$parsed = $this->parse_csv_file( $file['tmp_name'] );
		$result = $this->update_widget_from_import( $widget['post_id'], $widget['element_id'], $parsed );

		if ( is_wp_error( $result ) ) {
			$this->redirect_with_notice( 'error', $result->get_error_message() );
		}

		$message = sprintf(
			/* translators: 1: mappings count, 2: settings count */
			__( 'CSV imported. %1$d mappings updated and %2$d settings applied.', 'repair-pricing-switcher' ),
			(int) $result['mapping_count'],
			(int) $result['settings_count']
		);

		$this->redirect_with_notice( 'success', $message );
	}

	private function verify_target_nonce( $action, $target ) {
		$action_name = $action === 'export' ? 'rps_export_csv_' : 'rps_import_csv_';
		check_admin_referer( $action_name . $target );
	}

	private function get_notice() {
		if ( empty( $_GET['rps_notice'] ) || empty( $_GET['rps_message'] ) ) {
			return null;
		}

		$type = sanitize_key( wp_unslash( $_GET['rps_notice'] ) );
		if ( ! in_array( $type, [ 'success', 'error', 'warning' ], true ) ) {
			$type = 'success';
		}

		return [
			'type' => $type,
			'message' => sanitize_text_field( wp_unslash( $_GET['rps_message'] ) ),
		];
	}

	private function redirect_with_notice( $type, $message ) {
		wp_safe_redirect(
			add_query_arg(
				[
					'page' => self::PAGE_SLUG,
					'rps_notice' => $type,
					'rps_message' => $message,
				],
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	private function get_widget_by_target( $target ) {
		$parts = explode( ':', $target );
		if ( count( $parts ) !== 2 ) {
			return null;
		}

		$post_id = absint( $parts[0] );
		$element_id = sanitize_key( $parts[1] );
		$widgets = $this->get_widgets_from_post( $post_id );

		foreach ( $widgets as $widget ) {
			if ( $widget['element_id'] === $element_id ) {
				return $widget;
			}
		}

		return null;
	}

	private function get_widget_index() {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID, p.post_title, p.post_type
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
				WHERE p.post_status NOT IN ('trash','auto-draft','inherit')
				AND p.post_type <> 'revision'
				ORDER BY p.post_title ASC, p.ID ASC",
				'_elementor_data'
			),
			ARRAY_A
		);

		$widgets = [];
		$seen = [];

		foreach ( $rows as $row ) {
			$post_widgets = $this->get_widgets_from_post( (int) $row['ID'] );
			foreach ( $post_widgets as $widget ) {
				$widget_key = $widget['post_id'] . ':' . $widget['element_id'];

				if ( isset( $seen[ $widget_key ] ) ) {
					continue;
				}

				$seen[ $widget_key ] = true;
				$widgets[] = $widget;
			}
		}

		return $widgets;
	}

	private function get_widgets_from_post( $post_id ) {
		$data = get_post_meta( $post_id, '_elementor_data', true );
		if ( empty( $data ) || ! is_string( $data ) ) {
			return [];
		}

		$elements = json_decode( $data, true );
		if ( ! is_array( $elements ) ) {
			return [];
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$widgets = [];
		$this->collect_widgets_from_elements( $elements, $post, $widgets, [] );

		return $widgets;
	}

	private function collect_widgets_from_elements( $elements, $post, &$widgets, $trail ) {
		if ( empty( $elements ) || ! is_array( $elements ) ) {
			return;
		}

		$position = 0;
		foreach ( $elements as $element ) {
			$position++;
			$current_trail = $trail;
			$current_trail[] = $position;

			if ( isset( $element['elType'] ) && $element['elType'] === 'widget' && isset( $element['widgetType'] ) && $element['widgetType'] === self::WIDGET_NAME ) {
				$settings = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : [];
				$friendly_name = isset( $settings['pricing_table_name'] ) ? trim( (string) $settings['pricing_table_name'] ) : '';
				$default_device = isset( $settings['default_device'] ) ? trim( (string) $settings['default_device'] ) : '';
				$label = $friendly_name !== '' ? $friendly_name : $default_device;
				if ( '' === $label ) {
					$label = __( 'Pricing table', 'repair-pricing-switcher' );
				}
				$label .= ' #' . implode( '.', $current_trail );

				$widgets[] = [
					'post_id' => (int) $post->ID,
					'post_title' => $post->post_title,
					'post_type' => $post->post_type,
			'post_slug' => $post->post_name,
					'element_id' => isset( $element['id'] ) ? (string) $element['id'] : '',
					'label' => $label,
					'settings' => $settings,
				];
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->collect_widgets_from_elements( $element['elements'], $post, $widgets, $current_trail );
			}
		}
	}

	private function build_csv( $settings ) {
		$rows = $this->build_export_rows( $settings );
		$stream = fopen( 'php://temp', 'r+' );

		foreach ( $rows as $row ) {
			fputcsv( $stream, $row );
		}

		rewind( $stream );
		$content = stream_get_contents( $stream );
		fclose( $stream );

		return (string) $content;
	}

	private function build_export_rows( $settings ) {
		$rows = [];
		$export_keys = $this->get_allowed_setting_keys();

		$rows[] = [ 'row_type', 'device', 'model', 'service', 'applecare', 'price', 'terms_text', 'setting_key', 'setting_value' ];

		foreach ( $export_keys as $key ) {
			$rows[] = [ 'setting', '', '', '', '', '', '', $key, isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '' ];
		}

		$mappings = isset( $settings['mappings'] ) && is_array( $settings['mappings'] ) ? $settings['mappings'] : [];
		foreach ( $mappings as $mapping ) {
			$terms_text = isset( $mapping['terms_text'] ) ? (string) $mapping['terms_text'] : '';
			$row_lines = $this->normalise_rows_text( isset( $mapping['rows_text'] ) ? (string) $mapping['rows_text'] : '' );

			if ( empty( $row_lines ) ) {
				$rows[] = [ 'mapping', isset( $mapping['device'] ) ? (string) $mapping['device'] : '', isset( $mapping['model'] ) ? (string) $mapping['model'] : '', '', '', '', $terms_text, '', '' ];
				continue;
			}

			foreach ( $row_lines as $index => $line ) {
				$parts = array_map( 'trim', explode( '|', $line ) );
				$rows[] = [
					'mapping',
					isset( $mapping['device'] ) ? (string) $mapping['device'] : '',
					isset( $mapping['model'] ) ? (string) $mapping['model'] : '',
					isset( $parts[0] ) ? $parts[0] : '',
					isset( $parts[1] ) ? $parts[1] : '',
					isset( $parts[2] ) ? $parts[2] : '',
					0 === $index ? $terms_text : '',
					'',
					'',
				];
			}
		}

		return $rows;
	}

	private function normalise_rows_text( $rows_text ) {
		$rows_text = str_replace( [ "\r\n", "\r" ], "\n", (string) $rows_text );
		$lines = array_map( 'trim', explode( "\n", $rows_text ) );
		$lines = array_filter(
			$lines,
			static function ( $line ) {
				return $line !== '';
			}
		);

		return array_values( $lines );
	}

	private function parse_csv_file( $tmp_name ) {
		$handle = fopen( $tmp_name, 'r' );
		if ( ! $handle ) {
			return new WP_Error( 'rps_csv_open_failed', __( 'The uploaded CSV could not be opened.', 'repair-pricing-switcher' ) );
		}

		$rows = [];
		while ( false !== ( $row = fgetcsv( $handle ) ) ) {
			if ( $row === [ null ] ) {
				continue;
			}
			$rows[] = $row;
		}
		fclose( $handle );

		if ( empty( $rows ) ) {
			return new WP_Error( 'rps_csv_empty', __( 'The CSV is empty.', 'repair-pricing-switcher' ) );
		}

		$header = array_shift( $rows );
		$columns = [];
		foreach ( $header as $index => $column_name ) {
			$columns[ trim( (string) $column_name ) ] = $index;
		}

		if ( ! isset( $columns['row_type'] ) ) {
			return new WP_Error( 'rps_csv_missing_header', __( 'The CSV must include a row_type column.', 'repair-pricing-switcher' ) );
		}

		$allowed_setting_keys = array_fill_keys( $this->get_allowed_setting_keys(), true );
		$imported_settings = [];
		$mappings = [];
		$mappings_index = [];

		foreach ( $rows as $row ) {
			$row_type = strtolower( trim( (string) $this->get_csv_value( $row, $columns, 'row_type' ) ) );
			$device = trim( (string) $this->get_csv_value( $row, $columns, 'device' ) );
			$model = trim( (string) $this->get_csv_value( $row, $columns, 'model' ) );
			$service = trim( (string) $this->get_csv_value( $row, $columns, 'service' ) );
			$applecare = trim( (string) $this->get_csv_value( $row, $columns, 'applecare' ) );
			$price = trim( (string) $this->get_csv_value( $row, $columns, 'price' ) );
			$terms_text = (string) $this->get_csv_value( $row, $columns, 'terms_text' );
			$setting_key = trim( (string) $this->get_csv_value( $row, $columns, 'setting_key' ) );
			$setting_value = (string) $this->get_csv_value( $row, $columns, 'setting_value' );

			if ( 'setting' === $row_type ) {
				if ( $setting_key && isset( $allowed_setting_keys[ $setting_key ] ) ) {
					$imported_settings[ $setting_key ] = $setting_value;
				}
				continue;
			}

			if ( 'mapping' !== $row_type || '' === $device || '' === $model ) {
				continue;
			}

			$mapping_key = $device . '||' . $model;
			if ( ! isset( $mappings_index[ $mapping_key ] ) ) {
				$mappings_index[ $mapping_key ] = [
					'_id' => $this->create_mapping_id( count( $mappings ) ),
					'device' => $device,
					'model' => $model,
					'rows_text' => '',
					'terms_text' => '',
				];
				$mappings[] = &$mappings_index[ $mapping_key ];
			}

			if ( '' !== trim( $terms_text ) && '' === $mappings_index[ $mapping_key ]['terms_text'] ) {
				$mappings_index[ $mapping_key ]['terms_text'] = $terms_text;
			}

			if ( '' === $service && '' === $applecare && '' === $price ) {
				continue;
			}

			$row_text = implode( ' | ', [ $service, $applecare, $price ] );
			$mappings_index[ $mapping_key ]['rows_text'] = '' === $mappings_index[ $mapping_key ]['rows_text']
				? $row_text
				: $mappings_index[ $mapping_key ]['rows_text'] . "\n" . $row_text;
		}

		return [
			'mappings' => array_values( $mappings ),
			'settings' => $imported_settings,
		];
	}

	private function get_csv_value( $row, $columns, $key ) {
		if ( ! isset( $columns[ $key ] ) ) {
			return '';
		}

		$index = (int) $columns[ $key ];
		return isset( $row[ $index ] ) ? $row[ $index ] : '';
	}

	private function create_mapping_id( $index ) {
		return 'rpscsv' . (string) ( $index + 1 ) . wp_generate_password( 6, false, false );
	}

	private function get_allowed_setting_keys() {
		return [
			'pricing_table_name',
			'panel_template_id',
			'dropdown_layout',
			'auto_select_first_model',
			'device_label',
			'model_label',
			'placeholder_device',
			'placeholder_model',
			'default_device',
			'default_model',
			'enable_applecare_column',
			'col_label_applecare',
			'col_label_price',
			'hide_applecare_when_empty',
			'show_empty_message',
		];
	}

	private function update_widget_from_import( $post_id, $element_id, $parsed ) {
		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		$data = get_post_meta( $post_id, '_elementor_data', true );
		if ( empty( $data ) || ! is_string( $data ) ) {
			return new WP_Error( 'rps_missing_elementor_data', __( 'This page does not contain valid Elementor data.', 'repair-pricing-switcher' ) );
		}

		$elements = json_decode( $data, true );
		if ( ! is_array( $elements ) ) {
			return new WP_Error( 'rps_invalid_elementor_data', __( 'This page contains invalid Elementor data.', 'repair-pricing-switcher' ) );
		}

		$updated = $this->replace_widget_settings_in_elements( $elements, $element_id, $parsed );
		if ( ! $updated ) {
			return new WP_Error( 'rps_widget_not_updated', __( 'The selected pricing table could not be updated.', 'repair-pricing-switcher' ) );
		}

		$json = wp_json_encode( $elements );
		if ( ! $json ) {
			return new WP_Error( 'rps_json_failed', __( 'The updated Elementor data could not be saved.', 'repair-pricing-switcher' ) );
		}

		update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', get_post_meta( $post_id, '_elementor_template_type', true ) );
		clean_post_cache( $post_id );

		if ( did_action( 'elementor/loaded' ) ) {
			$elementor = \Elementor\Plugin::$instance;
			if ( isset( $elementor->files_manager ) && method_exists( $elementor->files_manager, 'clear_cache' ) ) {
				$elementor->files_manager->clear_cache();
			}
		}

		return [
			'mapping_count' => isset( $parsed['mappings'] ) && is_array( $parsed['mappings'] ) ? count( $parsed['mappings'] ) : 0,
			'settings_count' => isset( $parsed['settings'] ) && is_array( $parsed['settings'] ) ? count( $parsed['settings'] ) : 0,
		];
	}

	private function replace_widget_settings_in_elements( &$elements, $element_id, $parsed ) {
		if ( empty( $elements ) || ! is_array( $elements ) ) {
			return false;
		}

		foreach ( $elements as &$element ) {
			if ( isset( $element['id'] ) && (string) $element['id'] === (string) $element_id && isset( $element['widgetType'] ) && $element['widgetType'] === self::WIDGET_NAME ) {
				if ( ! isset( $element['settings'] ) || ! is_array( $element['settings'] ) ) {
					$element['settings'] = [];
				}

				$element['settings']['mappings'] = isset( $parsed['mappings'] ) && is_array( $parsed['mappings'] ) ? $parsed['mappings'] : [];

				if ( ! empty( $parsed['settings'] ) && is_array( $parsed['settings'] ) ) {
					foreach ( $parsed['settings'] as $key => $value ) {
						if ( 'panel_template_id' === $key ) {
							continue;
						}
						$element['settings'][ $key ] = $value;
					}
				}

				return true;
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$updated = $this->replace_widget_settings_in_elements( $element['elements'], $element_id, $parsed );
				if ( $updated ) {
					return true;
				}
			}
		}

		return false;
	}
}
