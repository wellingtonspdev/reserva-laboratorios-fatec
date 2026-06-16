<?php
$saved = $this->session->flashdata('saved');

echo form_open('profile/new_password', [
	'class' => 'cps-admin-form cps-password-reset-form',
	'id' => 'new_password',
]);
?>

<div class="cps-password-reset-page">
	<div class="cps-admin-form-card cps-password-reset-card">
		<div class="cps-admin-form-card-header">
			<div>
				<h2><?= lang('account.password.title') ?></h2>
				<p><?= lang('account.password.intro.1') ?></p>
			</div>
		</div>

		<?php if (!empty($saved)): ?>
			<div class="cps-password-reset-message"><?= $saved ?></div>
		<?php endif; ?>

		<div class="cps-admin-form-grid">
			<div class="cps-admin-form-field">
				<label for="password1"><?= lang('user.field.password') ?></label>
				<div class="cps-admin-form-control">
					<?php
					echo form_password([
						'name' => 'password1',
						'id' => 'password1',
						'tabindex' => tab_index(),
						'value' => '',
						'class' => 'cps-admin-form-input',
						'autocomplete' => 'new-password',
					]);
					?>
					<div class="cps-admin-form-help"><?= lang('user.field.password.hint') ?></div>
					<div class="cps-admin-form-error"><?= form_error('password1') ?></div>
				</div>
			</div>

			<div class="cps-admin-form-field">
				<label for="password2"><?= lang('user.field.password2') ?></label>
				<div class="cps-admin-form-control">
					<?php
					echo form_password([
						'name' => 'password2',
						'id' => 'password2',
						'tabindex' => tab_index(),
						'value' => '',
						'class' => 'cps-admin-form-input',
						'autocomplete' => 'new-password',
					]);
					?>
					<div class="cps-admin-form-error"><?= form_error('password2') ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="cps-admin-actions cps-password-reset-actions">
		<div class="submit">
			<?php
			echo form_button([
				'type' => 'submit',
				'content' => lang('account.password.submit'),
				'tabindex' => tab_index(),
			]);
			?>
		</div>
	</div>
</div>

<?php
echo form_close();
