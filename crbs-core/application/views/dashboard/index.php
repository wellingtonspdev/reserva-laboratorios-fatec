<?php

echo $this->session->flashdata('saved');

$img = cps_icon('calendar', 'w-4 h-4 mr-2');
echo '<div class="mb-6">';
echo anchor('bookings', $img . lang('booking.bookings'), 'class="inline-flex items-center text-cps-black hover:text-cps-red font-bold text-lg no-underline"');
echo '</div>';

$this->load->view('dashboard/stats');

?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

	<?php $this->load->view('dashboard/user_bookings') ?>
	<?php $this->load->view('dashboard/room_bookings') ?>

</div>
