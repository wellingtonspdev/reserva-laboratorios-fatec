<?php
echo $notice ?? '';
?>

<p>O classroombookings foi atualizado!</p>
<br>
<p>Verifique se voce consegue entrar e se tudo funciona como esperado.</p>
<p>Se tudo estiver correto, exclua os itens abaixo que nao sao mais necessarios:</p>
<ul>
	<li><strong>system</strong> pasta</li>
	<li><strong>temp</strong> pasta</li>
	<li><strong>webroot</strong> pasta</li>
	<li><strong>classroombookings.sql</strong> arquivo</li>
	<li><strong>license.txt</strong> arquivo</li>
</ul>

<?php
echo iconbar(array(
	array('login', 'Clique aqui para entrar', 'user_go.png'),
));
