<?php

$hidden = [
	'room_id' => $room->room_id,
	'period_id' => $period->period_id,
	'date' => $date_info->date,
];

echo form_hidden($hidden);

$none = sprintf('(%s)', lang('app.none'));

echo "<fieldset class='border-none p-0 m-0 w-full'>";

// Date
//
$field = 'date';
$label = form_label(lang('app.date'), 'date', ['class' => 'block font-bold text-sm text-cps-black mb-1']);
$input = sprintf('%s (%s)', date_output_long($datetime), html_escape($week->name));
echo "<div class='mb-4'>{$label}<div class='text-cps-gray-text'>{$input}</div></div>";


// Period
//
$field = 'period_id';
$label = form_label(lang('period.period'), $field, ['class' => 'block font-bold text-sm text-cps-black mb-1']);
$options = results_to_assoc($all_periods, 'period_id', function($period) {
	$start = date_output_time($period->time_start);
	$end = date_output_time($period->time_end);
	return sprintf('%s (%s - %s)', $period->name, $start, $end);
});
$value = set_value($field, $period->period_id, FALSE);
$input = form_dropdown([
	'name' => $field,
	'id' => $field,
	'options' => $options,
	'selected' => $value,
	'class' => 'w-full border border-cps-gray-border rounded-input px-3 py-2 focus:ring focus:ring-cps-red focus:outline-none'
]);
echo "<div class='mb-4'>{$label}{$input}</div>";


// Room
//
$field = 'room_id';
$label = form_label(lang('room.room'), $field, ['class' => 'block font-bold text-sm text-cps-black mb-1']);
$input = html_escape($room->name);
echo "<div class='mb-4'>{$label}<div class='text-cps-gray-text'>{$input}</div></div>";


// Department
//
$field = 'department_id';
$label = form_label(lang('department.department'), $field, ['class' => 'block font-bold text-sm text-cps-black mb-1']);
$show_department = FALSE;
if ($can_set_department) {
	$show_department = TRUE;
	$options = results_to_assoc($all_departments, 'department_id', 'name', $none);
	$value = set_value($field, $department ? $department->department_id : '', FALSE);
	$input = form_dropdown([
		'name' => $field,
		'id' => $field,
		'options' => html_escape($options),
		'selected' => $value,
		'class' => 'w-full border border-cps-gray-border rounded-input px-3 py-2 focus:ring focus:ring-cps-red focus:outline-none'
	]);
} else {
	if (!empty($department)) {
		$show_department = TRUE;
		$input = "<div class='text-cps-gray-text'>" . html_escape($department->name) . "</div>";
	}
}
echo ($show_department)
	? "<div class='mb-4'>{$label}{$input}</div>"
	: '';

// Who
//
$field = 'user_id';
$label = form_label(lang('booking.booked_by'), $field, ['class' => 'block font-bold text-sm text-cps-black mb-1']);
if ($can_set_user) {
	$options = results_to_assoc($all_users, 'user_id', fn($user) => !empty($user->displayname)
			? $user->displayname
			: $user->username, $none);
	$value = set_value($field, $user->user_id, FALSE);
	$input = form_dropdown([
		'name' => $field,
		'id' => $field,
		'options' => html_escape($options),
		'selected' => $value,
		'class' => 'w-full border border-cps-gray-border rounded-input px-3 py-2 focus:ring focus:ring-cps-red focus:outline-none'
	]);
} else {
	$input = !empty($user->displayname)
		? $user->displayname
		: $user->username;
	$input = "<div class='text-cps-gray-text'>" . html_escape($input) . "</div>";
}
echo "<div class='mb-4'>{$label}{$input}</div>";


// Notes
//
$field = 'notes';
$value = set_value($field, '', FALSE);
$label = form_label(lang('booking.notes'), 'notes', ['class' => 'block font-bold text-sm text-cps-black mb-1']);
$input = form_textarea([
	'autofocus' => 'true',
	'name' => $field,
	'id' => $field,
	'rows' => '3',
	'cols' => '50',
	'tabindex' => tab_index(),
	'value' => $value,
	'class' => 'w-full border border-cps-gray-border rounded-input px-3 py-2 focus:ring focus:ring-cps-red focus:outline-none'
]);
echo sprintf("<div class='mb-6'>%s%s</div>%s", $label, $input, form_error($field));

echo "</fieldset>";

// Actions
//
$submit = form_button([
	'type' => 'submit',
	'name' => 'action',
	'value' => 'create',
	'class' => 'cps-btn-primary',
	'content' => '&check; ' . lang('booking.add.single.action'),
]);

$cancel = anchor($return_uri, lang('app.action.cancel'), ['up-dismiss' => '', 'class' => 'cps-btn-secondary']);

echo "<div class='flex items-center justify-end gap-3 mt-6'>{$cancel}{$submit}</div>";
