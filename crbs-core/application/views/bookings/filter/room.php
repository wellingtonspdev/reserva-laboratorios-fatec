<?php
$rooms_grouped = [];
foreach ($rooms as $room) {
	$group_id = $room->room_group_id ?? 'ungrouped';
	$rooms_grouped[ $group_id ][ $room->room_id ] = $room;
}

$base_uri = site_url('bookings');
?>

<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 room-groups'>
	<?php foreach ($room_groups as $group) : ?>
		<div class='flex flex-col'>
			<h5 class='text-lg font-bold text-[var(--cps-red)] mb-1'>
				<?= html_escape($group->name) ?>
			</h5>

			<?php if ( ! empty($group->description)) : ?>
				<p class='text-sm text-gray-500 mb-3'>
					<?= html_escape($group->description) ?>
				</p>
			<?php endif; ?>

			<ul class='space-y-2 mt-2'>
				<?php
				$items = $rooms_grouped[$group->room_group_id] ?? [];
				foreach ($items as $room) :
					$query = [
						'room' => $room->room_id,
						'date' => $current_date,
					];
					$url = $base_uri . '?' . http_build_query($query);
					$room_name = html_escape($room->name);
					
					$isActive = ($room->room_id == $current_room);
					$linkClass = $isActive ? 'text-[var(--cps-red)] font-bold' : 'text-gray-700 hover:text-[var(--cps-red)]';
					
					$link = anchor($url, $room_name, [
						'class' => "block py-1 transition-colors {$linkClass}",
						'attrs' => 'up-follow up-preload',
					]);
				?>
					<li><?= $link ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endforeach; ?>
</div>
