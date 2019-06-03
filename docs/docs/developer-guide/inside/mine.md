---
layout: default
title: Использование своих классов для работы с материалами
nav_order: 10
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Использование своих классов для работы с материалами

В CeteraCMS v3.18.0 и выше.

Базовым классом, инкапсулирующим работу со всеми материалами в CeteraCMS, является \Cetera\Material. 
Если вы хотите расширить стандартную функциональность, создайте новый класс, расширяющий \Cetera\MaterialUser :

	class Article extends \Cetera\MaterialUser {
	 
        public static function getTypeId() {
            // здесь указываем ID тима материалов для которого создан класс
            return 1;
        }        
     
	    [ваши методы]
	 
	}
 

И поместите его в <каталог темы>/classes/Article.php

Затем в bootstrap.php нужно зарегистрировать этот класс:

	// Для работы со стандартными материалами будем использовать свой класс
	\Cetera\ObjectDefinition::registerClass('Article');