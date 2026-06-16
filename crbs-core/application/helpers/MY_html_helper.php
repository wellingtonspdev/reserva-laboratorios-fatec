<?php

defined('BASEPATH') OR exit('No direct script access allowed');


function field($validation, $database = NULL, $last = ''){
	$value = $validation ?? $database ?? $last;
	return $value;
}


function buttonlist($items = array()) {
	$out = '';
	$links = [];

	foreach ($items as $item) {
		if (is_null($item)) continue;
		$is_active = isset($item['active']) && $item['active'] == true;
		$url = $item['url'];
		$title = $item['title'];
		$attrs = $item['attrs'] ?? null;
		if (is_array($attrs)) {
			$attrs = _stringify_attributes($attrs);
		}
		
		$base_class = 'cps-floor-pill inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border';
		$pill_class = $is_active 
			? "{$base_class} cps-floor-pill-active bg-[var(--cps-red)] text-black border-[var(--cps-red)]" 
			: "{$base_class} bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:text-[var(--cps-red)] hover:border-[var(--cps-red)]";
			
		$link = anchor($url, $title, "class='{$pill_class}' {$attrs}");
		$link = str_replace(site_url('#'), '#', $link);
		$links[] = $link;
	}

	$items_html = implode("", $links);

	$out = "<nav class='cps-floor-nav flex flex-wrap gap-2 mb-6'>{$items_html}</nav>";
	return $out;
}

function cps_icon_name($icon)
{
	$icon = (string) $icon;
	$map = [
		'calendar.png' => 'calendar',
		'cal_day.png' => 'calendar-days',
		'calendar_view_day.png' => 'calendar-days',
		'calendar_view_month.png' => 'calendar-range',
		'school_manage_settings.png' => 'settings',
		'school_manage_details.png' => 'building',
		'school_manage_times.png' => 'clock',
		'school_manage_weeks.png' => 'calendar-range',
		'school_manage_rooms.png' => 'door-open',
		'school_manage_users.png' => 'users',
		'school_manage_departments.png' => 'building-2',
		'school_manage_holidays.png' => 'calendar-x',
		'school_manage_reports.png' => 'chart',
		'school_manage_search.png' => 'search',
		'school_manage_timetable.png' => 'calendar-range',
		'school_manage_xmlrpc.png' => 'network',
		'room_fields.png' => 'list-plus',
		'world.png' => 'globe',
		'eye.png' => 'eye',
		'vcard_key.png' => 'key',
		'lock.png' => 'lock',
		'logout.png' => 'log-out',
		'user.png' => 'user',
		'cake.png' => 'sparkles',
		'table.png' => 'table',
		'add.png' => 'plus',
		'edit.png' => 'edit',
		'delete.png' => 'trash',
		'cancel.png' => 'x',
		'disk.png' => 'save',
		'accept.png' => 'check',
		'tick.png' => 'check',
		'enabled.png' => 'check',
		'no.png' => 'x',
		'arrow_left.png' => 'chevron-left',
		'arrow_right.png' => 'chevron-right',
		'arrow_refresh.png' => 'refresh',
		'arrow_turn_left.png' => 'undo',
		'picture.png' => 'image',
		'photo.png' => 'image',
		'folder.png' => 'folder',
		'key.png' => 'key',
		'info.png' => 'info',
		'error.png' => 'alert-triangle',
		'bug.png' => 'bug',
		'pdf.png' => 'file-text',
		'report.png' => 'file-text',
		'chart.png' => 'chart',
		'chart_bar.png' => 'chart',
		'server_key.png' => 'key',
		'shield.png' => 'shield',
	];

	return $map[$icon] ?? preg_replace('/\.(png|gif|jpg|jpeg|svg)$/i', '', $icon);
}

function cps_icon($icon, $class = '', $label = null)
{
	$name = cps_icon_name($icon);
	$paths = [
		'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'calendar-days' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>',
		'calendar-range' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M7 14h5M12 18h5"/>',
		'calendar-x' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M10 14l4 4M14 14l-4 4"/>',
		'settings' => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-.4-1.1 1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.1-.4 1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 .4 1.1 1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.38.16.72.36 1 .6.28.24.6.4 1.1.4H21a2 2 0 1 1 0 4h-.09c-.5 0-.82.16-1.1.4-.28.24-.48.58-.6 1z"/>',
		'building' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-7h6v7M9 9h.01M12 9h.01M15 9h.01"/>',
		'building-2' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/>',
		'door-open' => '<path d="M13 4h3a2 2 0 0 1 2 2v14M2 20h20M13 20V2L6 4v16M10 12h.01"/>',
		'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
		'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>',
		'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
		'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 0 20M12 2a15.3 15.3 0 0 0 0 20"/>',
		'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
		'key' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="M11 12l9-9M15 8l2 2M18 5l2 2"/>',
		'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		'table' => '<path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18M15 3v18"/>',
		'list-plus' => '<path d="M11 12H3M16 6H3M11 18H3M18 9v6M15 12h6"/>',
		'plus' => '<path d="M12 5v14M5 12h14"/>',
		'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>',
		'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>',
		'x' => '<path d="M18 6L6 18M6 6l12 12"/>',
		'check' => '<path d="M20 6L9 17l-5-5"/>',
		'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
		'chevron-left' => '<path d="M15 18l-6-6 6-6"/>',
		'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
		'chevron-down' => '<path d="M6 9l6 6 6-6"/>',
		'refresh' => '<path d="M21 12a9 9 0 0 1-15.5 6.3L3 16M3 12A9 9 0 0 1 18.5 5.7L21 8M3 16v5h5M21 8V3h-5"/>',
		'undo' => '<path d="M9 14L4 9l5-5"/><path d="M4 9h10a7 7 0 0 1 7 7v1"/>',
		'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
		'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
		'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
		'alert-triangle' => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
		'bug' => '<path d="M8 2l1.9 1.9M16 2l-1.9 1.9M9 7h6M7 11H3M21 11h-4M7 15H3M21 15h-4M8 19l-2 2M16 19l2 2"/><rect x="7" y="4" width="10" height="16" rx="5"/>',
		'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8M8 9h2"/>',
		'chart' => '<path d="M3 3v18h18M8 17V9M13 17V5M18 17v-6"/>',
		'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		'sparkles' => '<path d="M12 3l1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3zM5 16l.8 2.2L8 19l-2.2.8L5 22l-.8-2.2L2 19l2.2-.8L5 16z"/>',
		'search' => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/>',
		'network' => '<rect x="16" y="16" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v4M5 16v-2h14v2"/>',
	];

	$body = $paths[$name] ?? $paths['info'];
	$class = trim('cps-svg-icon ' . $class);
	$label_attr = $label === null ? 'aria-hidden="true"' : 'role="img" aria-label="' . html_escape($label) . '"';

	return '<svg class="' . html_escape($class) . '" ' . $label_attr . ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . $body . '</svg>';
}



