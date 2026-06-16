<?php

echo $this->session->flashdata('saved');
?>

<div class="cps-admin-page">
	<div class="cps-admin-toolbar">
		<div>
			<?= anchor('setup', cps_icon('chevron-left', 'w-4 h-4') . lang('app.action.back'), ['class' => 'cps-admin-btn']) ?>
		</div>
		<div class="cps-admin-toolbar-actions">
			<?= anchor('users/add', cps_icon('plus', 'w-4 h-4') . lang('user.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?>
			<?= anchor('users/import', cps_icon('users', 'w-4 h-4') . lang('user.import_from_csv'), ['class' => 'cps-admin-btn']) ?>
		</div>
	</div>

	<?php $this->load->view('users/filter'); ?>

	<div id="users_list">
		<?php if (empty($users)): ?>
			<div class="cps-admin-empty">
				<p><?= lang('user.no_items') ?></p>
				<div class="mt-4 flex justify-center gap-2">
					<?= anchor('users/add', cps_icon('plus', 'w-4 h-4') . lang('user.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?>
					<?= anchor('users/import', lang('user.import_from_csv'), ['class' => 'cps-admin-btn']) ?>
				</div>
			</div>
		<?php else: ?>
			<div class="cps-admin-table-wrap">
				<table class="cps-admin-table">
					<thead>
						<tr>
							<th><?= sort_link('users', 'enabled', lang('user.field.enabled')) ?></th>
							<th><?= sort_link('users', 'username', lang('user.field.username')) ?></th>
							<th><?= sort_link('users', 'displayname', lang('user.field.displayname')) ?></th>
							<th><?= sort_link('users', 'role', lang('role.role')) ?></th>
							<th><?= sort_link('users', 'department', lang('department.department')) ?></th>
							<th><?= sort_link('users', 'lastlogin', lang('user.last_logged_in')) ?></th>
							<th><?= lang('app.actions') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<?php
							$enabled_html = ($user->enabled == 1)
								? "<span class='cps-admin-status cps-admin-status-success'>" . cps_icon('check', 'w-4 h-4') . lang('app.yes') . "</span>"
								: "<span class='cps-admin-status cps-admin-status-danger'>" . cps_icon('x', 'w-4 h-4') . lang('app.no') . "</span>";
							$username_html = anchor('users/edit/'.$user->user_id, html_escape($user->username));
							$display = $user->displayname == '' ? $user->username : $user->displayname;
							$last_login_html = ($user->lastlogin == '0000-00-00 00:00:00' || empty($user->lastlogin))
								? lang('app.never')
								: date("d/m/Y H:i", strtotime((string) $user->lastlogin));
							$actions_html = $this->load->view('partials/editdelete', [
								'edit' => 'users/edit/' . $user->user_id,
								'delete' => 'users/delete/' . $user->user_id,
							], TRUE);
							?>
							<tr>
								<td data-label="<?= lang('user.field.enabled') ?>"><?= $enabled_html ?></td>
								<td data-label="<?= lang('user.field.username') ?>"><?= $username_html ?></td>
								<td data-label="<?= lang('user.field.displayname') ?>"><?= html_escape($display) ?></td>
								<td data-label="<?= lang('role.role') ?>"><?= html_escape($user->role) ?></td>
								<td data-label="<?= lang('department.department') ?>"><?= html_escape($user->department) ?></td>
								<td data-label="<?= lang('user.last_logged_in') ?>"><?= $last_login_html ?></td>
								<td data-label="<?= lang('app.actions') ?>"><?= $actions_html ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?= $pagelinks ?>
		<?php endif; ?>
	</div>
</div>
