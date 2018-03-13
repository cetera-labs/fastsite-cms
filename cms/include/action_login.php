<?php
namespace Cetera;
include('common.php');
$translator = $application->getTranslator();  
$application->connectDb();
$application->initSession();
    
if (isset($_POST['recover']))
{
        $user = User::getByLogin($_POST['recover']);
        
        if (!$user || !$user->isEnabled() || !$user->allowBackOffice()) {
            echo $translator->_('Пользователь не найден');
            exit;
        }
		
		try {
			$user->recoverPassword();	
			echo $translator->_('Новый пароль отправлен');        		
		}
		catch (\Exception $e) {
			echo $e->getMessage();
		}		
                             
        exit;
}

if (isset($_POST['login']))
{

    $res = array(
        'success' => false,
        'errors'  => array()
    );
    
    if ($_POST['locale']) {
        $application->setLocale($_POST['locale'], true);
        $res['locale'] = $_POST['locale'];
    }
  
    $result = $application->getAuth()->authenticate(new UserAuthAdapter($_POST, true)); 
    switch ($result->getCode())
	{
        case \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
            $res['errors']['login'] = $translator->_('Пользователь не найден');
            Event::trigger(EVENT_CORE_BO_LOGIN_FAIL, ['message' => 'Login: '.$_POST['login'].', Pass: '.$_POST['pass'].', IP: '.$_SERVER['REMOTE_ADDR']]);
            break;
    
        case \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
            $res['errors']['pass'] = $translator->_('Неправильный пароль');
            Event::trigger(EVENT_CORE_BO_LOGIN_FAIL, ['message' => 'Login: '.$_POST['login'].', Pass: '.$_POST['pass'].', IP: '.$_SERVER['REMOTE_ADDR']]);
            break;
    
        case \Zend_Auth_Result::SUCCESS:
            $res['success'] = true;
            $res['user'] = $application->getUser()->boArray();
            Event::trigger(EVENT_CORE_BO_LOGIN_OK, ['message' => 'IP: '.$_SERVER['REMOTE_ADDR']]);
            break;
    }
	
    echo json_encode($res);
    exit;
}