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

	echo "<div class='bg-white px-4 py-5 sm:px-6 mb-6 rounded-lg shadow-sm ring-1 ring-gray-200'><dl class='grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2'>{$info_html}</dl></div>";
}


// Generate rows
if (is_array($multibooking->slots)) {
    echo "<div class='flex flex-col gap-4 mb-6'>";
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
            'class' => 'h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-600 cursor-pointer'
		];
		if (!$capabilities['single.create']) {
			$check_props['disabled'] = '';
			$check_props['checked'] = false;
		} else {
			$allowed_booking_count++;
		}
		$create_check = form_checkbox($check_props);
		$check_col = "<div class='flex items-center h-6 mr-3 mt-1'>" . $create_hidden . $create_check . "</div>";

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
				'class' => 'cps-form-control block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6',
				'up-copy-group' => 'department_id',
			]);
			$append_block = '';
			if ($is_first_department) {
				$append_block = "<button type='button' class='ml-2 inline-flex items-center rounded bg-gray-50 px-2.5 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100 transition' up-copy-to='department_id' title='Copiar para todos'>&darr;</button>";
			}
			$department_col = "<div class='flex items-center mb-3'><div class='w-24 md:w-32 text-sm text-gray-500 font-medium'>" . lang('department.department') . "</div><div class='flex-1 flex'>{$input}{$append_block}</div></div>";
		} else {
			$department_label = sprintf('(%s)', lang('app.none'));
			if (isset($department) && ! empty($department)) {
				$department_label = html_escape($department->name);
			}
			$department_col = "<div class='flex items-center mb-3'><div class='w-24 md:w-32 text-sm text-gray-500 font-medium'>" . lang('department.department') . "</div><div class='flex-1 text-sm text-gray-900 font-medium'>{$department_label}</div></div>";
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
				'class' => 'cps-form-control block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6',
				'up-copy-group' => 'user_id',
			]);
			$append_block = '';
			if ($is_first_user) {
				$append_block = "<button type='button' class='ml-2 inline-flex items-center rounded bg-gray-50 px-2.5 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100 transition' up-copy-to='user_id' title='Copiar para todos'>&darr;</button>";
			}
			$user_col = "<div class='flex items-center mb-3'><div class='w-24 md:w-32 text-sm text-gray-500 font-medium'>" . lang('user.user') . "</div><div class='flex-1 flex'>{$input}{$append_block}</div></div>";
		} else {
			$user_label = sprintf('(%s)', lang('app.none'));
			if (isset($user) && ! empty($user)) {
				$user_label = !empty($user->displayname) ? $user->displayname : $user->username;
				$user_label = html_escape($user_label);
			}
			$user_col = "<div class='flex items-center mb-3'><div class='w-24 md:w-32 text-sm text-gray-500 font-medium'>" . lang('user.user') . "</div><div class='flex-1 text-sm text-gray-900 font-medium'>{$user_label}</div></div>";
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
				'class' => 'cps-form-control block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6',
				'up-copy-group' => 'notes',
			]);
			$append_block = '';
			if ($is_first) {
				$append_block = "<button type='button' class='ml-2 inline-flex items-center rounded bg-gray-50 px-2.5 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100 transition' up-copy-to='notes' title='Copiar para todos'>&darr;</button>";
			}
			$notes_col = "<div class='flex items-center'><div class='w-24 md:w-32 text-sm text-gray-500 font-medium'>" . lang('booking.notes') . "</div><div class='flex-1 flex'>{$input}{$append_block}</div></div>";
		} else {
			$notes_col = msgbox('notice is-solo large', lang('booking.error.no_permission_room_date'));
		}

        // Wrap the row (Card)
        echo "<div class='bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5 flex flex-col md:flex-row gap-6 md:items-start transition-colors hover:bg-gray-50'>";
        
        echo "<div class='flex flex-row md:flex-col md:w-1/3 min-w-[200px]'>";
        echo "<div class='flex items-start'>";
        echo $check_col;
        echo "<div class='flex-1'>";
        echo $date_col;
        echo $room_col;
        echo "</div>";
        echo "</div>"; 
        echo "</div>"; 

        echo "<div class='flex flex-col md:w-2/3 flex-1 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6'>";
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

	echo "<div class='flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200'>{$submit}{$cancel}</div>";
}
