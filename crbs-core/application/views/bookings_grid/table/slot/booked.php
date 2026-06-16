<?php

$template = "{user}{notes}{actions}";

$vars = [
	'{user}' => '',
	'{notes}' => '',
	'{actions}' => '',
];

$actions = [];

if ($show_user && ! empty($booking->user)) {

	$user_label = !empty($booking->user->displayname)
		? $booking->user->displayname
		: $booking->user->username;
	if (!empty($user_label)) {
		$vars['{user}'] = '<div class="booking-cell-user font-medium text-sm">' . html_escape($user_label) . '</div>';
	}
}

// Notes
//
if ($show_notes && !empty($booking->notes)) {
	$notes = html_escape($booking->notes);
	$vars['{notes}'] .= '<div class="booking-cell-notes text-xs mt-1 opacity-90">' . nl2br($notes) . '</div>';
}

if ( ! empty($actions)) {
	$vars['{actions}'] = '';
}

// Process template for items
$body = strtr($template, $vars);
// Remove tags that don't have content
$body = str_replace(array_keys($vars), '', $body);

if (empty(trim($body))) {
	$body = '&mdash;';
}


// URL params to pass to /view/ so it can return to source page
$params = ['params' => http_build_query($context->get_query_params()) ];
$uri = sprintf('bookings/view/%d?%s', $booking->booking_id, http_build_query($params));
$url = site_url($uri);

// For checkbox
//
$input_name = sprintf('bookings[]');
$input_id = sprintf('booking_%d', $booking->booking_id);
$input_value = $booking->booking_id;

// Deletable
//
$is_deletable = booking_cancelable($booking);

$is_recurring = !empty($booking->repeat_id);
$type_class = $is_recurring ? 'cps-slot-booked-recurring' : 'cps-slot-booked-single';
$owner_class = $is_deletable ? 'cps-slot-booked-mine' : 'cps-slot-booked-other';

?>

<div class='<?= $class ?> cps-slot cps-slot-booked <?= $type_class ?> <?= $owner_class ?>'>

	<?php if ($is_deletable): ?>
	<?php
	echo form_checkbox([
		'form' => 'form_cancel_multi',
		'name' => $input_name,
		'id' => $input_id,
		'value' => $input_value,
		'class' => 'bookings-grid-booked-check multi-select-content hidden mr-2',
		'data-multi' => 'true',
	]);
	?>
	<label
		class="bookings-grid-button multi-select-content hidden"
		data-multi="true"
		for="<?= $input_id ?>"
	>
		<?php
		echo $body;
		?>
	</label>
	<?php endif; ?>

	<?php

	$cls = 'bookings-grid-button cps-slot-link';

	if ($is_deletable) {
		$cls .= ' multi-select-content';
	}

	$link_attrs = [
		'class' => $cls,
		'up-target' => ".bookings-view",
		'up-layer' => "new modal",
		'up-size' => "large",
		'up-history' => "false",
		'up-preload' => '',
	];

	if ($is_deletable) {
		$link_attrs['data-multi'] = "false";
	}

	echo anchor($url, $body, $link_attrs);

	?>

</div>
