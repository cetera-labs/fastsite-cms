---
layout: default
title: MenuUser
nav_order: 16
parent: Виджеты
grand_parent: Руководство разработчика
---

# MenuUser

Выводит меню, созданное в конструкторе меню в интерфейсе CMS

## Пример вызова в Twig

	{% raw %}{% widget 'MenuUser' with { 'alias': 'main' } %}{% endraw %}

## Пример вызова в PHP

	\Cetera\Application::getInstance()->getWidget('MenuUser', [
	    'alias' => 'main',
	])->display();

## Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**alias**|alias меню
**menu**|ID меню