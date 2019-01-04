---
layout: default
title: Container
nav_order: 20
parent: Виджеты
grand_parent: Руководство разработчика
---

# Container

Контейнер виджетов — это виджет, который состоит из последовательности других виджетов, набор и порядок следования которых формируются в визуальном конструкторе в интерфейсе CMS.

## Пример вызова

	\Cetera\Application::getInstance()->getWidget('Container', [array](http://php.net/array)(
	    'alias' => 'sidebar',
	))->display();

## Описание параметров

Параметр | Описание
**template**|Шаблон вывода
**alias**|alias контейнера