<?php
namespace Cetera\Widget\Traits; 

trait ReCaptcha {
	
	public function showRecaptcha()
	{
		return $this->getParam('recaptcha') && $this->getParam('recaptcha_site_key');
	}

	public function initRecaptcha()
	{
		if ( $this->showRecaptcha() ) {
			$this->application->addScript('https://www.google.com/recaptcha/api.js');
		}			
	}
	
	public function checkRecaptcha()
	{
		if ($this->showRecaptcha()) {
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
				'form_params' => [
					'secret'   => $this->getParam('recaptcha_secret_key'),
					'response' => $_REQUEST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR'],
				]
			]);	
			$res = json_decode($response->getBody(), true);
			if (!$res['success'])
			{
				throw new \Cetera\Exception\Form($this->t->_('Проверка не пройдена'), 'recaptcha');
			}
		}		
	}	
	
}