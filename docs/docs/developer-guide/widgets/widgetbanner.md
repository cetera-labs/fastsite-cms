---
layout: default
title: WidgetBanner
nav_order: 10
parent: Виджеты
grand_parent: Руководство разработчика
---

# WidgetBanner

Виджет WidgetBanner вставляет баннер из указанной группы

## Пример вызова Twig

	{% raw %}{% widget 'WidgetBanner' with { group: 'right' } %}{% endraw %}

## Пример вызова

	\Cetera\Application::getInstance()->getWidget('WidgetBanner', array(
	    'group' => 'right',
	))->display();

## Описание параметров

Параметр | Описание
---|---
**group**|Группа, из которой брать баннер