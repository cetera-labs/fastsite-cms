---
layout: default
title: Добавление виджета на сайт
nav_order: 1
parent: Виджеты
grand_parent: Руководство разработчика
---

# Добавление виджета на сайт

Технология виджетов была придумана для облегчения и упрощения создания сайтов.

Виджет — это блок, который реализует определенную законченную функциональность.

Вся нижеследующая информация актуальна для **FastsiteCMS v3.21.0** и выше

## В PHP:

Все виджеты наследуют базовый класс *\Cetera\Widget\Widget*

Чтобы получить виджет, следует пользоваться методом *\Cetera\Application::getWidget(String \<виджет>*, Array \<*параметры виджета*>)

Далее, для отображения виджета используются методы *\Cetera\Widget\Widget::getHtml()* или *\Cetera\Widget\Widget::display()*

### Пример 1. Вывод списка материалов текущего раздела с помощью виджета List

	// получим объект Application
	$a = \Cetera\Application::getInstance();
	 
	// получаем требуемый виджет с заданными параметрами
	$w = $a->getWidget(
	    // название виджета
	    'List', 
	    // параметры виджета
	    array(
	        // раздел — текущий
	        'catalog' => $a->getCatalog(),
	    ),
	);
	 
	// выводим виджет
	$w->display();

### Пример 2. То же самое, но более кратко, для боевого применения (подразумевается, что $a определена в bootstrap.php)

	$a->getWidget('List',array('catalog' => $a->getCatalog()))->display();
 

## В Twig

Можно организовать показ виджета прямо в twig-шаблоне используя тег {% raw %}*{% widget %}*{% endraw %} в шаблонах:

	{% raw %}{% widget 'List' with { catalog: application.getCatalog() } %}{% endraw %}
 

## Шаблоны виджетов

Очевидно, что не всегда html-код, генерируемый виджетом, будет отвечать потребностям конкретного сайта. Поэтому большинство виджетов используют шаблоны, которые отвечают за то, какой html будет генерировать виджет. У разработчика есть возможность изменить этот шаблон под свои нужды. Для того, чтобы использовать свой шаблон вывода, нужно передать в параметре template имя файла с требуемым шаблоном. Шаблоны по умолчанию виджетов в составе ядра cms расположены в каталоге *vendor/cetera-labs/cetera-cms/cms/twig_templates/widgets*