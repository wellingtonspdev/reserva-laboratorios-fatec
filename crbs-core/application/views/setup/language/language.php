<?php
echo $this->session->flashdata('saved');
echo iconbar([
	['setup', lang('app.action.back'), 'arrow_turn_left.png'],
]);
echo form_open(current_url(), ['id' => 'language', 'class'=>'cssform']);
?>


<fieldset>

	<legend accesskey="L" tabindex="<?php echo tab_index(); ?>"><?= lang('settings.settings') ?></legend>

	<p>
		<label class="required"><?= lang('language.field.languages') ?></label>
		<?php
		$field = 'languages';
		$inputs = '';
		foreach ($all_languages as $lang_id) {

			$check_id = "{$field}_{$lang_id}";

			$checked = false;
			$checked = in_array($lang_id, (array) set_value($field, $settings['languages'] ?? []));

			$title = lang(sprintf('language.lang.%s', $lang_id));
			$title = empty($title) ? $lang_id : html_escape($title);

			$attrs = [
				'name' => "{$field}[]",
				'id' => $check_id,
				'value' => $lang_id,
				'tabindex' => tab_index(),
				'checked' => $checked,
				'class' => 'js-language-option',
				'data-language-id' => $lang_id,
			];

			$hidden = '';
			if ($lang_id === 'english') {
				$attrs['disabled'] = '';
				$attrs['checked'] = true;
				$hidden = form_hidden("{$field}[]", $lang_id);
			}

			$input = form_checkbox($attrs);
			$inputs .= "<label for='{$check_id}' class='ni'>{$hidden}{$input} {$title}</label>";

		}

		echo $inputs;
		?>
	</p>
	<?php echo form_error('languages[]'); ?>


	<p>
		<label for="default_language"><?= lang('language.field.default_language') ?></label>
		<?php
		$field = 'default_language';
		$value = set_value($field, $settings['default_language'] ?? 'english');
		echo form_dropdown([
			'name' => $field,
			'id' => $field,
			'options' => $language_options,
			'selected' => $value,
			'tabindex' => tab_index(),
		]);
		?>
	</p>
	<?php echo form_error($field); ?>


</fieldset>



<?php

$this->load->view('partials/submit', array(
	'submit' => array(lang('app.action.save'), tab_index()),
	'cancel' => array(lang('app.action.cancel'), tab_index(), 'setup'),
));

echo form_close();

?>

<script>
ready(function () {
	var form = document.getElementById('language');
	if (!form) return;

	var select = document.getElementById('default_language');
	var checks = form.querySelectorAll('.js-language-option');
	if (!select || !checks.length) return;

	function syncDefaultLanguageOptions() {
		var enabled = {};
		checks.forEach(function (check) {
			if (check.checked || check.value === 'english') {
				enabled[check.value] = true;
			}
		});

		Array.prototype.forEach.call(select.options, function (option) {
			option.disabled = !enabled[option.value];
		});

		if (!enabled[select.value]) {
			select.value = enabled['portuguese-brazilian'] ? 'portuguese-brazilian' : 'english';
		}
	}

	checks.forEach(function (check) {
		check.addEventListener('change', syncDefaultLanguageOptions);
	});

	syncDefaultLanguageOptions();
});
</script>
