<?php

$is_first = true;
$is_first_department = true;
$is_first_user = true;

// Get columns for table
$dates = [];
$rooms = [];
if (is_array($multibooking->slots)) {
	foreach ($multibooking->slots as $key => $slot) {
		$dates[] = $slot->date;
		$rooms[] = $slot->room_id;
	}
}

$show_date_col = (count(array_unique($dates)) == 1)
	? FALSE
	: TRUE;

$show_room_col = (count(array_unique($rooms)) == 1)
	? FALSE
	: TRUE;

$allowed_booking_count = 0;


// Info secton
if ( ! $show_room_col || ! $show_date_col) {

	$info = [];
	if ( ! $show_date_col) {
		$info['app.date'] = date_output_long($slot->datetime);
	}

	if ( ! $show_room_col) {
		$info['room.room'] = html_escape($slot->room->name);
	}
	$info_fmt = '<div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">%s</dt><dd class="mt-1 text-base text-gray-900">%s</dd></div>';
	$info_html = '';
	foreach ($info as $key => $value) {
		$label = lang($key);
		$info_html .= sprintf($info_fmt, $label, $value);
	}

	echo "<div class='cps-multibooking-summary'><dl>{$info_html}</dl></div>";
}


// Generate rows
if (is_array($multibooking->slots)) {
    echo "<div class='cps-multibooking-list'>";
	foreach ($multibooking->slots as $key => $slot) {

		$capabilities = $slot->capabilities;

		// 'Create' checkbox col
		$create_field = sprintf('slot_single[%d][create]', $slot->mbs_id);
		$create_hidden = form_hidden($create_field, 0);
		$check_props = [
			'id' => $create_field,
			'name' => $create_field,
			'value' => 1,
			'checked' => (set_value($create_field, 1) == 1),
            'class' => 'cps-multibooking-check'
		];
		if (!$capabilities['single.create']) {
			$check_props['disabled'] = '';
			$check_props['checked'] = false;
		} else {
			$allowed_booking_count++;
		}
		$create_check = form_checkbox($check_props);
		$check_col = "<div class='cps-multibooking-check-wrap'>" . $create_hidden . $create_check . "</div>";

		// Date column
		if ($show_date_col) {
			$date = "<div class='font-semibold text-gray-900'>" . date_output_long($slot->datetime) . "</div>";
			$period_text = sprintf('<div class="text-sm text-gray-600">%s (%s - %s)</div>',
				html_escape($slot->period->name),
				date_output_time($slot->period->time_start),
				date_output_time($slot->period->time_end)
			);
			$date_col = form_label($date . $period_text, $create_field, ['class' => 'cursor-pointer block']);
		} else {
			$period_text = sprintf('<div class="font-semibold text-gray-900">%s</div><div class="text-sm text-gray-600">(%s - %s)</div>',
				html_escape($slot->period->name),
				date_output_time($slot->period->time_start),
				date_output_time($slot->period->time_end)
			);
			$date_col = form_label($period_text, $create_field, ['class' => 'cursor-pointer block']);
		}

		// Room column
		$room_col = $show_room_col ? "<div class='text-sm font-medium text-blue-700 mt-1'>" . html_escape($slot->room->name) . "</div>" : "";

		// Department column
		if ($capabilities['single.set_department']) {
			$department_field = sprintf('slot_single[%d][department_id]', $slot->mbs_id);
			$value = set_value($department_field, $department ? $department->department_id : '', FALSE);
			$input = form_dropdown([
				'name' => $department_field,
				'id' => $department_field,
				'options' => html_escape($department_options),
				'selected' => $value,
				'class' => 'cps-multibooking-control',
				'up-copy-group' => 'department_id',
			]);
			$append_block = '';
			if ($is_first_department) {
				$append_block = "<button type='button' class='cps-multibooking-copy' up-copy-to='department_id' title='Copiar para todos'>&darr;</button>";
			}
			$department_col = "<div class='cps-multibooking-field'><div class='cps-multibooking-label'>" . lang('department.department') . "</div><div class='cps-multibooking-input-group'>{$input}{$append_block}</div></div>";
		} else {
			$department_label = sprintf('(%s)', lang('app.none'));
			if (isset($department) && ! empty($department)) {
				$department_label = html_escape($department->name);
			}
			$department_col = "<div class='cps-multibooking-field'><div class='cps-multibooking-label'>" . lang('department.department') . "</div><div class='cps-multibooking-static'>{$department_label}</div></div>";
		}

		// User column
		if ($capabilities['single.set_user']) {
			$user_field = sprintf('slot_single[%d][user_id]', $slot->mbs_id);
			$value = set_value($user_field, $user->user_id, FALSE);
			$input = form_dropdown([
				'name' => $user_field,
				'id' => $user_field,
				'options' => $user_options,
				'selected' => $value,
				'class' => 'cps-multibooking-control',
				'up-copy-group' => 'user_id',
			]);
			$append_block = '';
			if ($is_first_user) {
				$append_block = "<button type='button' class='cps-multibooking-copy' up-copy-to='user_id' title='Copiar para todos'>&darr;</button>";
			}
			$user_col = "<div class='cps-multibooking-field'><div class='cps-multibooking-label'>" . lang('user.user') . "</div><div class='cps-multibooking-input-group'>{$input}{$append_block}</div></div>";
		} else {
			$user_label = sprintf('(%s)', lang('app.none'));
			if (isset($user) && ! empty($user)) {
				$user_label = !empty($user->displayname) ? $user->displayname : $user->username;
				$user_label = html_escape($user_label);
			}
			$user_col = "<div class='cps-multibooking-field'><div class='cps-multibooking-label'>" . lang('user.user') . "</div><div class='cps-multibooking-static'>{$user_label}</div></div>";
		}

		// Notes
		if ($capabilities['single.create']) {
			$notes_field = sprintf('slot_single[%d][notes]', $slot->mbs_id);
			$value = set_value($notes_field, '', FALSE);
			$input = form_input([
				'name' => $notes_field,
				'id' => $notes_field,
				'placeholder' => lang('booking.notes'),
				'value' => $value,
				'class' => 'cps-multibooking-control',
				'up-copy-group' => 'notes',
			]);
			$append_block = '';
			if ($is_first) {
				$append_block = "<button type='button' class='cps-multibooking-copy' up-copy-to='notes' title='Copiar para todos'>&darr;</button>";
			}
			$notes_col = "<div class='cps-multibooking-field'><div class='cps-multibooking-label'>" . lang('booking.notes') . "</div><div class='cps-multibooking-input-group'>{$input}{$append_block}</div></div>";
		} else {
			$notes_col = msgbox('notice is-solo large', lang('booking.error.no_permission_room_date'));
		}

        // Wrap the row (Card)
        echo "<div class='cps-multibooking-row'>";
        
        echo "<div class='cps-multibooking-slot'>";
        echo "<div class='cps-multibooking-slot-inner'>";
        echo $check_col;
        echo "<div class='cps-multibooking-slot-text'>";
        echo $date_col;
        echo $room_col;
        echo "</div>";
        echo "</div>"; 
        echo "</div>"; 

        echo "<div class='cps-multibooking-fields'>";
        echo $department_col;
        echo $user_col;
        echo $notes_col;
        echo "</div>";

        echo "</div>"; 

		if ($is_first) $is_first = false;
		if ($is_first_department) $is_first_department = false;
		if ($is_first_user) $is_first_user = false;
	}
    echo "</div>";
}


// Notice
if ( ! is_null($user_permitted_booking_count)) {
	if ($allowed_booking_count > $user_permitted_booking_count) {
		if ($user_booking_count == 0) {
			$line = lang('booking.warning.permitted_limit');
			$msg = sprintf($line, $user_permitted_booking_count);
		} else {
			$line = lang('booking.warning.permitted_limit_with_active');
			$msg = sprintf($line,
				$user_permitted_booking_count,
				$user_booking_limit,
				$user_booking_count
			);
		}
		echo msgbox('notice large', $msg);
	}
}


if ($can_book_single) {
	// Actions
	$submit = form_button([
		'type' => 'submit',
		'name' => 'action',
		'value' => 'create',
		'class' => 'cps-btn cps-btn-primary',
		'content' => '&check; ' . lang('booking.add.multi.single.action'),
	]);

	$cancel = anchor($return_uri, lang('app.action.cancel'), ['class' => 'cps-btn cps-btn-secondary', 'up-dismiss' => '']);

	echo "<div class='cps-multibooking-actions'>{$submit}{$cancel}</div>";
}
