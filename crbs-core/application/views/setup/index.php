<?php

echo $this->session->flashdata('saved');

$order = [
	'users',
	'resources',
	'timetable',
	'setup',
];

echo "<div class='setup-menu cps-settings-hub'>";

foreach ($order as $group) {
	if ( ! isset($setup_menu[$group])) continue;
	$items = $setup_menu[$group];
	$title = lang('setup.group.'.$group);
	echo "<section class='cps-settings-group'>";
	echo "<h3 class='setup-menu-heading'>{$title}</h3>";
	echo "<div class='cps-settings-links'>";
	if ($items !== null) {
		foreach ($items as $link) {
			echo '<a href="'.$link['url'].'" class="setup-menu-link">';
			echo '<span class="cps-settings-link-icon">' . cps_icon($link['icon'], 'w-5 h-5', $link['label']) . '</span>';
			echo '<span class="cps-settings-link-text">' . html_escape($link['label']) . '</span>';
			echo '<span class="cps-settings-link-arrow">' . cps_icon('chevron-right', 'w-4 h-4') . '</span>';
			echo '</a>';
		}
	}
	echo "</div>";
	echo "</section>";
}
echo "</div>";
