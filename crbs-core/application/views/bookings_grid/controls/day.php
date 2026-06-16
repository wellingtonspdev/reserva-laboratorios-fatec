<?php

$date_url = site_url('bookings/filter/date') . '?' . http_build_query($query_params);
$long_date = date_output_long($datetime);
$date_text = html_escape($long_date);
if (!empty($week_name)) {
	$date_text .= ' <span class="cps-date-nav-week">' . html_escape($week_name) . '</span>';
}
$calendar_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="cps-icon"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>';
$chevron_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="cps-icon-sm"><path d="m6 9 6 6 6-6"/></svg>';
$date_label = "<span class=\"cps-flex-center cps-gap-2\">{$calendar_icon} <span>{$date_text}</span></span> <span class=\"cps-ml-2 cps-flex-center\">{$chevron_icon}</span>";
$date_button = "<button
	type='button'
	class='cps-filter-btn cps-date-picker-btn'
	up-layer='new popup'
	up-size='medium'
	up-href='$date_url'
	up-history='false'
	up-target='.bookings-filter'
	up-preload=''
>{$date_label}</button>";

$prev_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="cps-icon-sm"><path d="m15 18-6-6 6-6"/></svg>';
$next_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="cps-icon-sm"><path d="m9 18 6-6-6-6"/></svg>';

$prev_button = !empty($prev_url)
	? anchor($prev_url, "{$prev_icon}<span>" . html_escape(lang('booking.nav.back')) . "</span>", [
		'class' => 'cps-date-nav-btn cps-date-nav-btn-prev',
		'up-follow' => '',
		'up-preload' => '',
	])
	: '<span class="cps-date-nav-btn cps-date-nav-btn-disabled" aria-disabled="true">' . $prev_icon . '<span>' . html_escape(lang('booking.nav.back')) . '</span></span>';

$next_button = !empty($next_url)
	? anchor($next_url, "<span>" . html_escape(lang('booking.nav.next')) . "</span>{$next_icon}", [
		'class' => 'cps-date-nav-btn cps-date-nav-btn-next',
		'up-follow' => '',
		'up-preload' => '',
	])
	: '<span class="cps-date-nav-btn cps-date-nav-btn-disabled" aria-disabled="true"><span>' . html_escape(lang('booking.nav.next')) . '</span>' . $next_icon . '</span>';

echo "<div class='cps-date-nav-group'>{$prev_button}{$date_button}{$next_button}</div>";
