<div class="cps-admin-filter">
	<?php
	echo form_open('users', [
		'class' => 'cps-admin-filter-form',
		'id' => 'users_filter',
		'method' => 'GET',
	]);
	?>
	<div class="cps-admin-filter-grid">
		<div class="cps-admin-field cps-admin-filter-search">
			<?php
			echo form_label(lang('app.search'), 'q');
			echo form_input([
				'name' => 'q',
				'id' => 'q',
				'value' => $filter['search'] ?? '',
			]);
			?>
		</div>
		<div class="cps-admin-field">
			<?php
			echo form_label(lang('role.role'), 'role_id');
			echo form_dropdown([
				'name' => 'role_id',
				'id' => 'role_id',
				'options' => $role_options,
				'selected' => $filter['role_id'] ?? '',
			]);
			?>
		</div>
		<div class="cps-admin-field">
			<?php
			echo form_label(lang('department.department'), 'department_id');
			echo form_dropdown([
				'name' => 'department_id',
				'id' => 'department_id',
				'options' => $department_options,
				'selected' => $filter['department_id'] ?? '',
			]);
			?>
		</div>
		<div class="cps-admin-field cps-admin-filter-actions">
			<label aria-hidden="true">&nbsp;</label>
			<div class="cps-admin-filter-buttons">
				<?php
				echo form_button([
					'type' => 'submit',
					'class' => 'cps-admin-btn cps-admin-btn-primary',
					'content' => cps_icon('search', 'w-4 h-4') . lang('app.filter'),
				]);
				echo anchor('users', lang('app.clear'), ['class' => 'cps-admin-btn']);
				?>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
