<?php

echo $this->session->flashdata('saved');

if (is_demo_mode()) {
	echo msgbox('notice large', lang('auth.ldap.demo_notice'));
}

$render_error = static function ($field) {
	$error = form_error($field);
	return $error ? "<div class='cps-admin-form-error'>{$error}</div>" : '';
};

$code = static function ($value) {
	return '<code>' . html_escape($value) . '</code>';
};

$render_help = static function ($content) {
	return $content === '' ? '' : "<div class='cps-admin-form-help'>{$content}</div>";
};

$render_field = static function ($field, $label, $control, $help = '') use ($render_error, $render_help) {
	return "
		<div class='cps-admin-form-field'>
			<label for='{$field}'>{$label}</label>
			<div class='cps-admin-form-control'>
				{$control}
				{$render_help($help)}
				{$render_error($field)}
			</div>
		</div>
	";
};

$render_toggle = static function ($field, $checked, $title, $help = '', $disabled = false) use ($render_error, $render_help) {
	$attrs = [
		'name' => $field,
		'id' => $field,
		'value' => '1',
		'tabindex' => tab_index(),
		'checked' => ($checked == '1'),
	];
	if ($disabled) {
		$attrs['checked'] = false;
		$attrs['disabled'] = 'disabled';
	}

	$html = form_hidden($field, '0');
	$html .= "<label for='{$field}' class='cps-admin-toggle-card'>";
	$html .= form_checkbox($attrs);
	$html .= "<span class='cps-admin-toggle-card-indicator' aria-hidden='true'></span>";
	$html .= "<span class='cps-admin-toggle-card-body'>";
	$html .= "<span class='cps-admin-toggle-card-title'>" . html_escape($title) . "</span>";
	$html .= $render_help($help);
	$html .= "</span></label>";
	$html .= $render_error($field);
	return $html;
};

echo form_open(current_url(), [
	'id' => 'ldap_settings',
	'class' => 'cssform cps-admin-form cps-ldap-admin-form cps-ldap-page',
	'ldap-settings' => '',
]);

?>

<div class="cps-admin-form-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h2><?= lang('auth.ldap.ldap') ?></h2>
			<p><?= lang('auth.ldap.summary') ?></p>
		</div>
	</div>

	<div class="cps-admin-form-grid">
		<div class="cps-admin-form-field">
			<label for="ldap_enabled"><?= lang('auth.ldap.field.ldap_enabled') ?></label>
			<div class="cps-admin-form-control">
				<?php
				$field = 'ldap_enabled';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				echo $render_toggle($field, $value, lang('auth.ldap.field.ldap_enabled.title'), '', is_demo_mode());
				?>
			</div>
		</div>

		<div class="cps-admin-form-field">
			<label for="ldap_create_users"><?= lang('auth.ldap.field.ldap_create_users') ?></label>
			<div class="cps-admin-form-control">
				<?php
				$field = 'ldap_create_users';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				$help = html_escape(lang('auth.ldap.field.ldap_create_users.hint.1')) . '<br>' . html_escape(lang('auth.ldap.field.ldap_create_users.hint.2'));
				echo $render_toggle($field, $value, lang('auth.ldap.field.ldap_create_users.title'), $help);
				?>
			</div>
		</div>
	</div>
</div>

<div class="cps-admin-form-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h3><?= lang('auth.ldap.connection') ?></h3>
			<p><?= lang('auth.ldap.field.ldap_server.hint') ?></p>
		</div>
	</div>

	<?php
	$field = 'ldap_server';
	$value = set_value($field, element($field, $settings), FALSE);
	$control = form_input([
		'name' => $field,
		'id' => $field,
		'maxlength' => '100',
		'tabindex' => tab_index(),
		'value' => $value,
		'class' => 'cps-admin-form-input',
	]);
	echo $render_field($field, lang('auth.ldap.field.ldap_server'), $control);
	?>

	<div class="cps-admin-form-grid cps-admin-form-grid-compact">
		<?php
		$field = 'ldap_port';
		$value = set_value($field, element($field, $settings), FALSE);
		$control = form_input([
			'type' => 'number',
			'name' => $field,
			'id' => $field,
			'maxlength' => '5',
			'tabindex' => tab_index(),
			'value' => $value,
			'class' => 'cps-admin-form-input',
		]);
		echo $render_field($field, lang('auth.ldap.field.ldap_port'), $control, lang('auth.ldap.field.ldap_port.hint'));

		$field = 'ldap_version';
		$value = set_value($field, element($field, $settings, 3), FALSE);
		$control = form_input([
			'type' => 'number',
			'name' => $field,
			'id' => $field,
			'maxlength' => '5',
			'tabindex' => tab_index(),
			'value' => $value,
			'class' => 'cps-admin-form-input',
		]);
		echo $render_field($field, lang('auth.ldap.field.ldap_version'), $control, lang('auth.ldap.field.ldap_version.hint'));
		?>
	</div>

	<div class="cps-admin-form-grid">
		<div class="cps-admin-form-field">
			<label for="ldap_use_tls"><?= lang('auth.ldap.field.ldap_use_tls') ?></label>
			<div class="cps-admin-form-control">
				<?php
				$field = 'ldap_use_tls';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				echo $render_toggle($field, $value, lang('auth.ldap.field.ldap_use_tls'));
				?>
			</div>
		</div>

		<div class="cps-admin-form-field">
			<label for="ldap_ignore_cert"><?= lang('auth.ldap.field.ldap_ignore_cert') ?></label>
			<div class="cps-admin-form-control">
				<?php
				$field = 'ldap_ignore_cert';
				$value = set_value($field, element($field, $settings, '0'), FALSE);
				echo $render_toggle($field, $value, lang('auth.ldap.field.ldap_ignore_cert'));
				?>
			</div>
		</div>
	</div>
