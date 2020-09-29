---
layout: default
title: Модуль «API»
nav_order: 12
parent: Плагины (модули) Fastsite CMS
grand_parent: Руководство разработчика
---

# Модуль «API»

Реализация REST API для взаимодействия с Fastsite CMS.

В случае успеха методы возвращают ответ вида:

	{
	    success: 1,
	    result: {
	        TYPE: 'OK',
	        MESSAGE: 'ОпUциональное сообщение об успехе'
	    },
	    data: {
	        …
	    }
	}

где data — объект с данными, специфичными для метода.

Объект data может и вовсе отсутствовать.

В случае ошибки методы возвращают ответ вида:

	{
	    success: 0,
	    result: {
	        TYPE: 'ERROR',
	        MESSAGE: 'Сообщение об ошибке'
	    }
	}

Для методов, требующих авторизацию пользователя, информация передается через заголовки:

	User-Id: <USER_ID>

	User-Hash: <USER_Hash>

## Авторизация пользователя

	POST /api/v1/personal/auth/

Параметры:

login — обязательное

password — обязательное

В случае успеха в объекте data возвращается информация:

	{
	    user_id: <USER_ID>, 
	    user_hash: '<USER_HASH>' 
	    user_profile: {
				"login": "login",
				"name: "null",
				"email": "user@cetera.ru",
	            … все поля пользователя, определенные в настройках CMS
	    }
	}

## Обновление токена авторизации

	GET /api/v1/personal/auth_refresh/

В случае успеха в объекте data возвращается информация:

	{
	    user_id: <USER_ID>, 
	    user_hash: '<USER_HASH>'
	}
 

## Регистрация пользователя

	POST /api/v1/personal/register/

Параметры:

login — обязательное

password — обязательное

confirm_password — обязательное

email — обязательное

Также в качестве параметров могут быть переданы все поля, определенные для пользователя в настройках CMS

В случае успеха в объекте data возвращается информация:

	{
	    user_id: <USER_ID>, 
	    user_hash: '<USER_HASH>',
	    user_profile: { 
	        "login": "login", 
	        "name: "null", 
	        "email": "user@cetera.ru", 
	        … все поля пользователя, определенные в настройках CMS }
	}

## Восстановление пароля пользователя на почту

	POST /api/v1/personal/recover/

Параметры:

login

email