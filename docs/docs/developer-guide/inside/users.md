---
layout: default
title: Работа с пользователями
nav_order: 7
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Работа с пользователями

Пользователи представлены классом [\Cetera\User](https://cetera.ru/cetera_cms/doc/api/Cetera/User.html). Все пользовательские поля материалов доступны как свойства объекта. Для получения материалов, как правило, используются соответствующие методы разделов. Смотри [Работа с разделами]({{site.baseurl}}/docs/developer-guide/inside/sections.html) и [Итераторы]({{site.baseurl}}/docs/developer-guide/inside/iterator.html).

## Методы \Cetera\User

Метод | Описание
---|---
**enum()**|Возвращает итератор с пользователями. См. [Итераторы]({{site.baseurl}}/docs/developer-guide/inside/iterator.html)
**getById($id)**|Статический. Возвращает пользователя с данным ID.
**getByLogin($username)**|Статический. Возвращает пользователя по его логину.
**getByLogin($email)**|Статический. Возвращает пользователя по его e-mail.
**getExternal($network, $id)**|Статический. Возвращает пользователя по ID внешней сети. $network — USER_FACEBOOK, USER_TWITTER, USER_VK, USER_LJ, USER_GOOGLE, USER_ODNOKLASSNIKI, USER_OPENID
**create($type)**|Статический. Создает пользователя.
**delete()**|Удаляет пользователя.
**save()**|Сохраняет пользователя в БД.
**setFields($fields)**|Обновляет поля.
**addExternal($network, $id)**|Привязывает пользователя к аккаунту внешней сети.
**getExternalId($network)**|Возвращает внешний id пользователя, если он привязан к внешней сети.
**getExternals()**|Возвращает id всех аккаунтов внешний сетей, к которым привязан пользователь.
**allowBackOffice()**|Имеет ли право пользователь на доступ в back office.
**isAdmin()**|Имеет ли пользователь привилегии администратора.
**isDisabled()**|Пользователь заблокирован.
**isInGroup($group_id)**|Является ли пользователь членом группы.
**getGroups()**|Список групп, в которых состоит пользователь.
**logout()**|Снимает авторизацию пользователя.

## Авторизация

Авторизация в CeteraCMS построена на базе класса [\Zend_Auth](http://framework.zend.com/manual/1.11/ru/zend.auth.introduction.html) используя адаптер \Cetera\UserAuthAdapter, реализующий интерфейс \Zend_Auth_Adapter_Interface

Пример авторизации пользователя:

	$result = \Cetera\Application::getInstance()->getAuth()->authenticate(new MyUserAuthAdapter(array(
	    'login'    => [ПОЛЬЗОВАТЕЛЬ],
	    'pass'     => [ПАРОЛЬ],
	    'remember' => [ДОЛГАЯ АВТОРИЗАЦИЯ]
	))); 
 
	switch ($result->getCode()) {
	    case \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
	        // вывести ошибку 'Пользователь не найден'     
	        break;
	    case \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
	        // вывести ошибку 'Неверный пароль'
	        break;
	    case \Zend_Auth_Result::SUCCESS:
	        // действия при успешной авторизации
	        break;
	}
 

Для получения авторизованного в настоящий момент пользователя служит метод \Cetera\Application::getUser()

Пример, ограничить доступ к странице только для зарегистрированных пользователей:

	$user = \Cetera\Application::getInstance()->getUser();
	if (!$user) die('Страница только для зарегистрированных пользователей');
	echo 'Добро пожаловать, '.$user->name;
 

## Авторизация через соцсети

Для авторизации через соцсети используется сервис [http://ulogin.ru/](http://ulogin.ru/)

Виджет авторизации делаем в конструкторе [http://ulogin.ru/constructor.php](http://ulogin.ru/constructor.php) и вставляем на сайт

Код авторизации удобно разместить в файле bootstrap.php, тогда в качестве обратной ссылки можно указать любую страницу сайта:

	if (isset($_POST['token'])) {
	    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
	    $u = json_decode($s, true);
	    if ($u && $u['uid']) \Cetera\Application::getInstance()->getAuth()->authenticate(new \Cetera\UserAuthAdapterULogin($u));
	}
 
## Создание пользователя (регистрация)

	$user = \Cetera\User::create();
	$user->setFields(array(
	    'login'    => 'donald',
	    'name'     => 'Donald J. Trump',
	    'email'    => 'trump@whitehouse@gov',
	    'password' => md5('HillarySucks'),
	));
	$user->save();