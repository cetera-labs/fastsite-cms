---
layout: default
title: Работа с материалами
nav_order: 6
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Работа с материалами

Материалы в CeteraCMS группируются по типам материалов. Каждый тип материалов характеризуется своим набором полей и таблицей БД, в которой хранятся материалы данного типа. Тип материалов описывается классом [\Cetera\ObjectDefinition](https://cetera.ru/cetera_cms/doc/api/Cetera/ObjectDefinition.html). Разделы могут содержать материалы только одного типа, указанного в свойствах раздела.

Материалы представлены классом [\Cetera\Material](https://cetera.ru/cetera_cms/doc/api/Cetera/Material.html). Все пользовательские поля материалов доступны как свойства объекта. Для получения материалов, как правило, используются соответствующих методы разделов. Смотри [Работа с разделами]({{site.baseurl}}/docs/developer-guide/inside/sections.html) и [Итераторы]({{site.baseurl}}/docs/developer-guide/inside/iterator.html).

## Методы \Cetera\Material

Метод | Описание
---|---
getById($id, $type)|Статический. Возвращает материал с данным ID. $type — id типа материала или объект \Cetera\ObjectDefinition
factory($type)|Статический. Создает материал. $type — id типа материала или объект \Cetera\ObjectDefinition
getCatalog()|Возвращает раздел, которому принадлежит материал или false, если материал не принадлежит разделу
getUrl()|Возвращает абсолютную ссылку на материал
delete()|Удаляет материал
copy($dst)|Копирует материал в указанный раздел
save()|Сохраняет материал в БД
setFields($fields)|Обновляет поля материала