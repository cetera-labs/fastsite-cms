---
layout: default
title: Урок №7. Делаем архив новостей с календарем.
nav_order: 7
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок №7. Делаем архив новостей с календарем.

На примере темы «Корпоративный сайт».

Открываем окно настройки темы и смотрим шаблон страницы новостей:

![Пример]({{site.baseurl}}/images/cp-lesson7-1.png)

Видим, что на странице используется виджет Section. Таким образом, для добавления календаря, необходимо использовать свой шаблон для виджета Section. Укажем виджету использовать наблон news.twig, добавив параметр template в код вызова виджета:

```
{% raw %}{% widget 'Section' with { template: 'news.twig', list_template: 'tiles.twig', list_limit: 6, material_share_buttons: 1, material_template: 'news.twig' } %}{% endraw %}
```
Сохраняем шаблон и сохраняем конфигурацию темы.

Далее нужно создать шаблон «news.twig» для виджета Section. Открываем панель «Шаблоны виджетов» и открываем стандартный шаблон виджета Section:

![Пример]({{site.baseurl}}/images/cp-lesson7-1.png)

Сохраним его как «news.twig» и приступим к модификации:

![Пример]({{site.baseurl}}/images/lesson-7-3.png)

Список новостей выводится виджетом List, {% raw %}({% widget 'List' with......){% endraw %}. Добавим рядом со списком новостей виджет Calendar:

```
{% raw %}<div class="row">
    <div class="small-12 medium-8 column">

        {% widget 'List' with {
            catalog:   widget.catalog.id,
            template:  widget.getParam('list_template'),
            where:     widget.getParam('list_where'),
            page:      widget.getParam('list_page'),
            page_param:widget.getParam('list_page_param'),
            limit:     widget.getParam('list_limit'),
            order:     widget.getParam('list_order'),
            sort:      widget.getParam('list_sort'),
            paginator: widget.getParam('list_paginator'),
            css_class: widget.getParam('list_css_class'),         
            paginator_template:  widget.getParam('paginator_template')
        } %}
    </div>
    <div class="small-12 medium-4 column">
        {% widget 'Calendar' with { } %}
    </div>
</div>{% endraw %}
```
Далее необходимо связать каледнарь со списком новостей, чтобы дата. выбранная в календаре влияла на содержимое списка. Для этого используем параметр «where» виджета List. Этот параметр позволяет организовать фильтрацию списка метериалов, который показывает виджет.

Дата, выбранная в календаре, передается через query параметр «date». В twig-шаблоне получить этот параметр можно через глобальный объект \Cetera\Application, который присвоен переменной application: application.request('date'). Формирование фильтра будет выглядеть следующим образом:

```
{% raw %}{% if application.request('date') %}
{# выбрана дата - формируем фильтр #}

    {% if application.request('month') %}
    {# новости за месяц #}
        {% set filter = "DATE_FORMAT(dat,'%Y-%m')='"~application.request('date')|date("Y-m")~"'" %}  
    {% else %}
    {# новости за день #}
        {% set filter = "DATE_FORMAT(dat,'%Y-%m-%d')='"~application.request('date')|date("Y-m-d")~"'" %}          
    {% endif %}
    
{% else %}
    {# дата не выбрана - пустой фильтр #}
    {% set filter = "" %}    
{% endif %}{% endraw %}
``` 

 Полный код измененного шаблона:

```
{% raw %}{% if widget.error404 %}

    {% if widget.getParam('page404_template') %}
        {% include widget.getParam('page404_template') %}
    {% else %}
        <div class="content">
            {% include "@widget/_common/page404.twig" %}
        </div>
    {% endif %}    

{% elseif widget.material %}

    {% widget 'Material' with {
        catalog:       widget.catalog.id,
        template:      widget.getParam('material_template'),
        material_id:   widget.material.id,
        share_buttons: widget.getParam('material_share_buttons'),
        show_pic:      widget.getParam('material_show_pic'),
        show_meta:     widget.getParam('show_meta')
    } %}    

{% else %}

    {% if application.request('date') %}
    {# выбрана дата - формируем фильтр #}
    
        {% if application.request('month') %}
        {# новости за месяц #}
            {% set m = ['','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'] %}
            <h1>{{ widget.catalog.name }} за {{ m[application.request('date')|date("n")] }} {{ application.request('date')|date("Y") }}</h1>
            {# фильтр по дате #}
            {% set filter = "DATE_FORMAT(dat,'%Y-%m')='"~application.request('date')|date("Y-m")~"'" %}  
        {% else %}
        {# новости за день #}
            {% set m = ['','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'] %}
            <h1>{{ widget.catalog.name }} за {{ application.request('date')|date("d") }} {{ m[application.request('date')|date("n")] }} {{ application.request('date')|date("Y") }}</h1>
            {# фильтр по дате #}
            {% set filter = "DATE_FORMAT(dat,'%Y-%m-%d')='"~application.request('date')|date("Y-m-d")~"'" %}          
        {% endif %}
        
    {% else %}
        {# дата не выбрана - пустой фильтр #}
        {% set filter = "" %}    
        <h1>{{ widget.catalog.name }}</h1>
    {% endif %}

    <div class="row">
        <div class="small-12 medium-8 column">

            {# список новостей с фильтром #}
            {% widget 'List' with {
                catalog:   widget.catalog.id,
                template:  widget.getParam('list_template'),
                where:     filter,
                page:      widget.getParam('list_page'),
                page_param:widget.getParam('list_page_param'),
                limit:     widget.getParam('list_limit'),
                order:     widget.getParam('list_order'),
                sort:      widget.getParam('list_sort'),
                paginator: widget.getParam('list_paginator'),
                css_class: widget.getParam('list_css_class'),         
                paginator_template:  widget.getParam('paginator_template')
            } %}
        </div>
        <div class="small-12 medium-4 column">
            {% widget 'Calendar' with { } %}
        </div>
    </div>
    
{% endif %}{% endraw %}
```
Сохраняем измененный шаблон. Итоговый результат:

![Пример]({{site.baseurl}}/images/lesson7-4.png)