</div>

<div class="cps-admin-form-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h3><?= lang('auth.ldap.search') ?></h3>
			<p><?= lang('auth.ldap.field.ldap_bind_dn_format.hint') ?></p>
		</div>
	</div>

	<?php
	$field = 'ldap_bind_dn_format';
	$value = set_value($field, element($field, $settings), FALSE);
	$control = form_textarea([
		'name' => $field,
		'id' => $field,
		'rows' => '3',
		'tabindex' => tab_index(),
		'value' => $value,
		'class' => 'cps-admin-form-textarea cps-admin-form-textarea-compact',
	]);
	$help = '<div class="cps-admin-code-list">' . $code('EXAMPLE.LOCAL\:user') . $code(':user@EXAMPLE.LOCAL') . $code('uid=:user,cn=users,dc=example,dc=com') . '</div>';
	echo $render_field($field, lang('auth.ldap.field.ldap_bind_dn_format'), $control, $help);

	$field = 'ldap_base_dn';
	$value = set_value($field, element($field, $settings), FALSE);
	$control = form_textarea([
		'name' => $field,
		'id' => $field,
		'rows' => '3',
		'tabindex' => tab_index(),
		'value' => $value,
		'class' => 'cps-admin-form-textarea cps-admin-form-textarea-compact',
	]);
	echo $render_field($field, lang('auth.ldap.field.ldap_base_dn'), $control, lang('app.example') . ': ' . $code('dc=example,dc=local'));

	$field = 'ldap_search_filter';
	$value = set_value($field, element($field, $settings), FALSE);
	$control = form_textarea([
		'name' => $field,
		'id' => $field,
		'rows' => '5',
		'tabindex' => tab_index(),
		'value' => $value,
		'class' => 'cps-admin-form-textarea',
	]);
	$help = lang('app.example') . ': ' . $code('(&(:attr=:user))') . '<br>' . html_escape(lang('auth.ldap.field.ldap_search_filter.hint'));
	echo $render_field($field, lang('auth.ldap.field.ldap_search_filter'), $control, $help);
	?>
</div>

<div class="cps-admin-form-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h3><?= lang('auth.ldap.user_attribute_mapping') ?></h3>
			<p><?= lang('auth.ldap.user_attribute_mapping.hint.1') ?></p>
		</div>
	</div>

	<div class="cps-admin-form-help cps-admin-form-card-note">
		<?= lang('auth.ldap.user_attribute_mapping.hint.2') ?><?= $code(':givenName :sn') ?>.
		<br><?= lang('auth.ldap.user_attribute_mapping.hint.3') ?>
	</div>

	<div class="cps-admin-form-grid">
		<?php
		$fields = [
			'ldap_attr_firstname' => [lang('user.field.firstname'), lang('app.example') . ': ' . $code('givenName')],
			'ldap_attr_lastname' => [lang('user.field.lastname'), lang('app.example') . ': ' . $code('sn')],
			'ldap_attr_displayname' => [lang('user.field.displayname'), lang('app.example') . ': ' . $code('displayName') . ' / ' . $code(':givenName :sn')],
			'ldap_attr_email' => [lang('user.field.email'), lang('app.example') . ': ' . $code('mail')],
		];
		foreach ($fields as $field => $config) {
			$value = set_value($field, element($field, $settings), FALSE);
			$control = form_input([
				'name' => $field,
				'id' => $field,
				'maxlength' => '100',
				'tabindex' => tab_index(),
				'value' => $value,
				'class' => 'cps-admin-form-input',
			]);
			echo $render_field($field, $config[0], $control, $config[1]);
		}
		?>
	</div>
</div>

<div class="cps-admin-form-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h3><?= lang('auth.ldap.user_assignments') ?></h3>
			<p><?= lang('auth.ldap.field.ldap_create_users.title') ?></p>
		</div>
	</div>

	<div class="cps-admin-form-grid">
		<?php
		$field = 'ldap_default_role_id';
		$value = set_value($field, element($field, $settings, ''), FALSE);
		$control = form_dropdown([
			'name' => 'ldap_default_role_id',
			'id' => 'ldap_default_role_id',
			'options' => $role_options,
			'selected' => $value,
			'tabindex' => tab_index(),
			'class' => 'cps-admin-form-input',
		]);
		echo $render_field($field, lang('role.role'), $control);

		$field = 'ldap_default_department_id';
		$value = set_value($field, element($field, $settings, ''), FALSE);
		$control = form_dropdown([
			'name' => 'ldap_default_department_id',
			'id' => 'ldap_default_department_id',
			'options' => $department_options,
			'selected' => $value,
			'tabindex' => tab_index(),
			'class' => 'cps-admin-form-input',
		]);
		echo $render_field($field, lang('department.department'), $control);
		?>
	</div>
</div>

<?php
echo "<div class='cps-admin-actions'>";
$this->load->view('partials/submit', [
	'submit' => [lang('app.action.save'), tab_index()],
]);
echo "</div>";

echo form_close();
