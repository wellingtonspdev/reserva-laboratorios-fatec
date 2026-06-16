<?php

$attrs = [
	'method' => 'post',
	'id' => 'bookings_controls_session',
	'class' => 'cps-session-form',
];

$hidden = [
	'params' => http_build_query($query_params)
];

echo form_open($form_action, $attrs, $hidden);

$sess_lang = lang('session.session');
$icon_sess = '<svg class="w-4 h-4 text-cps-red flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
echo "<label for='session_id' class='cps-session-label flex items-center gap-1'>{$icon_sess} {$sess_lang}:</label>";

echo form_dropdown([
	'name' => 'session_id',
	'id' => 'session_id',
	'class' => 'cps-session-select',
	'options' => html_escape($session_options),
	'selected' => $selected_session_id,
	'data-script' => 'on change requestSubmit() on the closest <form/>',
]);

echo form_close();
