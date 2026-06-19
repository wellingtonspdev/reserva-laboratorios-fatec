<?php

use app\components\Calendar;

$dates = [];
$by_room = [];

foreach ($multibooking->slots as $key => $slot) {
	$dates[] = $slot->date;
	$by_room[$slot->room_id]['room'] = $slot->room;
	$by_room[$slot->room_id]['slots'][$key] = $slot;
}

$show_date_col = (count(array_unique($dates)) == 1)
	? false
	: true;

$render_field = static function ($label, $control, $class = '') {
	$class_attr = trim("cps-recurring-field {$class}");
	return "<div class='{$class_attr}'><div class='cps-recurring-label'>{$label}</div><div class='cps-recurring-control'>{$control}</div></div>";
};

$render_input_group = static function ($input, $copy_group = '', $show_copy = false) {
	$copy = '';
	if ($show_copy) {
		$copy = "<button type='button' class='cps-multibooking-copy' up-copy-to='{$copy_group}' title='Copiar para todos'>&darr;</button>";
	}
	return "<div class='cps-multibooking-input-group'>{$input}{$copy}</div>";
};

foreach ($by_room as $room_id => $data) {

	$fields = [];
	$room_name_esc = html_escape($data['room']->name);

	echo "<fieldset class='cps-recurring-room-section'>";
	echo "<legend>{$room_name_esc}</legend>";

	if (!has_permission(Permission::BK_RECUR_CREATE, $room_id)) {
		echo msgbox('notice large', lang('booking.error.no_permission_room'));
		echo "</fieldset>";
		continue;
	}

	$slot_count = count($data['slots']);
	echo "<div class='cps-recurring-list'>";

	foreach ($data['slots'] as $key => $slot) {

		$capabilities = $slot->capabilities;

		$day_name = Calendar::get_day_name($slot->datetime->format('N'));
		$lang_key = sprintf('cal_%s', strtolower((string) $day_name));
		$day_name_lang = lang($lang_key);

		$recurring_date_options = [];
		if (is_array($slot->recurring_dates)) {
			foreach ($slot->recurring_dates as $date) {
				$title = date_output_long($date->date);
				if ($date->date->format('Y-m-d') == $slot->date) {
					$title = '* ' . $title;
				}
				$recurring_date_options[$date->date->format('Y-m-d')] = $title;
			}
		}

		$create_field = sprintf('slots[%d][create]', $slot->mbs_id);
		$create_hidden = form_hidden($create_field, 0);
		$create_check = form_checkbox([
			'id' => $create_field,
			'name' => $create_field,
			'value' => 1,
			'checked' => (set_value($create_field, 1) == 1),
			'class' => 'cps-multibooking-check',
		]);

		if ($show_date_col) {
			$slot_title = date_output_long($slot->datetime);
			$slot_meta = sprintf('%s (%s - %s)',
				html_escape($slot->period->name),
				date_output_time($slot->period->time_start),
				date_output_time($slot->period->time_end)
			);
		} else {
			$slot_title = html_escape($slot->period->name);
			$slot_meta = sprintf('%s - %s',
				date_output_time($slot->period->time_start),
				date_output_time($slot->period->time_end)
			);
		}

		$slot_header = form_label(
			$create_hidden
			. $create_check
			. "<span class='cps-recurring-slot-text'><strong>{$slot_title}</strong><small>{$slot_meta}</small></span>",
			$create_field,
			['class' => 'cps-recurring-slot-label']
		);

		if ($capabilities['recur.set_department']) {
			$copy_group = sprintf('r%d-department', $room_id);
			$department_field = sprintf('slots[%d][department_id]', $slot->mbs_id);
			$value = set_value($department_field, $department ? $department->department_id : '', FALSE);
			$input = form_dropdown([
				'name' => $department_field,
				'id' => $department_field,
				'options' => html_escape($department_options),
				'selected' => $value,
				'class' => 'cps-multibooking-control',
				'up-copy-group' => $copy_group,
			]);
			$department_col = $render_field(
				lang('department.department'),
				$render_input_group($input, $copy_group, !isset($fields['department']) && $slot_count > 1)
			);
			$fields['department'] = true;
		} else {
			$department_label = sprintf('(%s)', lang('app.none'));
			if (isset($department) && !empty($department)) {
				$department_label = html_escape($department->name);
			}
			$department_col = $render_field(lang('department.department'), "<div class='cps-multibooking-static'>{$department_label}</div>");
		}

		if ($capabilities['recur.set_user']) {
			$copy_group = sprintf('r%d-user', $room_id);
			$user_field = sprintf('slots[%d][user_id]', $slot->mbs_id);
			$value = set_value($user_field, $user->user_id, FALSE);
			$input = form_dropdown([
				'name' => $user_field,
				'id' => $user_field,
				'options' => $user_options,
				'selected' => $value,
				'class' => 'cps-multibooking-control',
				'up-copy-group' => $copy_group,
			]);
			$user_col = $render_field(
				lang('user.user'),
				$render_input_group($input, $copy_group, !isset($fields['user']) && $slot_count > 1)
			);
			$fields['user'] = true;
		} else {
			$user_label = sprintf('(%s)', lang('app.none'));
			if (isset($user) && !empty($user)) {
				$user_label = !empty($user->displayname) ? $user->displayname : $user->username;
				$user_label = html_escape($user_label);
			}
			$user_col = $render_field(lang('user.user'), "<div class='cps-multibooking-static'>{$user_label}</div>");
		}

		$copy_group = sprintf('r%d-notes', $room_id);
		$notes_field = sprintf('slots[%d][notes]', $slot->mbs_id);
		$value = set_value($notes_field, '', FALSE);
		$input = form_input([
			'name' => $notes_field,
			'id' => $notes_field,
			'size' => 30,
			'value' => $value,
			'class' => 'cps-multibooking-control',
			'placeholder' => lang('booking.notes'),
			'up-copy-group' => $copy_group,
		]);
		$notes_col = $render_field(
			lang('booking.notes'),
			$render_input_group($input, $copy_group, !isset($fields['notes']) && $slot_count > 1),
			'cps-recurring-field-notes'
		);
		$fields['notes'] = true;

		$copy_group = sprintf('r%d-start-%s', $room_id, $day_name);
		$start_field = sprintf('slots[%d][recurring_start]', $slot->mbs_id);
		$options = [
			'session' => lang('booking.recurring.start_of_session'),
			lang('booking.recurring.specific_date') => $recurring_date_options,
		];
		$value = set_value($start_field, $slot->date, FALSE);
		$input = form_dropdown([
			'name' => $start_field,
			'id' => $start_field,
			'options' => $options,
			'selected' => $value,
			'class' => 'cps-multibooking-control',
			'up-copy-group' => $copy_group,
		]);
		$start_col = $render_field(
			lang('booking.start'),
			$render_input_group($input, $copy_group, !isset($fields[$copy_group]) && $slot_count > 1),
			'cps-recurring-field-start'
		);
		$fields[$copy_group] = true;

		$copy_group = sprintf('r%d-end-%s', $room_id, $day_name);
		$end_field = sprintf('slots[%d][recurring_end]', $slot->mbs_id);
		$options = [
			'session' => lang('booking.recurring.end_of_session'),
			lang('booking.recurring.specific_date') => $recurring_date_options,
		];
		$value = set_value($end_field, 'session', FALSE);
		$input = form_dropdown([
			'name' => $end_field,
			'id' => $end_field,
			'options' => $options,
			'selected' => $value,
			'class' => 'cps-multibooking-control',
			'up-copy-group' => $copy_group,
		]);
		$end_col = $render_field(
			lang('booking.end'),
			$render_input_group($input, $copy_group, !isset($fields[$copy_group]) && $slot_count > 1),
			'cps-recurring-field-end'
		);
		$fields[$copy_group] = true;

		echo "<div class='cps-recurring-row'>";
		echo "<div class='cps-recurring-slot'>{$slot_header}</div>";
		echo "<div class='cps-recurring-fields'>{$department_col}{$user_col}{$start_col}{$end_col}{$notes_col}</div>";
		echo "</div>";
	}

	echo "</div>";
	echo "</fieldset>";
}

if ($can_book_recur) {
	$submit = form_button([
		'type' => 'submit',
		'name' => 'action',
		'value' => 'create',
		'class' => 'cps-btn cps-btn-primary',
		'content' => lang('app.action.continue') . ' &rarr;',
	]);

	$cancel = anchor($return_uri, lang('app.action.cancel'), ['class' => 'cps-btn cps-btn-secondary', 'up-dismiss' => '']);

	echo "<div class='cps-multibooking-actions'>{$submit}{$cancel}</div>";
}
