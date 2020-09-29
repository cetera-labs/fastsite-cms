<?php
namespace Cetera\Widget\User; 

/**
 * Виджет "Форма регистрации пользователя"
 * 
 * @package FastsiteCMS
 */ 
class Register extends \Cetera\Widget\Templateable {
	
	use \Cetera\Widget\Traits\ReCaptcha;
	
	public static $name = 'User.Register';
	
	public $post = null;	
	public $error = false;
	public $error_message = '';
	public $error_field = null;
	public $success = false;
	public $login_error = null;
	public $email_error = null;
	public $recaptcha_error = null;
	public $password_error = null;
	public $password2_error = null;
		
    protected $_params = array(
		'unique_email'   => false,
		'email_is_login' => false,
		'check_password' => true,
	    'template'       => 'default.twig',
		
		'success_auth'     => true,
		'success_redirect' => '/',
		
		'recaptcha'		       => false,
		'recaptcha_site_key'   => null,
		'recaptcha_secret_key' => null,		
    ); 
	

	protected function init()
	{
		$this->initRecaptcha();
		
		if (isset($_POST['UserRegister']) && $_POST['UserRegister'] == $this->getUniqueId()) {
			$this->post = $_POST;
			try {
				
				$this->checkRecaptcha();	

				if ($this->getParam('email_is_login')) {
					$_POST['login'] = $_POST['email'];
				}		

				if ($this->getParam('check_password') && $_POST['password'] != $_POST['password2']) {
					throw new \Cetera\Exception\Form($this->t->_('Пароли не совпадают'), 'password', 'password');
				}
				
				$u = \Cetera\User::register($_POST, $this->getParam('unique_email'));
				
				$this->success = true;
				
				if ( $this->getParam('success_auth') ) {				
					$result = $this->application->getAuth()->authenticate(new \Cetera\UserAuthAdapter( array(
						'login' => $_POST['login'],
						'pass'  => $_POST['password'],
					) )); 
				}
				
				if ( $this->getParam('success_redirect') ) {
					header('Location: '.$this->getParam('success_redirect'));
					die();
				}
			}
			catch (\Cetera\Exception\Form $e) {

				$field = $e->field.'_error';
				
				if ($field == 'login_error' && $this->getParam('email_is_login')) {
					$field == 'email_error';
				}
				
				$this->$field = $e->getMessage();
				$this->error_message = $e->getMessage();
				$this->error_field = $field;
				$this->error = true;
				return;
			}
			catch (\Exception $e) {
				$this->error = true;
				$this->error_message = $e->getMessage();
				return;
			}			
		}
		
	}	
      
}