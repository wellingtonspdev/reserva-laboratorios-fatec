<?php

echo $this->session->flashdata('saved');
?>

<div class="cps-admin-page">
	<div class="cps-admin-toolbar">
		<div>
			<?= anchor('setup', cps_icon('chevron-left', 'w-4 h-4') . lang('app.action.back'), ['class' => 'cps-admin-btn']) ?>
		</div>
		<div class="cps-admin-toolbar-actions">
			<?= anchor('roles/add', cps_icon('plus', 'w-4 h-4') . lang('role.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?>
		</div>
	</div>

	<?php if (empty($roles)): ?>
		<div class="cps-admin-empty">
			<p><?= lang('role.no_items') ?></p>
			<div class="mt-4"><?= anchor('roles/add', cps_icon('plus', 'w-4 h-4') . lang('role.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?></div>
		</div>
	<?php else: ?>
		<div class="cps-admin-table-wrap">
			<table class="cps-admin-table">
				<thead>
					<tr>
						<th><?= lang('role.field.name') ?></th>
						<th><?= lang('role.field.description') ?></th>
						<th><?= lang('role.field.user_count') ?></th>
						<th><?= lang('constraint.max_active_bookings.short') ?></th>
						<th><?= lang('app.actions') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($roles as $role): ?>
						<?php
						$name_html = anchor('roles/edit/' . $role->role_id, html_escape($role->name));
						$user_count = sprintf('%d', $role->user_count);
						$max_active_bookings = ($role->max_active_bookings == null)
							? sprintf('<em>%s</em>', lang('app.unlimited'))
							: sprintf('%d', $role->max_active_bookings);
						$description_html = empty($role->description) ? '' : word_limiter(html_escape($role->description), 8);
						$actions_html = $this->load->view('partials/editdelete', [
							'edit' => 'roles/edit/' . $role->role_id,
							'delete' => 'roles/delete/' . $role->role_id,
						], TRUE);
						?>
						<tr>
							<td data-label="<?= lang('role.field.name') ?>"><?= $name_html ?></td>
							<td data-label="<?= lang('role.field.description') ?>"><?= $description_html ?></td>
							<td data-label="<?= lang('role.field.user_count') ?>"><?= $user_count ?></td>
							<td data-label="<?= lang('constraint.max_active_bookings.short') ?>"><?= $max_active_bookings ?></td>
							<td data-label="<?= lang('app.actions') ?>"><?= $actions_html ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
