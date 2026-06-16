<?php

echo $this->session->flashdata('saved');
echo iconbar([
	['setup', lang('app.action.back'), 'arrow_turn_left.png'],
]);

$render_error = static function ($field) {
	$error = form_error($field);
	return $error ? "<div class='cps-settings-error'>{$error}</div>" : '';
};

$render_radio_cards = static function ($field, $value, array $options, array $attrs = []) {
	$html = "<div class='cps-settings-options'>";
	foreach ($options as $opt) {
		$id = "{$field}_{$opt['value']}";
		$input_attrs = [
			'name' => $field,
			'id' => $id,
			'value' => $opt['value'],
			'checked' => ($value == $opt['value']),
			'tabindex' => tab_index(),
		];
		foreach ($attrs as $attr => $attr_value) {
			$input_attrs[$attr] = $attr_value;
		}
		$label_attrs = [
			'for' => $id,
			'class' => trim('cps-settings-option ' . ($opt['class'] ?? '')),
		];
		if (isset($opt['for'])) {
			$label_attrs['up-show-for'] = $opt['for'];
		}
		$label_attrs = _stringify_attributes($label_attrs);
		$label = html_escape($opt['label']);
		$description = isset($opt['description']) && $opt['description'] !== ''
			? "<span class='cps-settings-option-description'>" . html_escape($opt['description']) . "</span>"
			: '';
		$html .= "<label{$label_attrs}>";
		$html .= form_radio($input_attrs);
		$html .= "<span class='cps-settings-option-body'><span class='cps-settings-option-title'>{$label}</span>{$description}</span>";
		$html .= "</label>";
	}
	$html .= "</div>";
	return $html;
};

$render_toggle = static function ($field, $checked, $label, $hint = '') {
	$html = form_hidden($field, '0');
	$html .= "<label for='{$field}' class='cps-settings-toggle'>";
	$html .= form_checkbox([
		'name' => $field,
		'id' => $field,
		'value' => '1',
		'tabindex' => tab_index(),
		'checked' => ($checked == '1'),
	]);
	$html .= "<span class='cps-settings-toggle-control' aria-hidden='true'></span>";
	$html .= "<span class='cps-settings-toggle-text'>";
	$html .= "<span class='cps-settings-toggle-title'>" . html_escape($label) . "</span>";
	if ($hint !== '') {
		$html .= "<span class='cps-settings-toggle-hint'>" . html_escape($hint) . "</span>";
	}
	$html .= "</span></label>";
	return $html;
};

echo form_open(current_url(), ['id' => 'settings', 'class' => 'cssform cps-settings-form']);
?>

