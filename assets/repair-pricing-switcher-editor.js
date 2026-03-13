(function ($) {
	var widget_name = 'dms_device_model_switcher';

	function escape_html(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function csv_escape(value) {
		var string_value = String(value == null ? '' : value);
		if (/[",\n\r]/.test(string_value)) {
			return '"' + string_value.replace(/"/g, '""') + '"';
		}

		return string_value;
	}

	function parse_csv(text) {
		var rows = [];
		var row = [];
		var value = '';
		var in_quotes = false;
		var i = 0;

		text = String(text || '').replace(/^\uFEFF/, '');

		for (i = 0; i < text.length; i++) {
			var char = text.charAt(i);
			var next = text.charAt(i + 1);

			if (in_quotes) {
				if (char === '"' && next === '"') {
					value += '"';
					i++;
					continue;
				}

				if (char === '"') {
					in_quotes = false;
					continue;
				}

				value += char;
				continue;
			}

			if (char === '"') {
				in_quotes = true;
				continue;
			}

			if (char === ',') {
				row.push(value);
				value = '';
				continue;
			}

			if (char === '\n') {
				row.push(value);
				rows.push(row);
				row = [];
				value = '';
				continue;
			}

			if (char === '\r') {
				continue;
			}

			value += char;
		}

		row.push(value);
		rows.push(row);

		return rows.filter(function (current_row) {
			if (!current_row.length) {
				return false;
			}

			return current_row.some(function (cell) {
				return String(cell || '').trim() !== '';
			});
		});
	}

	function get_widget_model() {
		var panel_view;
		var page_view;
		var edited_view;
		var widget_model = null;

		if (!window.elementor || !elementor.getPanelView) {
			return null;
		}

		panel_view = elementor.getPanelView();
		if (!panel_view || !panel_view.getCurrentPageView) {
			return null;
		}

		page_view = panel_view.getCurrentPageView();

		if (page_view && page_view.getOption) {
			edited_view = page_view.getOption('editedElementView');
			if (edited_view) {
				if (typeof edited_view.getEditModel === 'function') {
					widget_model = edited_view.getEditModel();
				} else if (edited_view.model) {
					widget_model = edited_view.model;
				}
			}
		}

		if (!widget_model && page_view && page_view.model) {
			widget_model = page_view.model;
		}

		if (!widget_model && window.elementorCommon && elementorCommon.elements && typeof elementorCommon.elements.$window !== 'undefined') {
			widget_model = elementorCommon.elements.$window.data('model');
		}

		if (!widget_model || !widget_model.get) {
			return null;
		}

		if (widget_model.get('widgetType') !== widget_name && widget_model.get('elType') !== 'widget') {
			return null;
		}

		if (widget_model.get('widgetType') && widget_model.get('widgetType') !== widget_name) {
			return null;
		}

		return widget_model;
	}

	function get_settings_model(widget_model) {
		if (!widget_model || !widget_model.get) {
			return null;
		}

		return widget_model.get('settings') || null;
	}

	function get_settings_json(widget_model) {
		var settings_model = get_settings_model(widget_model);

		if (!settings_model) {
			return {};
		}

		if (typeof settings_model.toJSON === 'function') {
			return settings_model.toJSON();
		}

		if (settings_model.attributes) {
			return $.extend(true, {}, settings_model.attributes);
		}

		return {};
	}

	function set_widget_setting(widget_model, key, value) {
		var settings_model = get_settings_model(widget_model);

		if (settings_model && typeof settings_model.set === 'function') {
			settings_model.set(key, value);
			return true;
		}

		if (widget_model && typeof widget_model.setSetting === 'function') {
			widget_model.setSetting(key, value);
			return true;
		}

		if (widget_model && typeof widget_model.set === 'function') {
			widget_model.set(key, value);
			return true;
		}

		return false;
	}

	function trigger_editor_refresh(widget_model) {
		var settings_model = get_settings_model(widget_model);

		if (settings_model && typeof settings_model.trigger === 'function') {
			settings_model.trigger('input');
		}

		if (window.elementor && elementor.channels && elementor.channels.editor) {
			elementor.channels.editor.trigger('change');
		}
	}

	function normalise_rows_text(rows_text) {
		return String(rows_text || '')
			.replace(/\r\n/g, '\n')
			.replace(/\r/g, '\n')
			.split('\n')
			.map(function (line) {
				return line.trim();
			})
			.filter(function (line) {
				return line !== '';
			})
			.join('\n');
	}

	function build_export_rows(settings) {
		var rows = [];
		var export_keys = [
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
			'show_empty_message'
		];

		rows.push([
			'row_type',
			'device',
			'model',
			'service',
			'applecare',
			'price',
			'terms_text',
			'setting_key',
			'setting_value'
		]);

		export_keys.forEach(function (key) {
			rows.push([
				'setting',
				'',
				'',
				'',
				'',
				'',
				'',
				key,
				settings[key] == null ? '' : settings[key]
			]);
		});

		(settings.mappings || []).forEach(function (mapping) {
			var terms_text = String(mapping.terms_text || '');
			var row_lines = normalise_rows_text(mapping.rows_text).split('\n').filter(function (line) {
				return line !== '';
			});

			if (!row_lines.length) {
				rows.push([
					'mapping',
					mapping.device || '',
					mapping.model || '',
					'',
					'',
					'',
					terms_text,
					'',
					''
				]);
				return;
			}

			row_lines.forEach(function (line, index) {
				var parts = line.split('|').map(function (part) {
					return String(part || '').trim();
				});

				rows.push([
					'mapping',
					mapping.device || '',
					mapping.model || '',
					parts[0] || '',
					parts[1] || '',
					parts[2] || '',
					index === 0 ? terms_text : '',
					'',
					''
				]);
			});
		});

		return rows;
	}

	function build_csv(settings) {
		return build_export_rows(settings)
			.map(function (row) {
				return row.map(csv_escape).join(',');
			})
			.join('\n');
	}

	function create_mapping_id(index) {
		return 'rpscsv' + String(index + 1) + Math.random().toString(36).slice(2, 8);
	}

	function import_csv_into_settings(csv_text, widget_model) {
		var rows = parse_csv(csv_text);
		var header = rows.shift() || [];
		var columns = {};
		var mappings = [];
		var mappings_index = {};
		var imported_settings = {};
		var allowed_setting_keys = {
			panel_template_id: true,
			dropdown_layout: true,
			auto_select_first_model: true,
			device_label: true,
			model_label: true,
			placeholder_device: true,
			placeholder_model: true,
			default_device: true,
			default_model: true,
			enable_applecare_column: true,
			col_label_applecare: true,
			col_label_price: true,
			hide_applecare_when_empty: true,
			show_empty_message: true
		};

		if (!header.length) {
			throw new Error('The CSV is empty.');
		}

		header.forEach(function (name, index) {
			columns[String(name || '').trim()] = index;
		});

		if (typeof columns.row_type === 'undefined') {
			throw new Error('The CSV must include a row_type column.');
		}

		rows.forEach(function (row) {
			var row_type = String(row[columns.row_type] || '').trim().toLowerCase();
			var device = String(row[columns.device] || '').trim();
			var model = String(row[columns.model] || '').trim();
			var service = String(row[columns.service] || '').trim();
			var applecare = String(row[columns.applecare] || '').trim();
			var price = String(row[columns.price] || '').trim();
			var terms_text = String(row[columns.terms_text] || '').trim();
			var setting_key = String(row[columns.setting_key] || '').trim();
			var setting_value = String(row[columns.setting_value] || '').trim();
			var mapping_key;
			var row_text;

			if (row_type === 'setting') {
				if (!setting_key || !allowed_setting_keys[setting_key]) {
					return;
				}

				imported_settings[setting_key] = setting_value;
				return;
			}

			if (row_type !== 'mapping') {
				return;
			}

			if (!device || !model) {
				return;
			}

			mapping_key = device + '||' + model;
			if (!mappings_index[mapping_key]) {
				mappings_index[mapping_key] = {
					_id: create_mapping_id(mappings.length),
					device: device,
					model: model,
					rows_text: '',
					terms_text: ''
				};
				mappings.push(mappings_index[mapping_key]);
			}

			if (terms_text && !mappings_index[mapping_key].terms_text) {
				mappings_index[mapping_key].terms_text = terms_text;
			}

			if (!service && !applecare && !price) {
				return;
			}

			row_text = [service, applecare, price].join(' | ');
			mappings_index[mapping_key].rows_text = mappings_index[mapping_key].rows_text
				? mappings_index[mapping_key].rows_text + '\n' + row_text
				: row_text;
		});

		set_widget_setting(widget_model, 'mappings', mappings);

		Object.keys(imported_settings).forEach(function (key) {
			if (key === 'panel_template_id') {
				return;
			}

			set_widget_setting(widget_model, key, imported_settings[key]);
		});

		trigger_editor_refresh(widget_model);

		return {
			mapping_count: mappings.length,
			settings_count: Object.keys(imported_settings).length
		};
	}

	function download_csv(filename, content) {
		var blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
		var url;
		var link;

		if (window.navigator && typeof window.navigator.msSaveOrOpenBlob === 'function') {
			window.navigator.msSaveOrOpenBlob(blob, filename);
			return;
		}

		url = (window.URL || window.webkitURL).createObjectURL(blob);
		link = document.createElement('a');
		link.href = url;
		link.download = filename;
		link.style.display = 'none';
		document.body.appendChild(link);

		if (typeof link.click === 'function') {
			link.click();
		} else {
			link.dispatchEvent(new MouseEvent('click'));
		}

		setTimeout(function () {
			document.body.removeChild(link);
			(window.URL || window.webkitURL).revokeObjectURL(url);
		}, 100);
	}

	function get_export_filename() {
		var date = new Date();
		var stamp = [
			date.getFullYear(),
			String(date.getMonth() + 1).padStart(2, '0'),
			String(date.getDate()).padStart(2, '0')
		].join('');

		return 'repair-pricing-switcher-' + stamp + '.csv';
	}

	function bind_tools() {
		if ($(document).data('rps-csv-bound')) {
			return;
		}

		$(document).data('rps-csv-bound', true);

		$(document).on('click', '[data-rps-export]', function (event) {
			event.preventDefault();

			var $wrap = $(this).closest('#rps-csv-tools');
			var widget_model = get_widget_model();
			var settings;
			var csv;

			if (!$wrap.length) {
				$wrap = $('#rps-csv-tools').first();
			}

			if (!widget_model) {
				$wrap.find('[data-rps-status]').html('<span class="rps-csv-tools__error">Unable to find the current widget. Please close and reopen the widget editor, then try again.</span>');
				return;
			}

			settings = get_settings_json(widget_model);
			csv = build_csv(settings);
			download_csv(get_export_filename(), csv);
			$wrap.find('[data-rps-status]').text('CSV exported.');
		});

		$(document).on('change', '[data-rps-import-input]', function (event) {
			var $input = $(this);
			var $wrap = $input.closest('#rps-csv-tools');
			var widget_model = get_widget_model();
			var file = event.target.files && event.target.files[0];
			var reader;

			if (!file) {
				return;
			}

			if (!widget_model) {
				$wrap.find('[data-rps-status]').html('<span class="rps-csv-tools__error">Unable to find the current widget. Please close and reopen the widget editor, then try again.</span>');
				$input.val('');
				return;
			}

			reader = new FileReader();
			reader.onload = function (load_event) {
				try {
					var result = import_csv_into_settings(load_event.target.result, widget_model);
					$wrap.find('[data-rps-status]').text('CSV imported. ' + result.mapping_count + ' device/model mappings updated, ' + result.settings_count + ' settings applied.');
				} catch (error) {
					$wrap.find('[data-rps-status]').html('<span class="rps-csv-tools__error">' + escape_html(error.message || 'The CSV could not be imported.') + '</span>');
				}

				$input.val('');
			};
			reader.onerror = function () {
				$wrap.find('[data-rps-status]').html('<span class="rps-csv-tools__error">The selected file could not be read.</span>');
				$input.val('');
			};
			reader.readAsText(file);
		});
	}

	$(window).on('elementor:init', function () {
		bind_tools();

		if (window.elementor && elementor.hooks && elementor.hooks.addAction) {
			elementor.hooks.addAction('panel/open_editor/widget/' + widget_name, function () {
				setTimeout(bind_tools, 50);
			});
		}

		$(document).on('DOMNodeInserted', function (event) {
			if ($(event.target).find('#rps-csv-tools').length || $(event.target).is('#rps-csv-tools')) {
				bind_tools();
			}
		});
	});
})(jQuery);
