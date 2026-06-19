<?php

$blocks = [];

$blocks[] = [
	'title' => 'Todas as reservas',
	'figure' => $totals['all'],
];

$blocks[] = [
	'title' => 'Reservas neste semestre',
	'figure' => $totals['session'],
];

$blocks[] = [
	'title' => 'Reservas ativas',
	'figure' => $totals['active'],
];

if ( ! is_null($constraints['max_active_bookings'])) {
	$blocks[] = [
		'title' => 'Maximo de reservas ativas',
		'figure' => $constraints['max_active_bookings'],
	];
	$blocks[] = [
		'title' => 'Reservas que voce pode criar',
		'figure' => ($constraints['max_active_bookings'] - $totals['active']),
	];
}

echo "<div class='cps-dashboard-stats'>";

foreach ($blocks as $block) {

	$figure = number_format($block['figure']);
	$figure_html = "<dt class='text-2xl font-bold text-cps-red mt-1'>{$figure}</dt>";

	$title = html_escape($block['title']);
	$title_html = "<dd class='text-xs text-cps-gray-text uppercase tracking-wide m-0'>{$title}</dd>";

	$block_content = "<div class='cps-dashboard-stat-card'><dl class='m-0 p-0'>{$title_html}{$figure_html}</dl></div>";

	echo $block_content;

}

echo "</div>";