function iconbar($items = array(), $active = false) {

	$html = "<div class='iconbar'>";
	$i = 1;
	$max = count($items);

	foreach ($items as $item) {

		if (is_null($item)) {
			$max--;
			continue;
		}

		$attrs = '';

		if (isset($item['link'])) {
			extract($item);
		} else {
			[$link, $name, $icon] = $item;
		}

		$escape = true;
		if (isset($item['escape']) && $item['escape'] === false) {
			$escape = false;
		}

		$name = $escape ? html_escape($name) : $name;

		if (is_array($attrs)) {
			$attrs = _stringify_attributes($attrs);
		}

		if (isset($title)) {
			$title = html_escape($title);
			$attrs .= " title='$title'";
		}

		$class = ($link == $active)
			? 'active'
			: '';

		$img = cps_icon($icon, 'iconbar-svg', strip_tags($name));

		$count = '';
		if (isset($item['count'])) {
			$count_val = html_escape($item['count']);
			$count = "<span class='count'>({$count_val})</span>";
		}

		$meta = '';
		if (isset($item['meta'])) {
			$meta = "<span class='count'>{$item['meta']}</span>";
		}

		$label = anchor($link, "{$img} {$name}{$count}{$meta}", "class='{$class}' {$attrs}");
		$label = str_replace(site_url('#'), '#', $label);

		$html .= $label;

		if ($i < $max) {
			$html .= "<span class='iconbar-sep' aria-hidden='true'></span>";
		}

		$i++;
	}

	$html .= "</div>";

	return $html;
}


function tab_index($reset = false)
{
	static $_tab_index;

	if (empty($_tab_index) || $reset === true) {
		$_tab_index = 0;
	} else {
		$_tab_index++;
	}

	return $_tab_index;
}




function msgbox($type = 'error', $content = '', $escape = TRUE)
{
	if ($escape)
	{
		$content = nl2br(html_escape($content));
	}

	$html = "<div class='msgbox {$type}'>{$content}</div>";
	return $html;
}


function sort_link($base_uri, $param, $label)
{
	$CI =& get_instance();
	$get_data = $CI->input->get();
	$get_sort = $CI->input->get('sort') ?? '';
	$get_sort_field = ltrim($get_sort, '-');

	$get_data['sort'] = $param;

	if ($get_sort == $param) {
		$get_data['sort'] = "-{$param}";
	}

	$suffix = '';
	if ($get_sort == $param) {
		$suffix = '<span class="sort-arr">&#11205;</span>';
	} elseif ($get_sort == "-{$param}") {
		$suffix = '<span class="sort-arr">&#11206;</span>';
	}

	$query = http_build_query($get_data);
	$uri = $base_uri . '?' . $query;

	return anchor(site_url($uri), $label . $suffix, ['class' => 'sort-link']);
}


/**
 * script_src
 *
 * Render a <script> tag
 *
 * @access	public
 * @param	type	name
 * @return	type
 */
if (! function_exists('script_src')) {
	function script_src($src = '', $attributes = [])
	{
		$out = '<script';
		$attributes['src'] = $src;
		$out .= _stringify_attributes($attributes);
		$out .= "></script>\n";
		return $out;
	}
}


function date_picker_img($input_name)
{
	$hs = <<<EOS
on click call displayDatePicker('{$input_name}', false)
EOS;

	$title = html_escape(lang('app.choose_date'));
	$img = '<span class="cps-date-picker-icon" style="cursor:pointer" title="' . $title . '" script="' . html_escape($hs) . '">' . cps_icon('calendar-days', 'w-4 h-4') . '</span>';

	return $img;
}



function render_list_builder(array $params = [])
{
	$CI =& get_instance();
	$available_options = [];
	$selected_options = [];
	$value = $params['value'] ?? [];

	foreach (($params['options'] ?? []) as $id => $label) {
		// if (array_key_exists($id, $))
		if (in_array($id, $params['value'])) {
			$selected_options[$id] = $label;
		} else {
			$available_options[$id] = $label;
		}
	}

	$params['available_options'] = $available_options;
	$params['selected_options'] = $selected_options;

	return $CI->load->view('partials/list_builder', $params, true);
}

/*
function icon($name, $attributes = array())
{
	$CI =& get_instance();
	return $CI->feather->get($name, $attributes, FALSE);
}
*/
