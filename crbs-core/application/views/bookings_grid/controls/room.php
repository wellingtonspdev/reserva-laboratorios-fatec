<?php

$params = [
	'room' => $room ? $room->room_id : null,
	'date' => $datetime->format('Y-m-d'),
];

$date_url = site_url('bookings/filter/date') . '?' . http_build_query($params);
$long_date = date_output_long($datetime);
$date_text = html_escape($long_date);
$date_label = '<span class="inline-flex items-center gap-1">' . cps_icon('calendar-days', 'w-4 h-4') . $date_text . '</span> <span>&#x25BC;</span>';
$date_button = "<button
	type='button'
	class='cps-filter-btn'
	up-layer='new popup'
	up-size='medium'
	up-href='$date_url'
	up-history='false'
	up-target='.bookings-filter'
	up-preload=''
>{$date_label}</button>";

$room_button = '';
if ($room) {
	$rooms_url = site_url('bookings/filter/room') . '?' . http_build_query($params);
	$current_room = html_escape($room->name);
	$rooms_label = '<span class="inline-flex items-center gap-1">' . cps_icon('building', 'w-4 h-4') . $current_room . '</span> <span>&#x25BC;</span>';
	$room_button = "<button
		type='button'
		class='cps-filter-btn'
		up-layer='new popup'
		up-size='max'
		up-href='$rooms_url'
		up-history='false'
		up-target='.bookings-filter'
		up-preload=''
	>{$rooms_label}</button>";
}

$info_link = '';
if ($room) {
	$info_url = site_url("rooms/info/{$room->room_id}");
	$info_link = anchor($info_url, cps_icon('info', 'w-4 h-4') . "Info", [
		'class' => 'cps-filter-btn',
	]);
}

echo $room_button . $info_link . $date_button;
