---
layout: default
title: Урок №3. Подключаем шаблонизатор
nav_order: 3
parent: Быстрый старт, уроки для разработчиков
grand_parent: Руководство разработчика
---

# Урок№3. Подключаем шаблонизатор

Внимательный читатель заметит, что смешивать html и php-логику — плохая практика. И с этим сложно не согласиться. Будем идти в ногу со временем и подключим Twig в качестве шаблонизатора к нашему сайту.

Замечание. Twig входит в пакет библиотек, устанавливаемых вместе с дистрибутивом CMS начиная с версии 3.10.1

Создадим файл www/themes/default/bootstrap.php в котором произведем инициализацию Twig:

```php
	$a = $application;
	// Сервер
	$s = $a->getServer();
	// Активный раздел
	$c = $a->getCatalog();
	$twig = $a->getTwig();
	$twig->addGlobal('server',  $s);
	$twig->addGlobal('catalog', $c);
```

* layout.twig — основной каркас сайта
* page_index.twig — главная страница
* page_ordinary.twig — рядовая страница

www/themes/default/design/layout.twig:


	<!doctype html>
	<html class="no-js" lang="ru">
	  <head>
	    <meta charset="utf-8" />
	    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	  </head>
	  <body>
    <div class="row">
      <div class="large-12 columns">
        <a href="/" title="На главную"><img src="{% raw %}{{ server.picture }}{% endraw %}" alt="LOGO" align="left"></a>
        <h1>{% raw %}{{ server.name|raw }}{% endraw %}</h1>
      </div>
    </div>
    {# Основное содержимое #}   
    {% raw %}{% block main %}{% endblock %}{% endraw %} 
	  </body> 
	</html>

Макет индексной страницы — в www/themes/default/design/page_index.twig:

    {% raw %}{% extends "layout.twig" %}{% endraw %} 
 

Макет рядовой страницы — в www/themes/default/design/page_ordinary.twig:

	{% raw %}{% extends "layout.twig" %}{% endraw %} 
 

Теперь контроллер главной страницы www/themes/default/default.php будет выглядеть гораздо проще:

	$twig->display('page_index.twig');
 

По такому же принципу создадим контроллер рядовой страницы www/themes/default/ordinary.php:

	$twig->display('page_ordinary.twig');


Для всех разделов первого уровня надо указать, что они используют контроллер ordinary.php:

![Свойства раздела О компании]({{site.baseurl}}/images/pic10.png)

Сравним результат:

![Свойства раздела О компании]({{site.baseurl}}/images/pic7.png)

Ничего не сломалось. Можно двигаться дальше.