---
layout: default
title: Файловая структура Cetera CMS
nav_order: 2
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Файловая структура Cetera CMS

&lt;DOCUMENT_ROOT&gt;
+---.prefs           &nbsp;—&nbsp;основные настройки CMS
+---.htaccess        &nbsp;—&nbsp;настройки прав доступа и url rewrite
+---robots.txt    
+---cms/                    &nbsp;—&nbsp;скрипты Cetera CMS
+---library/                &nbsp;—&nbsp;сторонние библиотеки, используемые Cetera CMS
+---uploads/                &nbsp;—&nbsp;каталог по умолчанию для файлов, загружаемых пользователями через интерфейс CMS
+---plugins/                &nbsp;—&nbsp;каталог с дополнительными модулями
+---themes/                 &nbsp;—&nbsp;каталог с темами (редакциями)
+---.cache/                 &nbsp;—&nbsp;хранилище кэша
+---.templates/             &nbsp;—&nbsp;скрипты сайта
|   +---design/             &nbsp;—&nbsp;верстка 
|   |   +---layout.twig     &nbsp;—&nbsp;главный шаблон
|   |   +---page_index.twig &nbsp;—&nbsp;шаблон главной страницы сайта
|   |   +---page_404.twig   &nbsp;—&nbsp;шаблон страницы 404
|   +---classes/            &nbsp;—&nbsp;пользовательские классы
|   +---widgets/            &nbsp;—&nbsp;шаблоны виджетов
|   +---default.php         &nbsp;—&nbsp;контроллер по умолчанию
|   +---bootstrap.php       &nbsp;—&nbsp;скрипт запускаемый перед выполнением любого контроллера
+---css/                    &nbsp;—&nbsp;стили
+---js/                     &nbsp;—&nbsp;javascripts