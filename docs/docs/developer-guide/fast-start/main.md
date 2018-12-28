---
layout: default
title: Урок№4. Формируем главную страницу сайта
nav_order: 4
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок№4. Формируем главную страницу сайта

Давайте займемся главной страницей нашего сайта. Итак, контроллер, реализующий логику работы главной страницы находится в www/.templates/default.php, шаблон страницы — в www/.templates/design/page_index.twig

Я хочу разместить на главной странице сайта 5 последних новостей компании, а также 3 наиболее интересных проекта из раздела «Портфолио».

## Новости

Начнем с новостей.

Новости публикуются в разделе верхнего уровня, имеющем alias «news» и чтобы получить этот раздел, используем метод Catalog::getChildByAlias():

	$newsCat = \Cetera\Application::getInstance()->getServer()->getChildByAlias('news');
 

Чтобы получить список новостей из раздела, используем метод \Cetera\Catalog::getMaterials(), который возвращает материалы раздела — объект \Cetera\Iterator\Material, к которому применим модификаторы для выбора полей, сортировки и ограничения количества записей:

	$newsList = $newsCat->getMaterials()
	                    ->select('dat')
	                    ->orderBy('dat', 'DESC')
	                    ->setItemCountPerPage(5);
 

Список методов-модификаторов \Cetera\Iterator\Material:

* \Cetera\Iterator\Material::subFolders() — включить в список материалы из всех подразделов (если они того же типа)
* \Cetera\Iterator\Material::select($field1, $field2) — включить в выборке дополнительные поля материалов (по умолчанию выбираются только id,name,alias
* \Cetera\Iterator\Material::setItemCountPerPage($itemCountPerPage) — задать кол-во материалов в выборке
* \Cetera\Iterator\Material::setCurrentPageNumber($pageNumber) — выбрать страницу (при заданном \Cetera\Iterator\Material::setItemCountPerPage())
* \Cetera\Iterator\Material::where($where, $combination = 'AND') — задать дополнительное условие для выбора материалов
* \Cetera\Iterator\Material::orderBy($sort, $order = null, $add = true) — задать поле для сортировки, порядок сортировки, если $add = true, то добавить к предыдущему полю для сортировки
* \Cetera\Iterator\Material::groupBy($groupBy, $add = true) — группировать результат по полю
Добавим изученный код в www/.templates/index.php:

```
try {
    $newsList = $s->getChildByAlias('news')->getMaterials()
                                           ->select('dat')
                                           ->orderBy('dat', 'DESC')
                                           ->setItemCountPerPage(5);
} catch (Exception $e) {
    $newsList = null;
}                    
 
$twig->display('index.html', array(
    'news' => $newsList
));
``` 

Я добавил обработчик исключений на случай, если раздел с новостями по тем или иным причинам не будет найден. Тогда блок последних новостей просто не будет показан.

Шаблон главной страницы www/.templates/design/index.html:
```
{% raw %}{% extends "layout.html" %}
 
{% block title %}Главная{% endblock %}
 
{% block main %}
    {% if news|length > 0 %}
    {# Последние новости #}
    <div class="row">
        <div class="large-12 columns">
            <h2>Последние новости</h2>
 
            {% for m in news %}
                <div class="row">
                    <div class="large-2 columns small-3"><img src="{{ m.pic }}" width="140" /></div>
                    <div class="large-10 columns">
                        <h3><a href="{{ m.url }}">{{ m.name }}</a></h3>  
                        <h6>{{ m.dat|date("d.m.Y") }}</h6>              
                        {{ m.short }}
                        <p><a href="{{ m.url }}">Подробнее</a></p>
                    </div>
                </div>
                {% if not loop.last %}<hr/>{% endif %}            
             {% endfor %}
 
        </div>
    </div>    
    {% endif %}
{% endblock %}{% endraw %} 
```

Что получилось:

![Пример]({{site.baseurl}}/images/pic11.png)

## Портфолио

Чтобы проект из раздела «Портфолио» размещался на главной странице, необходимо, чтобы у материалов данного раздела был соответствующий признак. Добавим дополнительное поле к материалам. Откроем панель «Сервис > Типы материалов». Затем откроем панель редактирования materials «Материалы» и добавим к материалам новое логическое поле «Показывать на главной»(alias=show_index):

![Пример]({{site.baseurl}}/images/pic12.png)

Заметьте, что я отметил новое поле как скрытое, таким образом, по умолчанию, оно не будет показываться при редактировании материалов. Теперь откроем свойства раздела «Портфолио» и затем кнопку «Настроить» в правом верхнем углу окна (рядом с кнопкой закрытия окна):

![Пример]({{site.baseurl}}/images/pic13.png)

Снимаем галку с «наследовать настройки родительского раздела» и отмечаем «Показывать» напротив поля «Показывать на главной». Сохраняем. Теперь поле «Показывать на главной» видно при редактировании материалов только из раздела «Портфолио». Если понадобится показать это поле у материалов из другого раздела, следует аналогично настроить раздел.

Добавим код получения материалов портфолио в контроллер index.php:

	try {
	    $newsList = $s->getChildByAlias('news')->getMaterials()
	                                           ->select('dat')
	                                           ->orderBy('dat', 'DESC')
	                                           ->setItemCountPerPage(5);
	} catch (Exception $e) {
	    $newsList = null;
	}   
	 
	try {
	    $portfolioList = $s->getChildByAlias('portfolio')->getMaterials()
	                                                     ->select('pic')
	                                                     ->where('show_index>0')
	                                                     ->orderBy('RAND()');
	} catch (Exception $e) {
	    $portfolioList = null;
	}                   
	 
	$twig->display('index.html', array(
	    'news'      => $newsList,
	    'portfolio' => $portfolioList
	));
 

И отобразим его в шаблоне index.html:

```
{% raw %}{% extends "layout.html" %}
 
{% block title %}Главная{% endblock %}
 
{% block main %}
 
    {% if portfolio|length > 0 %}
    {# Портфолио #}
    <div class="row panel">
        <div class="large-12 columns">
 
            <ul data-orbit>
            {% for m in portfolio %}
                <li>
                    <div class="row">
                        <div class="large-1 columns">&nbsp;</div>
                        <div class="large-7 columns">
                            <h2><a href="{{ m.url }}">{{ m.name }}</a></h2>              
                            {{ m.short }}
                            <p><a href="{{ m.url }}">Подробнее</a></p>
                        </div>
                        <div class="large-4 columns small-3"><img src="{{ m.pic }}" width="280" /></div> 
</div>
                </li>         
             {% endfor %}            
            </ul>
 
        </div>
    </div>    
    {% endif %}
 
    {% if news|length > 0 %}
    {# Последние новости #}
    <div class="row">
        <div class="large-12 columns">
            <h2>Последние новости</h2>
 
            {% for m in news %}
                <div class="row">
                    <div class="large-2 columns small-3"><img src="{{ m.pic }}" width="140" /></div>
                    <div class="large-10 columns">
                        <h3><a href="{{ m.url }}">{{ m.name }}</a></h3>  
                        <h6>{{ m.dat|date("d.m.Y") }}</h6>              
                        {{ m.short }}
                        <p><a href="{{ m.url }}">Подробнее</a></p>
                    </div>
                </div>
                {% if not loop.last %}<hr/>{% endif %}            
             {% endfor %}
 
        </div>
    </div>    
    {% endif %}
 
{% endblock %}{% endraw %} 
```

Наслаждаемся:

![Пример]({{site.baseurl}}/images/pic14.png)