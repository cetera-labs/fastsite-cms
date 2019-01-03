---
layout: default
title: Итераторы
nav_order: 7
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Итераторы

Итератор (от англ. iterator ― перечислитель) — интерфейс, предоставляющий доступ к элементам коллекции (массива или контейнера) и навигацию по ним.

## Итератор объектов CeteraCMS

\Cetera\Iterator\DbObject — абстрактный базовый класс для коллекций объектов в CeteraCMS.

Итератор реализует интерфейсы [\Countable](http://php.net/manual/ru/class.countable.php), [\Iterator](http://php.net/manual/ru/class.iterator.php), [\ArrayAccess](http://php.net/manual/ru/class.arrayaccess.php).

### Методы \Cetera\Iterator\DbObject

Метод | Описание
---|---
count()|Количество объектов в коллекции
getCountAll()|Количество объектов, исключая ограничение, заданное методом **setItemCountPerPage**
getFirstIndex()|Порядковый номер первого элемента коллекции
getLastIndex()|Порядковый номер последнего элемента коллекции
getPageCount()|Количество получившихся страниц, если задано ограничение методом **setItemCountPerPage**
setOffset( $offset )|Исключить из коллекции первые $offset объектов
setItemCountPerPage($itemCountPerPage)|Ограничить количество объектов в коллекции
setCurrentPageNumber($pageNumber)|Выбрать страницу, если задано ограничение методом **setItemCountPerPage**
orderBy($sort, $order = null, $add = true)|сортировать объекты по полю
groupBy($groupBy, $add = true)|группировать объекты по полю
where($where, $combination = 'AND')|Дополнительное условие для отбора объектов.
**$where SQL**-условие для отбора объектов.
**$combination** - способ комбинации с предыдущим вызовом where
filterInclude($fieldName, $condition, $combination = 'AND')|
Позволяет включить в итератор объекты, используя связь определенную полями "группа материалов" или "ссылка на группу материалов". $fieldName - имя поля, $condition - SQL-условие для отбора в материалов в этом поле. $combination - способ добавления (см. where).
Например, в материалах есть поле "colors" - группа материалов из справочника цветов. Нам нужно получить в итераторе только те материалы, в которых присутствуют либо красный, либо желный цвет. Это фильтруется следующим способом.
$iterator->filterInclude('colors', 'name IN ("красный","желтый")')
filterExclude($fieldName, $condition, $combination = 'AND')|То же самое, что и filterInclude, только материалы, попадающие под условие исключаются из итератора.

Методы \Cetera\Iterator\DbObject доступны в любом из нижеописанных итераторов.

### Итератор материалов

[\Cetera\Iterator\Material](https://cetera.ru/cetera_cms/doc/api/Cetera/Iterator/Material.html) — предоставляет доступ к коллекции материалов.

### Способы получения материалов

**\Cetera\Catalog::getMaterials()** — возвращает итератор с коллекцией материалов данного раздела

**\Cetera\ObjectDefinition::getMaterials()** — возвращает итератор с коллекцией материалов данного типа

Пример, получить материалы текущего раздела:

	$list = \Cetera\Application::getInstance()->getCatalog()->getMaterials();
 

Пример, получить материалы опубликованные в 2016 году в хронологическом порядке:

	$list = $catalog->getMaterials()->where('DATE_FORMAT(dat,"%Y") = 2016');
 

Пример, получить неопубликованные материалы:

	// с помощью ->unpublished() добавляем в итератор неопубликованные материалы, 
	// а затем с помощью ->where('type=0') оставляем только неопубликованные материалы
	$list = $catalog->getMaterials()->unpublished()->where('type=0');
 
Пример, получить все материалы типа «Материал» (стандартный тип материалов, имеющий id=1):

	$list = \Cetera\ObjectDefinition::findById(1)->getMaterials();
 
### Методы \Cetera\Iterator\Material

Метод | Описание
---|---
subFolders()|Включать материалы из подразделов. Включаются материалы только того же типа, что и в начальном разделе
unpublished()|Включать также неопубликованные материалы

## Итератор разделов

[\Cetera\Iterator\Catalog](https://cetera.ru/cetera_cms/doc/api/Cetera/Iterator/Catalog/Catalog.html) — предоставляет доступ к коллекции разделов.

Итератор разделов возвращают следующие методы:

**\Cetera\Catalog::getChildren()** — возвращает коллекцию дочерних разделов

**\Cetera\Catalog::getPath()** — возвращает коллекцию разделов от корня сайта

Пример, вывести все разделы верхнего уровня, исключая скрытые:

	$menu = \Cetera\Application::getInstance()-getServer()->getChildren()->where('hidden<>1');
	foreach ($menu as $catalog)
	{
	    echo $catalog->name.'<br>';
	}
 
Пример, вывести хлебные крошки к текущему разделу:

	foreach (\Cetera\Application::getInstance()-getCatalog()->getPath() as $catalog)
	{
	    if ($catalog->isRoot()) continue;// пропускаем раздел root
	    echo $catalog->name.' / ';
	}
 
### Методы \Cetera\Iterator\Catalog

Метод | Описание
---|---
has($catalog)|Имеется ли в коллекции указанный раздел

## Итератор пользователей

\Cetera\Iterator\User — предоставляет доступ к коллекции пользователей.

Итератор разделов возвращают следующие методы:

**\Cetera\User::enum()** — возвращает коллекцию пользователей CeteraCMS

Пример, найти пользователей, родившихся 15 августа:

	// предполагается, что в поле birth_date хранится дата рождения
	$list = \Cetera\User::enum()->where('DATE_FORMAT(birth_date,"%d.%m") = "15.08"');