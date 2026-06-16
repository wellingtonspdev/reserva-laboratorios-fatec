<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Login extends MY_Controller
{


	public function __construct()
	{
		parent::__construct();
	}


	function index()
	{
		if ($this->input->post()) {
			$this->handle_submit();
		}

		$this->data['title'] = lang('auth.log_in');
		$this->data['hide_global_menu'] = true;

		$this->data['message'] = '';
		if (setting('login_message_enabled')) {
			$this->data['message'] = html_escape(setting('login_message_text'));
		}

		$image_url = image_url(setting('logo'));
		$logo_html = (!empty($image_url))
			? img($image_url, FALSE, [
				'alt' => html_escape(setting('name')),
				'class' => 'cps-login-logo-image',
			])
			: img([
				'src' => asset_url('assets/images/logos/fatec-itaquera-color.svg'),
				'alt' => 'Logo Fatec',
				'class' => 'cps-login-logo-image',
			]);

		$auth = (string) $this->session->flashdata('auth');
		$form = $this->load->view('login/login_index', $this->data, TRUE);

		$this->data['body'] = <<<HTML
<section class="cps-login-page">
	<div class="cps-login-card">
		<div class="cps-login-content">
			{$auth}
			{$form}
		</div>
		<div class="cps-login-brand" aria-label="Fatec Itaquera">
			{$logo_html}
		</div>
	</div>
</section>
HTML;

		return $this->render();
	}


	private function handle_submit()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username', 'lang:user.field.username', 'required|max_length[255]');
		$this->form_validation->set_rules('password', 'lang:user.field.password', 'required');

		// Run validation
		if ($this->form_validation->run() == FALSE) {
			return false;
		}

		$username = $this->input->post('username');
		$password = $this->input->post('password');

		if ($this->userauth->log_in($username, $password)) {
			// Success
			$uri = '';
			if (isset($_SESSION['post_login_uri'])) {
				$uri = $_SESSION['post_login_uri'];
				unset($_SESSION['post_login_uri']);
			}
			redirect($uri);
		} else {
			$this->data['error'] = msgbox('error', lang('auth.bad_credentials'));
			return false;
		}
	}


}
