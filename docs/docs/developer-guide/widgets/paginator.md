---
layout: default
title: Paginator
nav_order: 15
parent: Виджеты
grand_parent: Руководство разработчика
---

# Paginator

## Пример вызова

	\Cetera\Application::getInstance()->getWidget('Paginator', array(
	    'iterator' => \Cetera\Application::getInstance()->getCatalog()->getMaterials(),
	))->display();

## Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**iterator**|`\Cetera\Iterator\DbObject` Итератор объектов, для которых делать навигацию
**url**|Шаблон ссылок на страницы. {page} будет заменен на № страницы. По умолчанию `?page={page}`

## Встроенные шаблоны

Название | Описание
---|---
**default.twig**|[http://foundation.zurb.com/sites/docs/pagination.html](http://foundation.zurb.com/sites/docs/pagination.html). Шаблон по умолчанию