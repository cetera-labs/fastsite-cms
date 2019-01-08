---
layout: default
title: Модуль «Конструктор форм»
nav_order: 4
parent: Плагины (модули) Cetera CMS
grand_parent: Руководство разработчика
---

# Модуль «Конструктор форм»

В модуле «Конструктор форм» выводит форму, созданную в конструкторе.

## Пример вызова

	\Cetera\Application::getInstance()->getWidget('forms', array(
	    'form' => 1,
	))->display();

## Описание параметров

Параметр | Описание
---|---
form|ID формы