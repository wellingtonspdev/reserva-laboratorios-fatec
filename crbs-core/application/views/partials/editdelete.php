<?php
if (!empty($edit)) {
	$img = cps_icon('edit', 'w-4 h-4', 'Editar');
	echo anchor("{$edit}", $img . '<span class="cps-sr-only">Editar</span>', 'title="Editar" class="cps-admin-btn"');
}

$img = cps_icon('trash', 'w-4 h-4', 'Excluir');
echo anchor("{$delete}", $img . '<span class="cps-sr-only">Excluir</span>', 'title="Excluir" class="cps-admin-btn cps-admin-btn-danger"');
