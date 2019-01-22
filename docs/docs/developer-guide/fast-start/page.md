---
layout: default
title: Урок №5. Делаем рядовую страницу сайта
nav_order: 5
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Делаем рядовую страницу сайта

При попадании на рядовую страницу мы будем выводить список всех материалов раздела с постраничной навигацией.

Внесем изменения в ordinary.php:
```
// получим список материалов активного раздела   
$list = $c->getMaterials()
          ->select('short')
          ->orderBy('dat', 'DESC')
          ->setItemCountPerPage(3)
          ->setCurrentPageNumber($_GET['page']);
 
$twig->display('ordinary-list.html', array(
    'list' => $list
));
```

Мы воспользовались уже знакомым методом получения списка материалов, задав модификаторы сортировки и страничного вывода.

Построим шаблон ordinary-list.html:
```
{% raw %}{% extends "ordinary.html" %}
 
{% block title %}{{ catalog.name }}{% endblock %}    
 
{% block content %}
 
            {% if list|length > 0 %}
            {# Список материалов #}
 
                {% for m in list %}
                    <div class="row">
                        <div class="large-12 columns">
                            <h3><a href="{{ m.url }}">{{ m.name }}</a></h3>             
                            {{ m.short }}
                            <p><a href="{{ m.url }}">Подробнее</a></p>
                        </div>
                    </div>
                    {% if not loop.last %}<hr/>{% endif %}            
                 {% endfor %}
 
                 {# Постраничная навигация #}                 
                 {% if list.getPageCount > 1 %}
                    <ul class="pagination">
                      {% for i in range(1, list.getPageCount) %}
                          <li{% if i == list.getCurrentPageNumber %} class="current"{% endif %}><a href="?page={{ i }}">{{ i }}</a></li>
                      {% endfor %}                      
                    </ul>
                 {% endif %} 
 
            {% endif %} 
 
{% endblock %}{% endraw %}
```

Результат не заставит себя долго ждать:

![Пример]({{site.baseurl}}/images/pic15.png)

Теперь обеспечим показ выбранного материала из списка. URL материалов формируется по следующему шаблону:

	http://<сервер>/<alias раздела 1>/…/<alias раздела N>/<alias материала>
 

При обращении к страницам фронтофиса ядро системы, анализируя URL запроса определят к какому серверу и разделу произошло обращение. Оставшаяся часть URL, для которой нет соответствующего раздела доступна через метод Application::getUnparsedUrl(), c помощью которого мы можем определить, какой материал показывать.

Дополним ordinary.php:

```
$uu = $a->getUnparsedUrl();
 
// покажем запрошенный материал
if ($uu) {
 
    try {
 
        $twig->display('ordinary-material.html', array(
            'material' => $c->getMaterialByAlias($uu)
        ));        
 
    } catch (\Exception $e) {
 
        $twig->display('404.html');
 
    }  
 
// получим список материалов активного раздела
} else {
 
    $twig->display('ordinary-list.html', array(   
        'list' => $c->getMaterials()
                      ->select('short')
                      ->orderBy('dat', 'DESC')
                      ->setItemCountPerPage(3)
                      ->setCurrentPageNumber($_GET['page'])
    ));
 
}
```

Конструкция получения материала обернута в try catch на случай запроса несуществующего материала. В этом случае будет показан шаблон 404.html

Шаблон material-ordinary.html:

```
{% raw %}{% extends "ordinary.html" %}
 
{% block title %}{{ material.name }}{% endblock %}    
 
{% block content %}
 
    <h1>{{ material.name }}</h1>
    {{ material.text|raw }}
 
{% endblock %}{% endraw %} 
```

Для того, чтобы при показе материала, ссылка на последний раздел в «хлебных крошках» была активна, изменим blocks/breadcrumbs.html:

    <div class="row">
        <div class="large-12 columns">
            <ul class="breadcrumbs">
            {% for c in catalog.getPath if c.id %}
                <li{% if catalog.id == c.id and (not material is defined) %} class="current"{% endif %}>
                    <a href="{{ c.url }}">{{ c.name|striptags }}</a>
                </li>
            {% endfor %}
            </ul>
        </div>
    </div>
 

Оценим результат:

![Пример]({{site.baseurl}}/images/pic16.png)