<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
 
abstract class ExternalUserAuthAdapter implements AdapterInterface {
    protected $_authenticateResultInfo = null;
    protected $user;
    private $_backoffice;
    
    abstract protected function getUser();
    
    public function __construct($u, $backoffice)
    {
        $this->user = $u;
        $this->_backoffice = $backoffice;
    }

    protected function _authenticateCreateAuthResult()
    {
        return new Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity']
        );
    }
    
    public function authenticate()
    {
        $this->_authenticateResultInfo = array('code' => Result::FAILURE);
        
        $user = $this->getUser();
        if (!$user || !$user->isEnabled() || (!$user->allowBackOffice() && $this->_backoffice)) {
            $this->_authenticateResultInfo['code'] = Result::FAILURE_IDENTITY_NOT_FOUND;
            return $this->_authenticateCreateAuthResult();
        }
        
        $this->_authenticateResultInfo['code'] = Result::SUCCESS;
        $this->_authenticateResultInfo['identity'] = array(
            'uniq'     => $user->authorize(true),
            'user_id'  => $user->id
        );
        
        if (Application::getInstance()->getSession()) {
            Application::getInstance()->getSession()->getManager()->rememberMe(REMEMBER_ME_SECONDS);
        }   	
        return $this->_authenticateCreateAuthResult();
    }
}