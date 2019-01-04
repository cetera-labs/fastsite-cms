---
layout: default
title: Menu
nav_order: 17
parent: Виджеты
grand_parent: Руководство разработчика
---

# Menu

Выводит подразделы и материалы из указанного раздела

## Пример вызова

	\Cetera\Application::getInstance()->getWidget('Menu', array(
	    'catalog' => \Cetera\Application::getServer(),
	    'depth'   => 1,
	))->display();

## Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**catalog**|Раздел, из которого выводить данные. 0 — для текущего раздела. По умолчанию 0
**depth**|Рекурсивно обрабатывать подразделы на заданную глубину. 0 — вывод всего дерева. По умолчанию **1**
**css_class**|CSS основного элемента. По умолчанию menu
**css_class_submenu**|CSS для вложеных элементов. По умолчанию menu nested
**expand_active**|`true|false` Разворачивать подразделы только ведущие к активному разделу. По умолчанию **false**
**materials_show**|`true|false` Показывать материалы. По умолчанию **false**
**materials_hide_index**|`true|false` Скрывать материалы с алиас='index'. По умолчанию **true**