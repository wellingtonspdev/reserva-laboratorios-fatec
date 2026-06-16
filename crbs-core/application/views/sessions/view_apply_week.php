<?php
$attrs = ['class' => 'cssform-stacked'];
echo form_open('sessions/apply_week', $attrs, ['session_id' => $session->session_id]);
?>

<fieldset>

	<legend>Aplicacao em lote</legend>

	<div style="padding: 12px 0 12px 0;">
		Aplicar a semana do calendario selecionada a todas as semanas deste semestre.
	</div>

	<p>
		<?php
		$options = array('' => 'Selecione uma semana...');
		if (isset($weeks)) {
			foreach ($weeks as $week) {
				$options[$week->week_id] = html_escape($week->name);
			}
		}
		echo form_dropdown([
			'name' => 'week_id',
			'id' => 'week_id',
			'options' => $options,
		]);
		?>
	</p>


	<?php
	$this->load->view('partials/submit', array(
		'submit' => array('Aplicar semana', tab_index()),
	));
	?>

</fieldset>

<?= form_close() ?>
