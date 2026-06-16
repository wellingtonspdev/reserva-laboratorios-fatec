<?php
echo $notice ?? '';
echo form_open_multipart(current_url(), array('class' => 'cssform', 'id' => 'install_step2'));
?>

<fieldset>

	<legend accesskey="S" tabindex="<?php echo tab_index() ?>">Configuracoes</legend>

	<p>
		<label for="database" class="required">Nome da instituicao</label>
		<?php
		$field = 'name';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '40',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<br><br>
	</p>
	<?php echo form_error($field); ?>

</fieldset>


<fieldset>

	<legend accesskey="U" tabindex="<?php echo tab_index() ?>">Usuario administrador</legend>

	<p>
		<label for="url" class="required">Username</label>
		<?php
		$field = 'admin_username';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '25',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="database" class="required">Password</label>
		<?php
		$field = 'admin_password';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '25',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Pelo menos 8 caracteres.</p>
	</p>
	<?php echo form_error($field); ?>

</fieldset>

<?php

$this->load->view('partials/submit', array(
	'submit' => array('Avancar', tab_index()),
	'cancel' => array('Voltar', tab_index(), 'install/config'),
));

echo form_close();
