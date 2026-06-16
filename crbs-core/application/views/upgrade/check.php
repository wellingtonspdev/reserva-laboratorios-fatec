<?php
echo $notice ?? '';

echo "<div class='req-error'>";
echo msgbox('exclamation', "Corrija os erros abaixo e atualize a pagina antes de continuar.");
echo "</div>";

echo form_open(current_url(), array('class' => 'cssform', 'id' => 'upgrade_check'));

echo form_hidden('upgrade', '1');

$items = array(
	'php_version' => 'PHP versao 5.5.0 ou superior',
	'php_module_gd' => "Modulo PHP 'GD' disponivel",
	'database' => 'Conexao com o banco de dados',
	'database_has_tables' => 'Banco de dados possui tabelas do classroombookings',
	'folder_local' => "Diretorio 'local' existe e permite escrita",
	'folder_uploads' => "Diretorio 'uploads' existe e permite escrita",
);

$errors = 0;
?>

<div>Faca um backup do banco de dados do classroombookings antes de continuar.</div>
<br><br>


<fieldset>

	<legend accesskey="C" tabindex="<?php echo tab_index() ?>">Configuracao</legend>

	<p>
		<label for="url" class="required">URL</label>
		<?php
		$default = config_item('base_url');
		$field = 'url';
		$value = set_value($field, $_SESSION[$field] ?? $default, FALSE);
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

<table cellpadding="2" cellspacing="2" width="100%" class="req-table">

	<thead>
		<tr class="heading">
			<td class="h">Requisito</td>
			<td class="h">Status</td>
		</tr>
	</thead>

	<tbody>

		<?php
		foreach ($items as $name => $label) {

			$status = '-';
			$message = '';


			if (array_key_exists($name, $requirements) && is_array($requirements[$name])) {

				if ($requirements[$name]['status'] == 'ok') {
					$status = "<span class='line-status status-ok'>OK</span>";
				}

				if ($requirements[$name]['status'] == 'err') {
					$errors++;
					$status = "<span class='line-status status-err'>Erro</span>";
				}

				if (array_key_exists('message', $requirements[$name])) {
					$message = $requirements[$name]['message'];
				}

			}

			echo "<tr>";
			echo "<td class='req-table-label'>";
			echo "<div class='req-table-label-title'>{$label}</div>";
			echo "<div class='req-table-label-message'>{$message}</div>";
			echo "</td>";
			echo "<td class='req-table-status'>{$status}</td>";
			echo "</tr>";
		}
		?>

	</tbody>

</table>


<?php

if ($errors === 0) {
	$this->load->view('partials/submit', array(
		'submit' => array('Atualizar', tab_index()),
	));
	echo "<style>.req-error{ display: none }</style>";
} else {
	echo "<style>.req-error{ display: block }</style>";
}

echo form_close();

?>

<style>
.req-table tr td {
	border-bottom: 1px solid #ddd;
	padding: 10px;
}
.req-table .req-table-label-title {
	font-weight: normal;
	margin: 0 0 5px 0;
	font-size: 115%;
}
.req-table .req-table-label-message {
	font-weight: normal;
	font-size: 90%;
	color: #666;
}

.line-status {
	font-weight: bold;
	background: #ccc;
	padding: 4px;
}

.line-status.status-ok {
	background: #3D9970;
	color: #fff;
}
.line-status.status-err {
	background: #85144b;
	color: #fff;
}
</style>
