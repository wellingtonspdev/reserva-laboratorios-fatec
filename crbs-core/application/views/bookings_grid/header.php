<?php

$classes = [
	'bookings-grid-header',
	'bg-[var(--cps-red)]',
	'text-black',
	'rounded-lg',
	'p-3',
	'mb-4',
	'shadow-md'
];

?>

<div class="<?= implode(' ', $classes) ?>">
	<div class="flex flex-col sm:flex-row items-center justify-between gap-3">

		<div class="flex-shrink-0 w-full sm:w-auto flex justify-start">
			<?php
			if ($prev) {
				echo anchor($prev['url'], '← ' . $prev['label'], [
					'class' => 'cps-btn-secondary bg-white text-black hover:bg-gray-100 text-sm px-4 py-2 border-none shadow-sm w-full sm:w-auto text-center',
					'up-follow' => '',
					'up-preload' => '',
				]);
			}
			?>
		</div>

		<div class="flex-1 text-center font-bold text-base sm:text-lg leading-tight">
			<?= $title ?>
		</div>

		<div class="flex-shrink-0 w-full sm:w-auto flex justify-end">
			<?php
			if ($next) {
				echo anchor($next['url'], $next['label'] . ' →', [
					'class' => 'cps-btn-secondary bg-white text-black hover:bg-gray-100 text-sm px-4 py-2 border-none shadow-sm w-full sm:w-auto text-center',
					'up-follow' => '',
					'up-preload' => '',
				]);
			}
			?>
		</div>
	</div>
</div>
