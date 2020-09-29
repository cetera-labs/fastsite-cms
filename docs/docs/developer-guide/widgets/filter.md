---
layout: default
title: Filter
nav_order: 5
parent: Виджеты
grand_parent: Руководство разработчика
---

# Filter

Виджет показывает форму, позволяюшую фильтровать материалы в итераторе. Является визуализатором \Cetera\Filter

*FastsiteCMS 3.37+*

## Пример вызова в Twig
	{% raw %}{% widget 'Filter' with { filter: filter } %}{% endraw %}
 

## Описание параметров

Параметр | Описание
---|---
template|Шаблон вывода
filter|Экземпляр \Cetera\Filter

## Встроенные шаблоны

Название | Описание
---|---
default.twig|Шаблон по умолчанию