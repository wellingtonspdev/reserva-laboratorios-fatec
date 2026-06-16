<?php
$data = $vars;
echo form_input(array(
		'name' => $data['name'],
		'id' => $data['name'],
		'size' => '7',
		'maxlength' => '7',
		'tabindex' => $data['tabindex'],
		'value' => $data['value'],
	));
	echo '<button type="button" onclick="showColorPicker(this,$(\''.$data['name'].'\'))" class="cps-colour-picker-button" title="Selecionar cor">' . cps_icon('settings', 'w-4 h-4') . '</button>';
?>
