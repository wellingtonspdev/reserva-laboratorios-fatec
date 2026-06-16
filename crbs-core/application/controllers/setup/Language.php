<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller
{

	private array $available_languages = [];
	private array $enabled_languages = [];


	public function __construct()
	{
		parent::__construct();

		$this->require_logged_in();
		$this->require_permission(Permission::SETUP_SETTINGS);
	}


	/**
	 * Language settings
	 *
	 */
	public function index()
	{
		$this->data['all_languages'] = $this->lang->get_languages();
		$this->available_languages = $this->data['all_languages'];

		$opts = [];
		foreach ($this->data['all_languages'] as $lang_id) {
			$title = lang(sprintf('language.lang.%s', $lang_id));
			$title = empty($title) ? $lang_id : html_escape($title);
			$opts[ $lang_id ] = $title;
		}

		$this->data['language_options'] = $opts;

		$this->data['settings'] = $this->settings_model->get_all('lang');
		$this->data['settings']['languages'] = $this->normalise_enabled_languages($this->data['settings']['languages'] ?? []);

		if ($this->input->post()) {
			$this->save();
		}

		$this->data['title'] = lang('language.language');
		$this->data['showtitle'] = $this->data['title'];

		$body = $this->load->view('setup/language/language', $this->data, TRUE);

		$this->data['body'] = $body;

		return $this->render();
	}


	/**
	* Controller function to handle a submitted form
	*
	*/
	private function save()
	{
		$this->load->library('form_validation');

		$this->available_languages = $this->lang->get_languages();
		$this->enabled_languages = $this->normalise_enabled_languages($this->input->post('languages') ?: []);

		$this->form_validation->set_rules('languages[]', 'lang:language.field.languages', 'required|callback__valid_languages');
		$this->form_validation->set_rules('default_language', 'lang:language.field.default_language', 'required|callback__valid_default_language');

		if ($this->form_validation->run() == false) {
			return false;
		}

		$settings = array(
			'languages' => $this->enabled_languages,
			'default_language' => $this->input->post('default_language'),
		);

		$this->settings_model->set($settings, 'lang');

		$this->session->set_flashdata('saved', msgbox('info', lang('language.save.success')));

		redirect('setup/language');
	}

	function _valid_languages()
	{
		foreach ($this->enabled_languages as $lang_id) {
			if ( ! in_array($lang_id, $this->available_languages, true)) {
				$this->form_validation->set_message('_valid_languages', 'A lista de idiomas contem uma opcao invalida.');
				return false;
			}
		}

		if ( ! in_array('english', $this->enabled_languages, true)) {
			$this->form_validation->set_message('_valid_languages', 'O ingles deve permanecer habilitado como idioma tecnico de fallback.');
			return false;
		}

		return true;
	}


	function _valid_default_language($default_language)
	{
		if ( ! in_array($default_language, $this->available_languages, true)) {
			$this->form_validation->set_message('_valid_default_language', 'O idioma padrao selecionado nao existe.');
			return false;
		}

		if ( ! in_array($default_language, $this->enabled_languages, true)) {
			$this->form_validation->set_message('_valid_default_language', 'O idioma padrao deve estar entre os idiomas habilitados.');
			return false;
		}

		return true;
	}


	private function normalise_enabled_languages(array $languages): array
	{
		$languages[] = 'english';
		$languages = array_values(array_unique(array_filter($languages, 'is_string')));
		sort($languages);

		return $languages;
	}


}
