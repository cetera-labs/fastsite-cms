---
layout: default
title: Расширение встроенных в FastsiteCMS классов
nav_order: 11
parent: Fastsite CMS изнутри
grand_parent: Руководство разработчика
---

# Расширение встроенных в FastsiteCMS классов

В FastsiteCMS v3.19.2 и выше.

Допустим, требуется сделать функционал активации для пользователей. Создаем класс, расширяющий Cetera\User:

class MyUser extends \Cetera\User {
 
    public function activate( $code )
    {
         …
    }
 
}
 

И помещаем его в .templates/classes/MyUser.php или в themes/\<ТЕМА>/classes/MyUser.php, если вы используете тему.

Затем необходимо сообщить системе, чтобы она использовала новый класс для пользователей.

Для этого в bootstrap.php помещаем такой код:

	<?php
	\Cetera\User::extend( 'MyUser');
 
Теперь пользователи системы будут экземплярами класса MyUser.