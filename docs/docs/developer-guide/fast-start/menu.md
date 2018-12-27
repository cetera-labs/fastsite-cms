---
layout: default
title: Урок №6. Строим меню и путешествуем по разделам
nav_order: 5
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок №6. Строим меню и путешествуем по разделам

В этой главе мы добавим элементы навигации на наш сайт.

Главное меню
Для создания меню используем виджет Menu.

Внесем изменения в www/.templates/design/layout.twig

```
{% raw %}<!doctype html>
<html class="no-js" lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  </head>
  <body>
 
      <div class="row">
          <div class="small-12 columns">
              {# Главное меню — все подразделы верхнего уровня #}
	      {% widget 'Menu' with { catalog: server } %}
          </div>
      </div>
 
    <div class="row">
      <div class="large-12 columns">
 
        <a href="/" title="На главную"><img src="{{ server.picture }}" alt="LOGO" align="left"></a>
        <h1>{{ server.name|raw }}</h1>
 
      </div>
    </div>
 
    {# Основное содержимое #}   
    {% block main %}{% endblock %}   
 
  </body> 
</html>{% endraw %}
```

Замечательно:

![Пример]({{site.baseurl}}/images/pic8.png)

## Хлебные крошки

Для построения «хлебных крошек» используем виджет Breadcrumbs:

```
{% raw %}<!doctype html>
<html class="no-js" lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  </head>
  <body>
 
      <div class="row">
          <div class="small-12 columns">
              {# Главное меню — все подразделы верхнего уровня #}
	      {% widget 'Menu' with { catalog: server } %}
          </div>
      </div>
 
    <div class="row">
      <div class="large-12 columns">
 
        <a href="/" title="На главную"><img src="{{ server.picture }}" alt="LOGO" align="left"></a>
        <h1>{{ server.name|raw }}</h1>
 
      </div>
    </div>
 
    <div class="row column">
       {# Хлебные крошки #}    
       {% widget 'Breadcrumbs' %}
    </div>    
 
    {# Основное содержимое #}   
    {% block main %}{% endblock %}   
 
  </body> 
</html>{% endraw %}
```

## Вспомогательное боковое меню

Для создания бокового меню также используем виджет Menu.

В боковом меню я хочу показывать содержание раздела первого уровня, в котором мы находимся. Поэтому для вычисления этого раздела воспользуюсь методом Catalog::getPath().

Напомню, что этот метод возвращает цепочку разделов:

* индекс 0 — раздел root, родитель всех разделов (объект Catalog, имеет ID=0)
* индекс 1 — текущий сервер (объект Server)
* индекс 2 — раздел 1-го уровня (объект Catalog)
* …
* индекс N — раздел N-уровня, вызвавший метод getPath

Таким образом для определения текущего раздела первого уровня, нам надо воспользоваться таким кодом: Application::getInstance()→getCatalog()→path[2]

Боковое меню будет показываться только на рядовых страницах, поэтому размещаем его в шаблоне www/.templates/design/page_ordinary.twig:

```
{% raw %}{% extends "layout.twig" %}
 
    <div class="row">
        <div class="columns small-12 medium-5 large-4">
	    <div class="callout">  
                <h2>{{ catalog.path[2].name|raw }}</h2>
                {% widget 'Menu' with { catalog: catalog.path[2], depth: 0 } %}
	    </div>  
        </div>      
        <div class="columns small-12 medium-7 large-8">
 
	    {% block content %}{% endblock %}	
 
        </div>
    </div>{% endraw %}
```

Итоговый результат:

![Пример]({{site.baseurl}}/images/pic9.png)