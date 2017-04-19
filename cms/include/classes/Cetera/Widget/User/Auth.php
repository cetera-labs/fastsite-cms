<?php
namespace Cetera\Widget\User; 

/**
 * Виджет "Постраничная навигация"
 * 
 * @package CeteraCMS
 */ 
class Auth extends \Cetera\Widget\Templateable {
	
	public static $name = 'User.Auth';
	
	public $password_error = false;
	public $login_error = false;
	public $login = '';
	public $redirect = '';
		 
    protected $_params = array(
		'register_url'        => '/register',
		'profile_url'         => '/personal',
		'recover_password_url'=> false,
	    'template'            => 'default.twig',
		'authorized_redirect' => false,
		'social'			  => false,
		'ajax'                => false,
    ); 

	protected function init()
	{
		if (isset($_POST['UserAuth']) && $_POST['UserAuth'] == $this->getUniqueId())
		{
			$_POST['email'] = $_POST['login'];
			$this->login = $_POST['login'];
			$result = $this->application->getAuth()->authenticate(new \Cetera\UserAuthAdapter($_POST)); 
			switch ($result->getCode()) {
				case \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
					$this->login_error = 'Пользователь не найден';
					break;
			
				case \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
					$this->password_error = 'Неправильный пароль';
					break;
			}			
		}
		
		if ($this->getParam('social')) {
		
			if (isset($_POST['token'])) {
				
				$client = new \GuzzleHttp\Client();
				$res = $client->get('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);		
				$u = json_decode( $res->getBody(), true);
				if ($u && $u['uid']) {
					
					$user = $this->application->getUser();
					if ($user)
					{
						$user->addExternal( $u['network'], $u['uid'] );
					}
					else 
					{
						\Cetera\User::getExternal($u['network'], $u['uid']);
						$this->application->getAuth()->authenticate(new \Cetera\UserAuthAdapterULogin($u, false));
					}
					
				}
			}
			else {
				$this->application->addScript('//ulogin.ru/js/ulogin.js');
				$this->redirect = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			}
		}
		
		if ($this->application->getUser() && $this->getParam('authorized_redirect') && !$this->getParam('ajaxCall') && isset($_POST['UserAuth']) && $_POST['UserAuth'] == $this->getUniqueId())
		{
			header('Location: '.$this->getParam('authorized_redirect'));
			die();				
		}
	}

    protected function _getHtml()
    {
		if ($this->getParam('ajaxCall')) {
			$res = array(
				'success' => $this->application->getUser()?true: false
			);
			if (!$res['success'] || !$this->getParam('authorized_redirect')) {
				$res['html'] = parent::_getHtml();
			}
			else {
				$res['redirect'] = $this->getParam('authorized_redirect');
			}
			return json_encode($res);
		}
		else {
			return parent::_getHtml();
		}
    }	
      
}