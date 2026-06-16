<?php
echo $notice ?? '';
echo form_open_multipart(current_url(), array('class' => 'cssform', 'id' => 'install_step_config'));
?>

<fieldset>

	<legend accesskey="D" tabindex="<?php echo tab_index() ?>">Dados de conexao com o banco de dados</legend>

	<p>
		<label for="hostname" class="required">Host</label>
		<?php
		$field = 'hostname';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '20',
			'maxlength' => '50',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="port" class="required">Port</label>
		<?php
		$field = 'port';
		$value = set_value($field, $_SESSION['data'][$field] ?? '3306', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '10',
			'maxlength' => '5',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="database" class="required">Nome do banco de dados</label>
		<?php
		$field = 'database';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '20',
			'maxlength' => '50',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="username" class="required">Username</label>
		<?php
		$field = 'username';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '20',
			'maxlength' => '100',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="password" class="required">Password</label>
		<?php
		$field = 'password';
		$value = set_value($field, $_SESSION['data'][$field] ?? '', FALSE);
		echo form_input(array(
			'type' => 'password',
			'name' => $field,
			'id' => $field,
			'size' => '20',
			'maxlength' => '100',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

</fieldset>


<fieldset>

	<legend accesskey="C" tabindex="<?php echo tab_index() ?>">Configuracao</legend>

	<p>
		<label for="url" class="required">URL</label>
		<?php
		$default = config_item('base_url');
		$field = 'url';
		$value = set_value($field, $_SESSION['data'][$field] ?? $default, FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '40',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<br>
		<br>
		<p class="hint">Este e o endereco web em que o classroombookings sera acessado. Ele deve terminar com uma barra /.</p>
	</p>
	<?php echo form_error($field); ?>

</fieldset>

<?php

$this->load->view('partials/submit', array(
	'submit' => array('Avancar', tab_index()),
	// 'cancel' => array('Cancel', tab_index(), 'users'),
));

echo form_close();
