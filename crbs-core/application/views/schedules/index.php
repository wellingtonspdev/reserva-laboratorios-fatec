<?php


echo $this->session->flashdata('saved');

echo iconbar([
	['schedules/add', lang('schedule.add.action'), 'add.png'],
]);

<table width="100%" cellpadding="2" cellspacing="2" border="0" class="border-table cps-table" style="line-height:1.3;margin-top:16px;margin-bottom:16px">
	<thead>
	<tr class="heading">
		<th width="25%"><?= lang('schedule.field.name') ?></th>
		<th width="45%"><?= lang('schedule.field.description') ?></th>
		<th width="10%"><?= lang('app.actions') ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (!empty($schedules)) {
		foreach ($schedules as $idx => $schedule) {
			$name = html_escape($schedule->name);
			$name_html = anchor('schedules/edit/' . $schedule->schedule_id, $name);
			$description_html = (empty($schedule->description)) ? '' : word_limiter(html_escape($schedule->description), 8);
			$actions = [
				'edit' => 'schedules/edit/' . $schedule->schedule_id,
				'delete' => 'schedules/delete/' . $schedule->schedule_id,
			];
			$actions_html = $this->load->view('partials/editdelete', $actions, TRUE);
			?>
			<tr>
				<td data-label="<?= lang('schedule.field.name') ?>"><?= $name_html ?></td>
				<td data-label="<?= lang('schedule.field.description') ?>"><?= $description_html ?></td>
				<td data-label="<?= lang('app.actions') ?>"><?= $actions_html ?></td>
			</tr>
			<?php
		}
	} else {
		echo '<tr><td colspan="3" align="center" style="padding:16px 0">' . msgbox('info', lang('schedule.no_items')) . '</td></tr>';
	}
	?>
	</tbody>
</table>
