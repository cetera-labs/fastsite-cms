---
layout: default
title: User.Register
nav_order: 3
parent: Виджеты
grand_parent: Руководство разработчика
---

# User.Register

Виджет показывает форму для регистрации нового пользователя.

## Пример вызова в Twig

	{% raw %}{% widget 'User.Register' with { recaptcha: 1 } %}{% endraw %}
 

## Описание параметров

Параметр | Описание
---|---
template|Шаблон вывода
unique_email|`[TRUE|FALSE]` Для каждого пользователя должен быть уникальный e-mail. По умолчанию **FALSE**
email_is_login|`[TRUE|FALSE]` Использовать e-mail как имя пользователя. По умолчанию **FALSE**
success_auth|`[TRUE|FALSE]` Автоматическая автроризация после успешной регистрации. По умолчанию **TRUE**
success_redirect|URL для перенаправления после успешной регистрации. По умолчанию "/" - **главная страница**
recaptcha|`[TRUE|FALSE]` Показать Google Recaptcha. По умолчанию **FALSE**
recaptcha_site_key|`ключ для recaptcha`
recaptcha_secret_key|`ключ для recaptcha`