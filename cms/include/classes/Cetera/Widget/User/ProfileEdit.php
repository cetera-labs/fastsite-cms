<?php
namespace Cetera\Widget\User; 

/**
 * Виджет "Постраничная навигация"
 * 
 * @package CeteraCMS
 */ 
class ProfileEdit extends \Cetera\Widget\Templateable {

	public static $name = 'User.Profile.Edit';

    protected $_params = array(
	    'template'        => 'default.twig',
		'unique_email'    => false,
		'change_login'    => false,
		'change_password' => false,
    ); 
	
	protected $user;
	protected $fields = array();
	protected $not_editable = array(
		'tag','date_reg','last_login','disabled','password'
	);
	
	public $errors = array();
	public $success = null;
	
	public function init()
	{
		if (!$this->getParam('change_login'))
		{
			$this->not_editable[] = 'login';
		}
		
		$this->user = $this->application->getUser();
		if (!$this->user) return;
		
		if (isset($_POST[$this->getUniqueId()]))
		{	
			if ($_POST['new_password'] && $this->getParam('change_password') )
			{
				if ($_POST['new_password'] != $_POST['new_password2']) {
					$this->errors['new_password'] = true;
					$this->errors['new_password2'] = $this->t->_('Пароли не совпадают');
				}
				elseif (md5($_POST['password']) != $this->user->password) {
					$this->errors['password'] = $this->t->_('Вы ввели неправильный пароль');
				}
				else {
					$this->user->password = $_POST['new_password'];
				}
			}				

			unset($_POST[$this->getUniqueId()]);
			unset($_POST['password']);
			unset($_POST['new_password']);
			unset($_POST['new_password2']);
			
			$fields = $this->user->getFieldsDef();
			foreach ($_POST as $name => $value)
			{
				if (in_array($name, $this->not_editable)) continue;
				if (!isset($fields[$name])) continue;
				if(!$fields[$name]['show']) continue;
				
				if ($name == 'email' && $this->getParam('unique_email') && $this->user->email != $value)
				{
					$u = \Cetera\User::getByEmail($value);
					if ($u)
					{
						$this->errors[$name] = array(
							'message' => $this->t->_('Пользователь с таким e-mail уже зарегистрирован'),
							'value' => $value,
						);
						continue;
					}					
				}
				
				$this->user->{$name} = $value;
			}
			if (!count($this->errors))
			{
				try {
					$this->user->save();
					$this->success = $this->t->_('Данные сохранены');
				}
				catch (\Cetera\Exception\Form $e) {
					$this->errors[$e->field] = array(
						'message' => $e->getMessage(),
						'value'   => $this->user->{$e->field},
					);					
				}
				
			}
			
			if (count($this->errors))
			{
				$this->errors[] = array(
					'message' => $this->t->_('Данные не сохранены. Исправьте ошибки и повторите попытку.'),
				);				
			}
			
		}
			
		foreach($this->user->getFieldsDef() as $f)
		{
			if(!$f['show']) continue;
			if (in_array($f['name'], $this->not_editable)) continue;
			
			$this->fields[] = array(
				'name'    => $f['name'],
				'describ' => $f['describ'],
				'value'   => $this->user->{$f['name']},
				'required'=> $f['required'],
				'error'   => false,
				'type'    => 'text',
			);
			
			if (isset($this->errors[$f['name']]))
			{
				$id = count($this->fields) - 1;
				$this->fields[$id]['error'] = $this->errors[$f['name']]['message'];
				$this->fields[$id]['value'] = $this->errors[$f['name']]['value'];
				unset($this->errors[$f['name']]);
			}
		}

		if ( $this->getParam('change_password') )
		{
			$this->fields[] = array(
				'name'    => 'password',
				'describ' => $this->t->_('Старый пароль'),
				'type'    => 'password',
				'required'=> false,
				'error'   => isset($this->errors['password'])?$this->errors['password']:false,
				'value'   => null,
			);	

			$this->fields[] = array(
				'name'    => 'new_password',
				'describ' => $this->t->_('Новый пароль'),
				'type'    => 'password',
				'required'=> false,
				'error'   => isset($this->errors['new_password'])?$this->errors['new_password']:false,
				'value'   => null,
			);	

			$this->fields[] = array(
				'name'    => 'new_password2',
				'describ' => $this->t->_('Повторите пароль'),
				'type'    => 'password',
				'required'=> false,
				'error'   => isset($this->errors['new_password2'])?$this->errors['new_password2']:false,
				'value'   => null
			);
			unset($this->errors['password']);			
			unset($this->errors['new_password']);			
			unset($this->errors['new_password2']);			
		}
		
	}

	public function getFields()
	{
		if (!$this->user) return null;	
		return $this->fields;		
	}
	
	public function getUser()
	{
		return $this->user;		
	}	
}