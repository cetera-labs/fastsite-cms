---
layout: default
title: Разработка нового сайта на Cetera CMS
nav_order: 6
---
# Разработка нового сайта на Cetera CMS(Developer style guide)

## 0. Перенос с площадки fastsite.ru
Если проект был изначально создан по методологии «Быстрый старт» и открыт через конструктор на площадке fastsite.ru, то необходимо запланировать и не забыть перенести его на свою собственную площадку хостинга.

## 1. Установка

Устанавливаем Cetera CMS согласно инструкции. Алгоритм выбора начальной темы:

* разработка интернет-магазина - выбираем shop-2018 последней версии;
* все остальные случаи - выбираем corp-2018 последней версии.

## 2. Переименование темы, удаление ненужных файлов

Если сайт имеет будет иметь дизайн, структуру и верстку отличающуюся от типового сайта, необходимо переименовать тему.

Это позволит как минимум избежать неожиданностей при случайном обновлении темы.

![Пример]({{site.baseurl}}/images/rename_theme.png)

Идентификатор предлагается выбирать по коду проекта в GIT
Обязательно включаем «режим разработчика» и «запретить обновление темы»

Если в каталоге темы присутствуют файлы data.sql и install.php то их нужно удалить из проекта, т.к. они больше не понадобятся.

## 3. Правила организации файловой структуры

Все файлы, необходимые для работы фронтофиса и бэкофиса проекта (элементы интерфейса, шаблоны, контроллеры, классы, стили, картинки, скрипты и т.д.) находятся в каталоге темы /www/themes/<имя_темы>/

Структура каталога:

* css/ - стили
* js/ - скрипты
* images/ - графика
* classes/ - PHP классы
* design/ - шаблоны верстки
* widgets/ - шаблоны виджетов
* ext/ - элементы интерфейса бэкофиса
* bootstrap.php - скрипт подключается перед вызовом контроллера на фронтофисе
* config.php - скрипт подключается при инициализации темы

**Важно!**

Если вам необходимо расширение функциональности CMS в рамках проекта, реализуйте её в этом же каталоге, вместо того, чтобы создавать новый модуль в каталоге plugins/ Вся функциональность, описанная в [https://cetera-labs.github.io/docs/developer-guide/plugins/develop.html]({{site.baseurl}}/docs/developer-guide/plugins/develop.html) сохраняется и в каталоге themes/

Также, допускается размещение дополнительных модулей в каталоге themes/<ИМЯ_МОДУЛЯ>/

**Разработка за пределами каталога themes/ запрещена!**

## 4. Правила именования twig шаблонов
* главный скелет расположен в файле layout.twig (если их несколько, то допускается layout_<SUFFIX>.twig)
* файлы шаблонов конечных страниц начинаются с page_, например, главная страница - page_index.twig, страница поиска - page_search.twig и т.д.
* шаблон конечной страницы расширяет основной скелет layout.twig
* файлы шаблонов подключаемых блоков начинаются с block_, например block_left_menu.twig
* если шаблонов подключаемых блоков много, допускается их размещение в подкаталогах внутри design/

## 5. Свои PHP классы

Должны быть расположены в подкаталоге classes/  
1 класс - 1 файл  
имя файла = ИмяКласса.php  
Файл с описанием класса подключаются автоматически!

Если классов много и требуются пространства имен, используем стандарт PSR-4. Вкратце - каждое пространство имен в своем подкаталоге с сохранением иерархии. Соответствие имен файлов и каталогов - вплоть до регистра.

## 6. Контроллеры

В CeteraCMS контроллер (в 99% случаев) - это PHP-cкрипт, расположенный в корневом каталоге темы и запускаемый при обращении к сайту.

Выбрать какой контроллер будет запускаться при обращении к определенному разделу сайта можно указав имя контроллера в свойствах раздела в админке.

Также, можно указать контроллер через url, например, при обращении к https://cetera.ru/hello будет запущен контроллер hello.php, если он существует.

ВАЖНО! Перед запуском любого контроллера, автоматически исполняется скрипт bootstrap.php в корне темы.

Для обработки AJAX запросов, рекомендуется создать контроллер ajax.php

## 7. API

Все API классы Cetera CMS находятся в пространстве имен Cetera

Описание: [https://cetera.ru/cetera_cms/doc/api/](https://cetera.ru/cetera_cms/doc/api/)

Базовый ликбез: 

[https://cetera-labs.github.io/docs/developer-guide/fast-start/]({{site.baseurl}}/docs/developer-guide/fast-start/)  
[https://cetera-labs.github.io/docs/developer-guide/inside/]({{site.baseurl}}/docs/developer-guide/inside/)  
[https://cetera-labs.github.io/docs/developer-guide/widgets/]({{site.baseurl}}/docs/developer-guide/widgets/)

## 8. Полезные библиотеки, устанавливаемые вместе с Cetera CMS

Guzzle [http://docs.guzzlephp.org/en/stable/](http://docs.guzzlephp.org/en/stable/)  
Twig [https://twig.symfony.com/](https://twig.symfony.com/)  
DBAL [https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/index.html](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/index.html)  
ckeditor [https://ckeditor.com/](https://ckeditor.com/)  
zendframework1 [http://framework.zend.com](http://framework.zend.com)  
phpmailer [https://github.com/PHPMailer/PHPMailer/](https://github.com/PHPMailer/PHPMailer/)  
sypexgeo [http://sypexgeo.net/ru/download/](http://sypexgeo.net/ru/download/)  
ua-parser [https://github.com/tobie/ua-parser/tree/master/php](https://github.com/tobie/ua-parser/tree/master/php)  
mysqldump-php [https://github.com/ifsnop/mysqldump-php](https://github.com/ifsnop/mysqldump-php)  
phpmorphy [https://sourceforge.net/projects/phpmorphy/](https://sourceforge.net/projects/phpmorphy/)  
pclzip [http://www.phpconcept.net/pclzip](http://www.phpconcept.net/pclzip)