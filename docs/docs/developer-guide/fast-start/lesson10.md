---
layout: default
title: Урок №10. Добавляем фильтр в ленту
nav_order: 10
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок №10. Добавляем фильтр в ленту

В CeteraCMS начиная с версии 3.37.0 появился класс \Cetera\Filter с помощью которого легко реализовать возможность фильтрации в ленте материалов.

В этом уроке я покажу как добавить фильтр  по категориям в ленты новостей и статей на примере сайта, построенного на теме «СМИ».

Список материалов для ленты формируется в контроллере controller_common.php:

```
$page = (int)$_GET['page']; if (!$page) $page = 1;    
    
$twig->display($material_tpl, array(
        'list' => $c->getMaterials()->orderBy('dat','DESC')->setItemCountPerPage(10)->setCurrentPageNumber($page)->subFolders(),
)); 
```

Для добавления фильтра изменим код:

```
$page = (int)$_GET['page']; if (!$page) $page = 1;    

// итератор - лента материалов    
$list = $c->getMaterials()->orderBy('dat','DESC')->setItemCountPerPage(10)->setCurrentPageNumber($page)->subFolders();

// создадим фильтр
// обязательные параметры - имя фильтра и итератор, который будет фильтраваться
$filter = new \Cetera\Filter('filter', $list);
// добавим в фильтр поле по которому будет осуществляться фильтрация
// можно добавить несколько полей
// первый параметр - имя поля, второй - внешний вид фильтра
$filter->addField('category', \Cetera\Filter::TYPE_CHECKBOX);
// применить фильтр
$filter->apply();
    
$twig->display($material_tpl, array(
    'list' => $list, // передадим в twig отфильтрованную ленту
    'filter' => $filter, // передадим в twig фильтр
));  
```

Для того, чтобы показать фильтр на странице сайта используется виджет Filter. Добавим фильтр в шаблон page_material.twig в правую колонку:

```
{% raw %}{% if filter is defined %}
    {% widget 'Filter' with { filter: filter } %}
{% endif %}{% endraw %}
```

Результат добавления фильтра:

![Пример]({{site.baseurl}}/images/Screenshot_10.png)