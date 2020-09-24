<?php
namespace Cetera\Widget\User; 

/**
 * Виджет "Форма регистрации пользователя"
 * 
 * @package FastsiteCMS
 */ 
class Recover extends \Cetera\Widget\Templateable {
	
	public static $name = 'User.Recover';
	
	public $post = null;	
	public $error = null;	
	public $success = null;	

    protected $_params = array(
		'unique_email' => false,
	    'template'     => 'default.twig',
		'from_email'   => 'no-reply@cetera.ru',
		'from_name'    => false,
		'ajax'         => false,
    ); 

	protected function init()
	{
		if (isset($_POST['UserRecover']))
		{
			$this->post = $_POST;
			
			if ($this->post['value'])
			{
				$u = \Cetera\User::getByLogin($this->post['value']);
				if (!$u)
				{
					$u = \Cetera\User::getByEmail($this->post['value']);
				}
			}
			
			if (!$u)
			{
				$this->error = $this->t->_('Пользователь не найден');
			}
			else
			{
				try {
					$u->recoverPassword($this->getParam('from_email'), $this->getParam('from_name'));			
					if ($this->getParam('success_message')) {
						$this->success = $this->getParam('success_message');
					}
					else {
						$this->success = $this->t->_('Новый пароль выслан вам по электронной почте');
					}
					$this->post['value'] = '';					
				}
				catch (\Exception $e) {
					$this->error = $e->getMessage();
				}
			}
			
		}
		
	}	
      
}