<div class="cps-settings-page">

	<fieldset class="cps-settings-section">
		<legend accesskey="S" tabindex="<?= tab_index() ?>"><?= lang('settings.general.bookings') ?></legend>
		<div class="cps-settings-section-header">
			<p><?= lang('settings.general.displaytype.hint') ?></p>
		</div>

		<div class="cps-settings-field" id="settings_displaytype">
			<label class="cps-settings-field-label"><?= lang('settings.general.displaytype.label') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$field = 'displaytype';
				$value = set_value($field, element($field, $settings), FALSE);
				echo $render_radio_cards($field, $value, [
					[
						'value' => 'day',
						'label' => lang('settings.general.displaytype.day'),
						'description' => lang('settings.general.displaytype.day.hint'),
					],
					[
						'value' => 'room',
						'label' => lang('settings.general.displaytype.room'),
						'description' => lang('settings.general.displaytype.room.hint'),
					],
				], ['up-switch' => '.d_columns_target']);
				echo $render_error($field);
				?>
			</div>
		</div>

		<div class="cps-settings-field" id="settings_columns">
			<label class="cps-settings-field-label"><?= lang('settings.general.columns.label') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$field = 'd_columns';
				$value = set_value($field, element($field, $settings), FALSE);
				echo $render_radio_cards($field, $value, [
					[
						'value' => 'periods',
						'label' => lang('settings.general.columns.periods'),
						'for' => '',
						'class' => 'd_columns_target',
					],
					[
						'value' => 'rooms',
						'label' => lang('settings.general.columns.rooms'),
						'for' => 'day',
						'class' => 'd_columns_target',
					],
					[
						'value' => 'days',
						'label' => lang('settings.general.columns.days'),
						'for' => 'room',
						'class' => 'd_columns_target',
					],
				]);
				?>
				<div class="cps-settings-help"><?= lang('settings.general.columns.hint') ?></div>
				<?= $render_error($field) ?>
			</div>
		</div>

		<div class="cps-settings-field" id="settings_highlight">
			<label class="cps-settings-field-label"><?= lang('settings.general.grid_highlight.label') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$field = 'grid_highlight';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				echo $render_toggle($field, $value, lang('settings.general.grid_highlight.label'), lang('settings.general.grid_highlight.hint'));
				echo $render_error($field);
				?>
			</div>
		</div>
	</fieldset>

	<fieldset class="cps-settings-section">
		<legend accesskey="D" tabindex="<?= tab_index() ?>"><?= lang('settings.general.datetime') ?></legend>
		<div class="cps-settings-section-header">
			<?php
			$link = anchor('https://www.php.net/manual/en/function.date.php#refsect1-function.date-parameters', lang('settings.general.datetime.link'), ['target' => '_blank', 'rel' => 'noopener', 'class' => 'cps-settings-help-link']);
			?>
			<p><?= html_escape(lang('settings.general.datetime.hint')) ?> <?= $link ?></p>
		</div>

		<div class="cps-settings-field">
			<label class="cps-settings-field-label" for="timezone"><?= lang('settings.general.timezone.label') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$value = set_value('timezone', element('timezone', $settings, date_default_timezone_get()), FALSE);
				echo form_dropdown([
					'name' => 'timezone',
					'id' => 'timezone',
					'options' => $timezones,
					'selected' => $value,
					'tabindex' => tab_index(),
					'up-autocomplete' => '',
					'class' => 'cps-settings-select',
				]);
				echo $render_error('timezone');
				?>
			</div>
		</div>

		<?php
		$date_fields = [
			'pattern_long' => [
				'label' => lang('settings.general.date_format_long.label'),
				'hint' => lang('settings.general.date_format_long.hint'),
				'options' => ['' => '(Default)'] + $date_pattern_options,
				'value' => element('pattern_long', $date_settings),
			],
			'pattern_weekday' => [
				'label' => lang('settings.general.date_format_weekday.label'),
				'hint' => lang('settings.general.date_format_weekday.hint'),
				'options' => ['' => '(Default)'] + $date_pattern_options,
				'value' => element('pattern_weekday', $date_settings),
			],
			'pattern_time' => [
				'label' => lang('settings.general.time_format_period.label'),
				'hint' => lang('settings.general.time_format_period.hint'),
				'options' => ['' => '(Default)'] + $time_pattern_options,
				'value' => element('pattern_time', $date_settings),
			],
		];
		foreach ($date_fields as $field => $config):
			$value = set_value($field, $config['value'], FALSE);
		?>
			<div class="cps-settings-field">
				<label class="cps-settings-field-label" for="<?= $field ?>"><?= $config['label'] ?></label>
				<div class="cps-settings-field-control">
					<?php
					echo form_dropdown([
						'name' => $field,
						'id' => $field,
						'options' => $config['options'],
						'selected' => $value,
						'tabindex' => tab_index(),
						'value' => $value,
						'class' => 'cps-settings-select',
					]);
					?>
					<div class="cps-settings-help"><?= $config['hint'] ?></div>
					<?= $render_error($field) ?>
				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>

	<fieldset class="cps-settings-section">
		<legend accesskey="L" tabindex="<?= tab_index() ?>"><?= lang('settings.general.login_message') ?></legend>
		<div class="cps-settings-section-header">
			<p><?= lang('settings.general.login_message.hint') ?></p>
		</div>

		<div class="cps-settings-field">
			<label class="cps-settings-field-label" for="login_message_enabled"><?= lang('app.enable') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$field = 'login_message_enabled';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				echo $render_toggle($field, $value, lang('settings.general.login_message'));
				?>
			</div>
		</div>

		<div class="cps-settings-field">
			<?php
			$field = 'login_message_text';
			$value = set_value($field, element($field, $settings, ''), FALSE);
			?>
			<label class="cps-settings-field-label" for="<?= $field ?>"><?= lang('settings.general.login_message_text') ?></label>
			<div class="cps-settings-field-control">
				<?php
				echo form_textarea([
					'name' => $field,
					'id' => $field,
					'rows' => '5',
					'tabindex' => tab_index(),
					'value' => $value,
					'class' => 'cps-settings-textarea',
				]);
				echo $render_error($field);
				?>
			</div>
		</div>
	</fieldset>

	<fieldset class="cps-settings-section cps-settings-section-danger">
		<legend accesskey="M" tabindex="<?= tab_index() ?>"><?= lang('settings.general.maintenance_mode') ?></legend>
		<div class="cps-settings-section-header">
			<p><?= lang('settings.general.maintenance_mode.hint') ?></p>
		</div>

		<div class="cps-settings-field">
			<label class="cps-settings-field-label" for="maintenance_mode"><?= lang('app.enable') ?></label>
			<div class="cps-settings-field-control">
				<?php
				$value = set_value('maintenance_mode', element('maintenance_mode', $settings, '0'), FALSE);
				echo $render_toggle('maintenance_mode', $value, lang('settings.general.maintenance_mode'));
				?>
			</div>
		</div>

		<div class="cps-settings-field">
			<?php
			$field = 'maintenance_mode_message';
			$value = set_value($field, element($field, $settings, ''), FALSE);
			?>
			<label class="cps-settings-field-label" for="<?= $field ?>"><?= lang('settings.general.maintenance_mode_message') ?></label>
			<div class="cps-settings-field-control">
				<?php
				echo form_textarea([
					'name' => $field,
					'id' => $field,
					'rows' => '5',
					'tabindex' => tab_index(),
					'value' => $value,
					'class' => 'cps-settings-textarea',
				]);
				?>
				<div class="cps-settings-help"><?= lang('settings.general.maintenance_mode_message.hint') ?></div>
				<?= $render_error($field) ?>
			</div>
		</div>
	</fieldset>

	<?php if ( ! empty($feature_list)): ?>
	<fieldset class="cps-settings-section">
		<legend accesskey="X" tabindex="<?= tab_index() ?>"><?= lang('settings.general.experimental_features') ?></legend>
		<div class="cps-settings-section-header">
			<p><?= lang('settings.general.experimental_features.hint') ?></p>
		</div>

		<div class="cps-settings-feature-list">
			<?php
			foreach ($feature_list as $feature_name) {
				$field = $feature_name;
				$title = lang("features_{$feature_name}");
				$description = lang("features_{$feature_name}_description");
				$value = set_value($field, element($field, $settings_features, '0'), FALSE);
				echo "<div class='cps-settings-feature'>";
				echo $render_toggle($field, $value, $title, $description);
				echo "</div>";
			}
			?>
		</div>
	</fieldset>
	<?php endif; ?>

	<div class="cps-settings-actions">
		<?php
		$this->load->view('partials/submit', [
			'submit' => [lang('app.action.save'), tab_index()],
			'cancel' => [lang('app.action.cancel'), tab_index(), 'setup'],
		]);
		?>
	</div>

</div>

<?php
echo form_close();
