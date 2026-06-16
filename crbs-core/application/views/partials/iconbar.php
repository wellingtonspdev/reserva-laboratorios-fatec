<?php
$data = $vars;
$items = count($data);
$html = '<p class="iconbar">';
for( $z=0; $z<$items; $z++){
	$link = $data[$z][0];
	$name = $data[$z][1];
	$icon = $data[$z][2];
	$html .= '<a href="'.site_url($link).'">';
	$html .= cps_icon($icon, 'iconbar-svg', $name) . ' ';
	$html .= $name . '</a>';
	if( $z != ($items-1) ){
		$html .= '<span class="iconbar-sep" aria-hidden="true"></span>';
	}
}
$html .= '</p>';
echo $html;
?>
