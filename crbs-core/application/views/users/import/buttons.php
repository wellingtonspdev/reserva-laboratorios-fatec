<?php
$data['name'] = 'submit';
$data['tabindex'] = $t;
?>
<div style="border-top:24px;text-align:center;">
  <?php
  $disabled = ($stage == $stage_config['first']) ? $data['disabled'] = 'disabled' : 0; 
  $data['value'] = '   < ' . lang('app.action.back') . '   ';
	echo form_submit($data)
	?> 
	&nbsp;&nbsp; 
	<?php
	unset($data['disabled']);
	$data['value'] = ($stage == $stage_config['last']) ? ' Concluir ' : '   Avancar >   ';
	$data['tabindex'] = $t+1;
	echo form_submit($data)
	?>
</div>
