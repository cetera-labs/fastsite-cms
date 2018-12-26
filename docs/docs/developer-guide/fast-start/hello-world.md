---
layout: default
title: Hello, World!
nav_order: 1
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Hello World

В Cetera CMS все скрипты фронтофиса располагаются в каталоге www/.templates. Допускается создание подкаталогов.

Создадим файл index.php, который будет отвечать за главную страницу сайта:

`echo '<h1>Hello world!!!</h1>';`

В свойствах сервера укажем, что нужно использовать index.php при обращении к сайту:

![Свойства раздела](site.baseurl/images/pic1.png)
![Свойства раздела]({{site.baseurl}}/images/pic1.png)

site.baseurl
