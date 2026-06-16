<?php
$attrs = [
	'class' => 'cssform-stacked up-form cps-admin-form cps-ldap-test-form',
	'ldap-test' => '',
	'up-target' => '#ldap_test_results',
	'up-history' => 'false',
];

$hidden = [
	'ldap_server' => '',
	'ldap_port' => '',
	'ldap_version' => '',
	'ldap_use_tls' => '',
	'ldap_ignore_cert' => '',
	'ldap_bind_dn_format' => '',
	'ldap_base_dn' => '',
	'ldap_search_filter' => '',
	'ldap_attr_firstname' => '',
	'ldap_attr_lastname' => '',
	'ldap_attr_displayname' => '',
	'ldap_attr_email' => '',
];

echo form_open('settings/authentication/ldap_test', $attrs, $hidden);
?>

<div class="cps-admin-form-card cps-ldap-test-card">
	<div class="cps-admin-form-card-header">
		<div>
			<h3><?= lang('auth.ldap.test.title') ?></h3>
			<p><?= lang('auth.ldap.test.hint.1') ?></p>
		</div>
	</div>

	<div class="cps-admin-form-help cps-admin-form-card-note"><?= lang('auth.ldap.test.hint.2') ?></div>

	<div class="cps-admin-form-grid">
		<div class="cps-admin-form-field">
			<label for="username"><?= lang('user.field.username') ?></label>
			<div class="cps-admin-form-control">
				<?php
				echo form_input([
					'name' => 'username',
					'id' => 'username',
					'maxlength' => '50',
					'tabindex' => tab_index(),
					'class' => 'cps-admin-form-input',
				]);
				?>
			</div>
		</div>

		<div class="cps-admin-form-field">
			<label for="password"><?= lang('user.field.password') ?></label>
			<div class="cps-admin-form-control">
				<?php
				echo form_password([
					'name' => 'password',
					'id' => 'password',
					'maxlength' => '50',
					'tabindex' => tab_index(),
					'class' => 'cps-admin-form-input',
				]);
				?>
			</div>
		</div>
	</div>

	<div class="cps-admin-actions cps-admin-test-actions">
		<div class="submit">
			<?php
			$button_attrs = [
				'value' => lang('auth.ldap.test.verify'),
				'tabindex' => tab_index(),
			];
			if (is_demo_mode()) {
				$button_attrs['disabled'] = '';
			}
			echo form_submit($button_attrs);
			?>
		</div>
	</div>

	<div class="loading-notice"><?= lang('auth.ldap.test.verifying') ?>...</div>
	<div id="ldap_test_results"></div>
</div>

<?= form_close() ?>
