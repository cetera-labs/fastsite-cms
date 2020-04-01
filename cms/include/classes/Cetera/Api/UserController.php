<?php
namespace Cetera\Api;

use Zend\View\Model\JsonModel;
use Zend\Authentication\Result;
use Cetera\UserAuthAdapter;
use Cetera\Event;
use Cetera\Application;

class UserController extends AbstractController
{

    public function authAction()
    {
        if (!$this->checkParams(['login','password'])) {
            return $this->invalidParams();
        }
        
        $t = Application::getInstance()->getTranslator();
        
        $res = [
            'success' => false
        ];
        
        $result = Application::getInstance()->getAuth()->authenticate(new UserAuthAdapter($this->params)); 
        switch ($result->getCode())
        {
            case Result::FAILURE_IDENTITY_NOT_FOUND:
                $res['error']['message'] = $t->_('Пользователь не найден');
                Event::trigger(EVENT_CORE_BO_LOGIN_FAIL, ['message' => 'Login: '.$this->params['login'].', Pass: '.$this->params['pass'].', IP: '.$_SERVER['REMOTE_ADDR']]);
                break;
        
            case Result::FAILURE_CREDENTIAL_INVALID:
                $res['error']['message'] = $t->_('Неправильный пароль');
                Event::trigger(EVENT_CORE_BO_LOGIN_FAIL, ['message' => 'Login: '.$this->params['login'].', Pass: '.$this->params['pass'].', IP: '.$_SERVER['REMOTE_ADDR']]);
                break;
        
            case Result::SUCCESS:
                $res['success'] = true;
                $id = $result->getIdentity();
                $res['user_token'] = $id['user_id'].'|'.$id['uniq'];
                $res['user'] = Application::getInstance()->getUser()->boArray();
                Event::trigger(EVENT_CORE_BO_LOGIN_OK, ['message' => 'IP: '.$_SERVER['REMOTE_ADDR']]);
                break;
        }        
        
        return new JsonModel( $res );
    }  

}