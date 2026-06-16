<?php echo $db ?>

<p>Configuracao concluida com sucesso para <?php echo stripslashes((string) $school['name']) ?>!</p>

<p>
<?php
$icondata[0] = array('login', 'Clique aqui para entrar', 'user_go.png' );
$this->load->view('partials/iconbar', $icondata);
?>
</p>
