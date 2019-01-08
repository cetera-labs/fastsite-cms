---
layout: default
title: Модуль «Баннерная система»
nav_order: 6
parent: Плагины (модули) Cetera CMS
grand_parent: Руководство разработчика
---

# Модуль «Баннерная система»

## Виджет WidgetBanner

В модуле «Баннерная система» вставляет баннер из указанной группы.

	Пример вызова
	\Cetera\Application::getInstance()->getWidget('WidgetBanner', array(
	    'group' => 'right',
	))->display();

## Описание параметров

Параметр | Описание
---|---
**group**|Группа, из которой брать баннер