<?php
use Zend\Authentication\Result;

require_once('include/common.php');

$application->setFrontOffice(true);
$application->connectDb();
$application->initSession();

$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
$user = json_decode($s, true);

$s = $application->getSession();
$result = $application->getAuth()->authenticate(new \Cetera\UserAuthAdapterULogin($user, false));
if ($result->getCode() != Result::SUCCESS) 
    $s->login_error = $result->getCode();

if (isset($s->return_url)) {
    $return_url =  $s->return_url;
    unset($s->return_url);
} else $return_url = '/';

header("Location: " . $return_url);
exit();

//$user['network'] - соц. сеть, через которую авторизовался пользователь
//$user['identity'] - уникальная строка определяющая конкретного пользователя соц. сети
//$user['first_name'] - имя пользователя
//$user['last_name'] - фамилия пользователя