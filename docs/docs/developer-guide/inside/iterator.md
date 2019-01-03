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

<table>
	<tbody>
		<tr>
			<th>Метод</th>
			<th>Описание</th>
		</tr>
		<tr>
			<td><strong>count()</strong></td>
			<td>Количество объектов в коллекции</td>
		</tr>
		<tr>
			<td><strong>getCountAll()</strong></td>
			<td>Количество объектов, исключая ограничение, заданное методом <strong>setItemCountPerPage</strong></td>
		</tr>
		<tr>
			<td><strong>getFirstIndex()</strong></td>
			<td>Порядковый номер первого элемента коллекции</td>
		</tr>
		<tr>
			<td><strong>getLastIndex()</strong></td>
			<td>Порядковый номер последнего элемента коллекции</td>
		</tr>
		<tr>
			<td><strong>getPageCount()</strong></td>
			<td>Количество получившихся страниц, если задано ограничение методом <strong>setItemCountPerPage</strong></td>
		</tr>
		<tr>
			<td><strong>setOffset( $offset )</strong></td>
			<td>Исключить из коллекции первые $offset объектов</td>
		</tr>
		<tr>
			<td><strong>setItemCountPerPage($itemCountPerPage)</strong></td>
			<td>Ограничить количество объектов в коллекции</td>
		</tr>
		<tr>
			<td><strong>setCurrentPageNumber($pageNumber)</strong></td>
			<td>Выбрать страницу, если задано ограничение методом <strong>setItemCountPerPage</strong></td>
		</tr>
		<tr>
			<td><strong>orderBy($sort, $order = null, $add = true)</strong></td>
			<td>сортировать объекты по полю</td>
		</tr>
		<tr>
			<td><strong>groupBy($groupBy, $add = true)</strong></td>
			<td>группировать объекты по полю</td>
		</tr>
		<tr>
			<td><strong>where($where, $combination = 'AND')</strong></td>
			<td>
			<p>Дополнительное условие для отбора объектов.<br>
			<strong>$where </strong>SQL-условие для отбора объектов.<br>
			<strong>$combination - </strong>способ комбинации с предыдущим вызовом <strong>where</strong></p>
			</td>
		</tr>
		<tr>
			<td><strong>filterInclude($fieldName, $condition, $combination = 'AND')</strong></td>
			<td>
			<p>Позволяет включить в итератор объекты, используя связь определенную полями "группа материалов" или "ссылка на группу материалов". <strong>$fieldName - </strong>имя поля, <strong>$condition - </strong>SQL-условие для отбора в материалов в этом поле. <strong>$combination - </strong>способ добавления (см.<strong> where</strong>).</p>
			<p>Например, в материалах есть поле "colors" - группа материалов из справочника цветов. Нам нужно получить в итераторе только те материалы, в которых присутствуют либо красный, либо желный цвет. Это фильтруется следующим способом.<br>
			$iterator-&gt;filterInclude('colors', 'name IN ("красный","желтый")')</p>
			</td>
		</tr>
		<tr>
			<td><strong>filterExclude($fieldName, $condition, $combination = 'AND')</strong></td>
			<td>То же самое, что и <strong>filterInclude,</strong> только материалы, попадающие под условие исключаются из итератора.</td>
		</tr>
	</tbody>
</table>

Методы \Cetera\Iterator\DbObject доступны в любом из нижеописанных итераторов.

### Итератор материалов

[\Cetera\Iterator\Material](https://cetera.ru/cetera_cms/doc/api/Cetera/Iterator/Material.html) — предоставляет доступ к коллекции материалов.

### Способы получения материалов

**\Cetera\Catalog::getMaterials()** — возвращает итератор с коллекцией материалов данного раздела

**\Cetera\ObjectDefinition::getMaterials()** — возвращает итератор с коллекцией материалов данного типа

Пример, получить материалы текущего раздела:

	{% raw %}$list = \Cetera\Application::getInstance()->getCatalog()->getMaterials();{% endraw %}
 

Пример, получить материалы опубликованные в 2016 году в хронологическом порядке:

	{% raw %}$list = $catalog->getMaterials()->where('DATE_FORMAT(dat,"%Y") = 2016');{% endraw %}
 

Пример, получить неопубликованные материалы:

	// с помощью ->unpublished() добавляем в итератор неопубликованные материалы, 
	// а затем с помощью ->where('type=0') оставляем только неопубликованные материалы
	{% raw %}$list = $catalog->getMaterials()->unpublished()->where('type=0');{% endraw %}
 
Пример, получить все материалы типа «Материал» (стандартный тип материалов, имеющий id=1):

	{% raw %}$list = \Cetera\ObjectDefinition::findById(1)->getMaterials();{% endraw %}
 
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

	{% raw %}$menu = \Cetera\Application::getInstance()-getServer()->getChildren()->where('hidden<>1');
	foreach ($menu as $catalog)
	{
	    echo $catalog->name.'<br>';
	}{% endraw %}
 
Пример, вывести хлебные крошки к текущему разделу:

	{% raw %}foreach (\Cetera\Application::getInstance()-getCatalog()->getPath() as $catalog)
	{
	    if ($catalog->isRoot()) continue;// пропускаем раздел root
	    echo $catalog->name.' / ';
	}{% endraw %}
 
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
	{% raw %}$list = \Cetera\User::enum()->where('DATE_FORMAT(birth_date,"%d.%m") = "15.08"');{% endraw %}