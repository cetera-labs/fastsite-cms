---
layout: default
title: Forms
nav_order: 9.1
parent: Виджеты
grand_parent: Руководство разработчика
---

# Forms

В модуле «Конструктор форм» выводит форму, созданную в конструкторе.

## Пример вызова в PHP

	::getInstance()->getWidget('forms', array(
	    'form' => 1,
	))->display();

## Пример вызова в Twig

	{% raw %}{% widget 'forms' with { form: 1 } %}{% endraw %}

## Описание параметров

Параметр | Описание
---|---
form|ID формы