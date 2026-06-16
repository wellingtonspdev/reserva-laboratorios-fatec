<?php
$url = site_url("rooms/info/{$room->room_id}");
$name = html_escape($room->name);
$link = anchor($url, $name, [
	'class'       => 'hover:underline',
]);
?>

<th class="bookings-grid-header-cell bookings-grid-header-cell-room">
	<strong><?= $link ?></strong>
	<?php
	$owner = '';
	if ($room->owner) {
		$owner = $room->owner->displayname ?: $room->owner->username;
		$owner = html_escape($owner);
		echo "<span class='text-xs opacity-80 block font-normal mt-0.5'>{$owner}</span>";
	}
	?>
</th>
