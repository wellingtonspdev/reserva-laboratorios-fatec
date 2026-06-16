<?php
if (!empty($message)) {
	echo "<div class='cps-login-message'>" . nl2br((string) $message) . "</div>";
}

echo $error ?? '';

echo validation_errors();

echo form_open(current_url(), array('id'=>'login','class'=>'cps-login-form'), array('page' => $this->uri->uri_string()) );

?>

<div class="cps-login-heading">
	<h1><?= lang('auth.log_in') ?></h1>
</div>

<div class="cps-login-field">
	<label for="username" class="required"><?= lang('user.field.username') ?></label>
	  <?php
		$value = set_value('username', '', FALSE);
		echo form_input(array(
			'name' => 'username',
			'id' => 'username',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
			'class' => 'cps-login-input',
			'autocomplete' => 'username',
		));
		?>
</div>

<div class="cps-login-field">
	<label for="password" class="required"><?= lang('user.field.password') ?></label>
	  <?php
		echo form_password(array(
			'name' => 'password',
			'id' => 'password',
			'tabindex' => tab_index(),
			'class' => 'cps-login-input',
			'autocomplete' => 'current-password',
		));
		?>
</div>

<?php
echo form_button([
	'type' => 'submit',
	'content' => lang('auth.log_in'),
	'tabindex' => tab_index(),
	'class' => 'cps-login-submit',
]);

echo form_close();

?>

<div class="cps-login-assistance" aria-label="Informacoes de acesso">
	<section class="cps-login-info-card">
		<h2>&Eacute; o seu primeiro acesso, professor(a)?</h2>
		<p>
			Para saber como solicitar o seu cadastro e aprender a utilizar o sistema,
			<a href="https://suporte.fatecitaquera.com.br/knowledgebase.php?article=4" target="_blank" rel="noopener noreferrer">acesse o nosso manual de instru&ccedil;&otilde;es clicando aqui.</a>
		</p>
	</section>

	<section class="cps-login-info-card">
		<h2>Estudantes</h2>
		<p>Desejam saber onde ser&aacute; sua pr&oacute;xima aula?</p>
		<dl class="cps-login-student-access">
			<div>
				<dt>Nome de usu&aacute;rio:</dt>
				<dd>aluno</dd>
			</div>
			<div>
				<dt>Senha:</dt>
				<dd>aluno1234</dd>
			</div>
		</dl>
	</section>
</div>
