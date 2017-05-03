<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera;
 
class UserAuthAdapterULogin extends ExternalUserAuthAdapter implements \Zend_Auth_Adapter_Interface {
        
    protected function getUser()
    {
		$u = User::getExternal($this->user['network'], $this->user['uid']);
		
		if ($u) return $u;
				
        if ($this->user['email'])
		{
			$u = User::getByEmail( $this->user['email'] );
			if ($u)
			{
				$u->addExternal( $this->user['network'], $this->user['uid'] );
				$u = User::getExternal($this->user['network'], $this->user['uid']);
				if ($u) return $u;
			}
		}
		
        $name = '';
        if ($this->user['first_name']) $name .= $this->user['first_name'];
        if ($this->user['last_name']) $name .= ' '.$this->user['last_name']; 
            
        $login = $this->user['uid'];
		
		$u = User::register(array(
            'login'       => $login, 
            'name'        => $name, 
            'email'       => $this->user['email'], 
            'disabled'    => 0
        ));
            
		$u->addExternal( $this->user['network'], $this->user['uid'] );
		$u = User::getExternal($this->user['network'], $this->user['uid']);

        return $u;
    }

}