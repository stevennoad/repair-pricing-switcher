(function ($) {
	function uniq(arr) {
		return Array.from(new Set(arr));
	}

	function buildOptions($select, items, placeholder, includePlaceholder) {
		var html = '';
		if (includePlaceholder) {
			html += '<option value="">' + (placeholder || 'Select…') + '</option>';
		}
		items.forEach(function (item) {
			html += '<option value="' + String(item).replace(/"/g, '&quot;') + '">' + item + '</option>';
		});

		$select.html(html);
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function isApplecareEmpty(rows) {
		if (!rows || !rows.length) {
			return true;
		}

		return rows.every(function (r) {
			return !String(r.applecare || '').trim();
		});
	}

	function renderPricesHtml(rows, labels, hideApplecare, showEmptyMessage) {
		if (!rows || !rows.length) {
			return showEmptyMessage ? '<div class="dms_prices__empty">No pricing rows set for this model.</div>' : '';
		}

		var applecareHidden = hideApplecare && isApplecareEmpty(rows);

		var html = '';
		html += '<div class="dms_prices__row dms_prices__row--head">';
		html += '<div class="dms_prices__col dms_prices__col--service"></div>';

		if (!applecareHidden) {
			html += '<div class="dms_prices__col dms_prices__col--ac">' + escapeHtml(labels.col_label_applecare || 'AppleCare+') + '</div>';
		}

		html += '<div class="dms_prices__col dms_prices__col--price">' + escapeHtml(labels.col_label_price || 'Price') + '</div>';
		html += '</div>';

		rows.forEach(function (r) {
			html += '<div class="dms_prices__row">';

			html += '<div class="dms_prices__col dms_prices__col--service">' + escapeHtml(r.service || '') + '</div>';

			if (!applecareHidden) {
				html += '<div class="dms_prices__col dms_prices__col--ac">' + escapeHtml(r.applecare || '') + '</div>';
			}

			html += '<div class="dms_prices__col dms_prices__col--price">' + escapeHtml(r.price || '') + '</div>';
			html += '</div>';
		});

		return html;
	}

	function updateFloatingLabels($root) {
		$root.find('.dms__field').each(function () {
			var $field = $(this);
			var $select = $field.find('select');
			if ($select.val()) {
				$field.addClass('dms__field--has-value');
				return;
			}
			$field.removeClass('dms__field--has-value');
		});
	}

	function updateMounts($root, rows, labels, hideApplecare, showEmptyMessage) {
		var html = renderPricesHtml(rows, labels, hideApplecare, showEmptyMessage);
		$root.find('[data-dms-prices]').each(function () {
			$(this).html(html);
		});
	}

	function initDms($scope) {
		var $root = $scope.hasClass('dms') ? $scope : $scope.find('.dms').first();
		if (!$root.length) {
			return;
		}

		var raw = $root.attr('data-dms-config') || '{}';
		var config = {};

		try {
			config = JSON.parse(raw);
		} catch (e) {
			config = {};
		}

		var index = (config && config.index) ? config.index : {};
		var devices = uniq(Object.keys(index));

		var labels = {
			col_label_applecare: config.col_label_applecare,
			col_label_price: config.col_label_price
		};

		var placeholders = {
			device: (config.placeholder_device || 'Select…'),
			model: (config.placeholder_model || 'Select…')
		};

		var hideApplecare = String(config.hide_applecare_when_empty || 'no') === 'yes';
		var showEmptyMessage = String(config.show_empty_message || 'yes') === 'yes';
		var autoPickGlobal = String(config.auto_select_first_model || 'yes') === 'yes';

		var $device = $root.find('.dms__select--device');
		var $model = $root.find('.dms__select--model');

		buildOptions($device, devices, placeholders.device, !autoPickGlobal);
		buildOptions($model, [], placeholders.model, !autoPickGlobal);
		$model.prop('disabled', true);

		updateMounts($root, [], labels, hideApplecare, showEmptyMessage);
		updateFloatingLabels($root);

		$device.off('change.dms').on('change.dms', function () {
			var deviceVal = $(this).val() || '';
			var models = [];

			if (deviceVal && index[deviceVal]) {
				models = uniq(Object.keys(index[deviceVal]));
			}

			buildOptions($model, models, placeholders.model, !autoPickGlobal);

			if (!models.length) {
				$model.prop('disabled', true);
				updateMounts($root, [], labels, hideApplecare, showEmptyMessage);
				updateFloatingLabels($root);
				return;
			}

						$model.prop('disabled', false);
			var preferredModel = String(config.default_model || '').trim();
			var preferredDevice = String(config.default_device || '').trim();
			var autoPick = autoPickGlobal;

			if (preferredDevice && preferredModel && deviceVal === preferredDevice && models.indexOf(preferredModel) !== -1) {
				$model.val(preferredModel);
			} else if (autoPick && models.length) {
				$model.val(models[0]);
			} else {
				$model.val('');
			}

			$model.trigger('change.dms');
			updateFloatingLabels($root);
			updateFloatingLabels($root);
		});

		$model.off('change.dms').on('change.dms', function () {
			var deviceVal = $device.val() || '';
			var modelVal = $(this).val() || '';

			if (!deviceVal || !modelVal || !index[deviceVal] || !index[deviceVal][modelVal]) {
				updateMounts($root, [], labels, hideApplecare, showEmptyMessage);
				updateFloatingLabels($root);
				return;
			}

			updateMounts($root, index[deviceVal][modelVal], labels, hideApplecare, showEmptyMessage);
			updateFloatingLabels($root);
		});

		if (devices.length) {
			var preferredDevice = String(config.default_device || '').trim();
			var deviceToSelect = (preferredDevice && devices.indexOf(preferredDevice) !== -1) ? preferredDevice : devices[0];
			$device.val(deviceToSelect).trigger('change.dms');
		}
	}

	$(window).on('elementor/frontend/init', function () {
		if (!window.elementorFrontend) {
			return;
		}

		elementorFrontend.hooks.addAction('frontend/element_ready/dms_device_model_switcher.default', function ($scope) {
			initDms($scope);
		});
	});
})(jQuery);
