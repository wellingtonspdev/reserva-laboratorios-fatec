<div class="cps-admin-page">
	<div class="cps-admin-toolbar">
		<div>
			<?= anchor('users/import', cps_icon('chevron-left', 'w-4 h-4') . lang('app.action.back'), ['class' => 'cps-admin-btn']) ?>
		</div>
		<div></div>
	</div>

<?php if (is_array($result)): ?>
	<div class="cps-admin-table-wrap">
		<table class="cps-admin-table">
			<thead>
				<tr>
					<th><?= lang('user.import.row') ?></th>
					<th><?= lang('user.field.username') ?></th>
					<th><?= lang('user.import.created') ?></th>
					<th><?= lang('app.status') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($result as $row): ?>
					<?php
					$is_success = $row->status == 'success';
					$status_key = sprintf('user.import.status.%s', $row->status);
					$status_line = lang($status_key);
					$status_class = $is_success ? 'cps-admin-status-success' : 'cps-admin-status-danger';
					?>
					<tr>
						<td data-label="<?= lang('user.import.row') ?>">#<?= $row->line ?></td>
						<td data-label="<?= lang('user.field.username') ?>"><?= html_escape($row->user->username) ?></td>
						<td data-label="<?= lang('user.import.created') ?>"><?= $is_success ? lang('app.yes') : lang('app.no') ?></td>
						<td data-label="<?= lang('app.status') ?>"><span class="cps-admin-status <?= $status_class ?>"><?= $status_line ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<div class="cps-admin-toolbar mt-4">
	<div>
		<?= anchor('users/import', cps_icon('chevron-left', 'w-4 h-4') . lang('app.action.back'), ['class' => 'cps-admin-btn']) ?>
	</div>
	<div class="cps-admin-toolbar-actions">
		<?= anchor('users', cps_icon('users', 'w-4 h-4') . lang('user.all_users'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?>
		<?= anchor('users/import', cps_icon('plus', 'w-4 h-4') . lang('user.import_more'), ['class' => 'cps-admin-btn']) ?>
	</div>
</div>
</div>
