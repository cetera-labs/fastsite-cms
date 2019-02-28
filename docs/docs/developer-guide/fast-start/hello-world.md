---
layout: default
title: Урок №1. Hello World
nav_order: 1
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок №1. Hello World

В Cetera CMS все скрипты фронтофиса располагаются в каталоге www/themes/<название темы>. Название темы "по умолчанию" - default.

Создадим файл www/themes/default/index.php, который будет отвечать за главную страницу сайта:

`echo '<h1>Hello world!!!</h1>';`

В свойствах сервера укажем, что нужно использовать index.php при обращении к сайту:

![Свойства раздела]({{site.baseurl}}/images/pic1.png)

Пробуем открыть сайт:

![Hello World]({{site.baseurl}}/images/pic3.png)

Ура! Мы только что создали первый сайт на Cetera CMS.
