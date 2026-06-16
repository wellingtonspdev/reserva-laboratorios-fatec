<?php

echo $this->session->flashdata('saved');
?>

<div class="cps-admin-page">
	<div class="cps-admin-toolbar">
		<div>
			<?= anchor('setup', cps_icon('chevron-left', 'w-4 h-4') . lang('app.action.back'), ['class' => 'cps-admin-btn']) ?>
		</div>
		<div class="cps-admin-toolbar-actions">
			<?= anchor('departments/add', cps_icon('plus', 'w-4 h-4') . lang('department.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?>
		</div>
	</div>

	<?php if (empty($departments)): ?>
		<div class="cps-admin-empty">
			<p><?= lang('department.no_items') ?></p>
			<div class="mt-4"><?= anchor('departments/add', cps_icon('plus', 'w-4 h-4') . lang('department.add.action'), ['class' => 'cps-admin-btn cps-admin-btn-primary']) ?></div>
		</div>
	<?php else: ?>
		<div class="cps-admin-table-wrap">
			<table class="cps-admin-table">
				<thead>
					<tr>
						<th><?= lang('department.field.name') ?></th>
						<th><?= lang('department.field.description') ?></th>
						<th><?= lang('app.actions') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($departments as $department): ?>
						<?php
						$actions_html = $this->load->view('partials/editdelete', [
							'edit' => 'departments/edit/'.$department->department_id,
							'delete' => 'departments/delete/'.$department->department_id,
						], TRUE);
						?>
						<tr>
							<td data-label="<?= lang('department.field.name') ?>"><?= anchor('departments/edit/'.$department->department_id, html_escape($department->name)) ?></td>
							<td data-label="<?= lang('department.field.description') ?>"><?= html_escape($department->description) ?></td>
							<td data-label="<?= lang('app.actions') ?>"><?= $actions_html ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?= $pagelinks ?>
	<?php endif; ?>
</div>
