---
layout: default
title: Использование своих классов для работы с материалами
nav_order: 9
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Использование своих классов для работы с материалами

В CeteraCMS v3.18.0 и выше.

Классом, инкапсулирующим работу со всеми материалами в CeteraCMS, является \Cetera\Material. Если вы хотите расширить стандартную функциональность, создайте новый класс, расширяющий \Cetera\Material :

	class Article extends \Cetera\Material {
	 
	    [ваши методы]
	 
	}
 

И поместите его в .templates/classes/Article.php

Затем в bootstrap.php нужно объявить для какого типа материалов использовать этот класс:

	// Для работы со стандартными материалами будем использовать свой класс
	\Cetera\ObjectDefinition::registerClass(1, 'Article');