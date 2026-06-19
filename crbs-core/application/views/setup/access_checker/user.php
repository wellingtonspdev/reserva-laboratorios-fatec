<?php

$uri = 'setup/access_checker/user/'.$user->user_id;

$attrs = [
	'class' => 'cssform cssform-stacked cps-access-checker-form',
	'hx-post' => site_url($uri),
	'hx-target' => 'this',
	'hx-swap' => 'outerHTML',
];

$hidden = [];

echo form_open($uri, $attrs, $hidden);

?>

<fieldset>

	<legend><?= lang('acl.access_checker.room') ?></legend>

	<p class="input-group cps-access-checker-field">
		<?php
		echo form_label(lang('room.room'), 'check_room_id');
		echo form_dropdown([
			'name' => 'room_id',
			'id' => 'check_room_id',
			'options' => ['' => ''] + $room_options,
			'selected' => set_value('room_id', ''),
			'style' => 'width:100%',
		]);
	?>
	</p>

	<?php
	echo form_submit([
		'value' => lang('acl.actions.check_access'),
		'class' => 'cps-access-checker-submit',
	]);
	?>

	<div class="cps-access-checker-result">
		<?php $this->load->view('setup/access_checker/_result') ?>
	</div>

</fieldset>

<?= form_close() ?>

