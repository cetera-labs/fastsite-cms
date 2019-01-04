---
layout: default
title: Breadcrumbs
nav_order: 14
parent: Виджеты
grand_parent: Руководство разработчика
---

# Breadcrumbs

Отображает цепочку ссылок на разделы от корня сайта до заданного.

## Пример вызова в PHP

	{% raw %}\Cetera\Application::getInstance()->getWidget('Breadcrumbs')->display();{% endraw %}
 

## Пример вызова в Twig

	{% raw %}{% widget 'Breadcrumbs' with { root: 0 } %}{% endraw %}
 

## Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**catalog**|До которого раздела делать навигацию. По умолчанию — текущий раздел
**root**|Названик корневого раздела. По умолчанию — «Главная». 0, null, false — использовать название сервера

## Встроенные шаблоны

Название | Описание
---|---
**default.twig**|[http://foundation.zurb.com/sites/docs/breadcrumbs.html](http://foundation.zurb.com/sites/docs/breadcrumbs.html). Шаблон по умолчанию
**path.twig**|Список разделов, разделенных «/»