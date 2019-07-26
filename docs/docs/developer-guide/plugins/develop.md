---
layout: default
title: Разработка плагинов
nav_order: 1
parent: Плагины (модули) Cetera CMS
grand_parent: Руководство разработчика
---

# Разработка плагинов

## Файловая структура плагина

	<имяплагина>/
	+---info.json
    +---composer.json
	+---config.php
	+---install.php
	+---schema.xml
	+---classes/
	|   +---<Имяплагина>/
	|       +---Classname1.php
	|       +---Classname2.php
	+---ext/
	|   +---Component1.js
	|   +---Component2.js
    +---lang/        

## Файл info.json (обязателен, если модуль будет устанавливаться через MarketPlace)

В файле содержится описательная часть плагина.

### Структура:
	{
	    "version": "Версия плагина",
	    "cms_version_min": "Минимальная версия CMS для работы плагина (необязательный параметр)",
	    "cms_version_max": "Максимальная версия CMS под которой будет работать плагин (необязательный параметр)",
	    "title": "Имя плагина",
	    "description": "Описание плагина.",
	    "author": "Автор плагина"
	}

### Пример:

	{
	    "version": "1.0",
	    "cms_version_min": "3.10.0",
	    "title": "Банерная система",
	    "description": "Модуль управления показом баннеров на сайте. Подсчет статистики.",
	    "author": "<a href='http://www.cetera.ru'>Cetera Labs</a>"
	}

## Файл composer.json (обязателен, если модуль будет устанавливаться как composer-пакет)

### Структура
    
    Стандартная, за исключением:
    
	{
	    "type": "cetera-cms-plugin",
        "extra": {
            "title": "Название модуля",
            "description": "Описание модуля"
        }
	}
    
### Пример:

    {
        "name": "cetera-labs/plugin-dummy",
        "type": "cetera-cms-plugin",
        "license": "MIT",
        "authors": [
            {
                "name": "Roman Romanov",
                "email": "nicodim@mail.ru",
                "role": "Lead Developer"
            }
        ],
        
        "minimum-stability": "dev",
        
        "autoload": {
            "psr-4": { 
                "Dummy\\": "classes/"
            }
        },

        "extra": {
            "description": "The dummy plugin for Cetera CMS"
        }
    }    

## Файл config.php

Скрипт подключается в методе Application::initPlugins() и позволяет плагину встроить себя в интерфейс BackOffice, зарегистрировать фильтр вывода и т.д.

### Используемые методы:

	$this->isFrontOffice()

Определяет где в данные момент работает приложение в админке или на фронтофисе.

	$this->getBo()

Возвращает объект BackOffice, если мы находимся в админке или NULL — для фронтофиса

	$this->getUser()

Возвращает авторизованного пользователя

	$this->registerOutputHandler(<array(class, method)>|<function>|<file>|<closure>)

Регистрирует фильтр вывода на фронтофисе. После выполнения кода контроллера, поочередно выполняются зарегистрированные фильтры. В качестве параметра им передается буфер вывода.

	$this->addUserGroup(array('id'⇒<GROUP_ID>,'name'⇒'<GROUP_NAME>','describ'⇒'<GROUP_DESCRIB>'))

Добавить предустановленную группу пользователей.

GROUP_ID должен быть < 0

Зарезервированные другими плагинами GROUP_ID — [http://cetera.ru/cetera_cms/RESERVED.txt](http://cetera.ru/cetera_cms/RESERVED.txt)

	$this→getUser()→isInGroup(<GROUP_ID>)

Проверяет, принадлежит ли пользователь группе. Позволяет реализовать права доступа в плагине

	$this→addCronJob(<array(class, method)>|<function>|<file>|<closure>)

Для организации запуска кода плагина по расписанию. При условии что настроен запуск по расписанию www/cms/cron.php

	$this→registerWidget(<array>)

Регистрирует виджет в системе.

	array(

	  'name'     => 'Символьный идентификатор виджета',
	  'class'    => 'PHP класс виджета, должен расширять \Cetera\Widget\Widget',
	  'describ'  => 'Описание виджета',
	  'icon'     => 'Иконка для админки 16x16',
	  'ui'       => 'Ext компонент настройки свойств виджета, должен расширять Cetera.widget.Widget',
	 

	)

	$this→getBo()→addScript(<file>)

Загружает указанный Javascript в админку.

	$this→getBo()→addModule(<array>)

Добавляет модуль в админку

```
array(

  	'id'	       => 'символьный идентификатор модуля',
  	'position' => MENU_SITE, // в какой раздел меню встроить модуль (MENU_SITE, MENU_SERVICE, MENU_PLUGINS)
      'name'     => 'Название модуля',
      'icon'       => 'icon.gif',
      'url'         => 'скрипт, загружаемый при активации модуля', // устаревший механизм
      'class'     => 'BannersPanel'
 

)
```

## Файл install.php

Этот скрипт запускается один раз при инсталляции плагина.

## Файл schema.xml

XML-файл содержит описание структуры БД, необходимой для работы плагина.

Создается вручную.

Тегами <table>, <field>, <key> описываются таблицы БД.

Тегами <object>, <field> описываются типы материалов (На основе данных из таблиц types и types_fields)

Если необходимо дополнить полями существующие типы материалов, то указать только необходимые поля:

	<object name="materials">
	    <field name="meta_title" type="1" pseudo_type="0" description="Meta title" length="1000" show="1" required="0" fixed="0" editor="1" editor_user="" default_value="" page="SEO" />
	    <field name="meta_keywords" type="1" pseudo_type="0" description="Meta keywords" length="1000" show="1" required="0" fixed="0" editor="16" editor_user="" default_value="" page="SEO" />
	    <field name="meta_description" type="1" pseudo_type="0" description="Meta description" length="1000" show="1" required="0" fixed="0" editor="16" editor_user="" default_value="" page="SEO" />
	</object>

Тегами <widgetcontainer>, <widget> описываются виджеты (На основе данных из таблиц widgets_containers и widgets)

### Пример файла:

```
<?xml version="1.0"?>
<schema>
 
<table name="TABLE">
    <field name="FIELD1" type="int(10) unsigned" null="0" auto_increment="1" />
    <field name="FIELD2" type="varchar(100)" null="1" />
    <key name="PRIMARY" unique="1">
        <column name="FIELD1" />
    </key>
    <key name="key" unique="0">
        <column name="FIELD2" />
    </key>
</table>
 
<object name="forums" description="Форумы" fixed="1" handler="" plugin="">
    <field name="tag" type="3" pseudo_type="0" description="Тэг" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="name" type="1" pseudo_type="0" description="Заголовок" length="99" show="0" required="0" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="alias" type="1" pseudo_type="0" description="Alias" length="255" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="dat" type="5" pseudo_type="0" description="Дата создания" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="dat_update" type="5" pseudo_type="0" description="Дата изменения" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="autor" type="3" pseudo_type="0" description="Автор" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="type" type="3" pseudo_type="0" description="Свойства" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="idcat" type="3" pseudo_type="0" description="Раздел" length="1" show="0" required="1" fixed="1" editor="0" editor_user="" default_value="" />
    <field name="link_cat" type="3" pseudo_type="0" description="Связь с разделом" length="1" show="0" required="0" fixed="0" editor="3" editor_user="" default_value="" />
    <field name="link_art" type="3" pseudo_type="0" description="Связь с материалом" length="1" show="0" required="0" fixed="0" editor="3" editor_user="" default_value="" />
    <field name="parent" type="6" pseudo_type="0" description="Ответ на" length="0" show="0" required="0" fixed="0" editor="6" editor_user="" default_value="" />
    <field name="lastanswer" type="5" pseudo_type="0" description="Дата последнего ответа" length="1" show="0" required="0" fixed="0" editor="5" editor_user="" default_value="" />
    <field name="username" type="1" pseudo_type="0" description="Пользователь" length="1000" show="1" required="0" fixed="0" editor="1" editor_user="" default_value="" />
    <field name="email" type="1" pseudo_type="0" description="E-mail" length="1000" show="1" required="0" fixed="0" editor="1" editor_user="" default_value="" />
    <field name="ip" type="3" pseudo_type="0" description="IP" length="1" show="0" required="0" fixed="0" editor="3" editor_user="" default_value="" />
    <field name="closed" type="9" pseudo_type="0" description="Закрытая ветка" length="1" show="1" required="0" fixed="0" editor="9" editor_user="" default_value="" />
    <field name="last_post" type="6" pseudo_type="0" description="Последний ответ" length="0" show="0" required="0" fixed="0" editor="6" editor_user="" default_value="" />
    <field name="answers" type="3" pseudo_type="0" description="Всего ответов" length="1" show="0" required="0" fixed="0" editor="3" editor_user="" default_value="" />
    <field name="thread" type="3" pseudo_type="0" description="Основная тема" length="1" show="0" required="0" fixed="0" editor="3" editor_user="" default_value="" />
    <field name="text" type="1" pseudo_type="0" description="Текст" length="32767" show="1" required="0" fixed="0" editor="15" editor_user="" default_value="" />
</object>
 
<widgetcontainer widgetAlias="simple_index" widgetTitle="Главная страница" widgetDisabled="0" widgetProtected="1">
 
<widget widgetTitle="Слайдер" widgetName="List">
<![CDATA[
a:6:{s:4:"name";s:0:"";s:7:"catalog";s:2:"14";s:5:"limit";s:1:"8";s:5:"order";s:3:"dat";s:4:"sort";s:4:"DESC";s:8:"template";s:39:"themes/simple/design/blocks/slider.twig";}
]]>
</widget>           
 
</widgetcontainer>
 
</schema>
```

## Каталог classes

Место для хранения PHP классов.

Используется автозагрузка классов с префиксом <Имяплагина>\ из каталога classes/<Имяплагина>/

## Каталог ext

Место для хранения Ext JS классов, реализующих пользовательский интерфейс плагина.

Классы должны иметь префикс Plugin.<имяплагина>.

## Каталог lang

Место для хранения файлов с переводом на другие языки

## Поддержка многоязычности в плагинах

Cetera CMS 3.29+

Мультиязычность в Cetera CMS построена с использованием GNU gettext. Файлы c переводами размещаются в подкаталоге lang.

В файле config.php указываем местоположение переводов к модулю:

	$this->getTranslator()->addTranslation(__DIR__.'/lang');

В файлах PHP фразы, подлежащие переводу оборачиваем в \Cetera\Application::getTranslator()::\_():

```
$t = \Cetera\Application::getInstance()->getTranslator();
 
$this->getBo()->addModule(array(
    'id'       => 'sale',
    'position' => MENU_SITE,
    'name'     => $t->_('Магазин'),
    'icon'     => 'images/icon.png',
    'class'    => 'Plugin.sale.Setup',
));
В Javascript используем функцию _()

Ext.define('Plugin.sale.Delivery', {
 
    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Delivery',
 
    columns: [
        {text: "ID",       width: 50, dataIndex: 'id'},
		{text: _('Акт.'),     width: 60, dataIndex: 'active', renderer: function (value) { if (value) return 'Да'; else return 'Нет'; }},
		{text: _('Сорт.'),    width: 60, dataIndex: 'tag'},		
        {text: _('Название'), flex: 1, dataIndex: 'name'}
    ],
 
    border: false,
 
	editWindowClass: 'Plugin.sale.DeliveryEdit',
 
	store: {
		model: 'Plugin.sale.model.Delivery',
		sorters: [{property: "tag", direction: "ASC"}],
		remoteSort: false,
		autoLoad: true,
		autoSync: true			
	}	
 
});
```

В Twig-шаблонах используем расширение i18n https://twig-extensions.readthedocs.io/en/latest/i18n.html#usage
Также можно использовать функцию \_()

```
{% raw %}<div id="add-to-favourites-auth-popup" data-reveal class="reveal tiny">
      <div class="row column text-center">
 
          <p>{{ _('Чтобы сохранять список, вам нужно') }}:</p><a href="{{ widget.param('login_url') }}" title="" class="button expanded">{{ _('Войти') }}</a>
          <p>или</p><a href="{{ widget.param('register_url') }}" title="" class="button expanded">{{ _('Зарегистрироваться') }}</a>
 
      </div>
      <button data-close aria-label="{{ _('Закрыть') }}" class="close-button"><span aria-hidden="true">&times;</span></button>
</div>{% endraw %}
```

### Перевод ресурсов на другие языки

Работа по переводу фраз на другие языки ведется с помощью инструмента [POEdit](https://poedit.net/)

Для создания нового перевода:
1. Запускаем POEdit и выбираем "Файл -> Создать ...".
2. Выбираем язык перевода, например "английский".
3. Выбираем "Файл -> Сохранить как ..."  и сохраняем файл перевода en.po в каталог lang модуля/
4. Указываем местоположение файлов исходного кода для извлечения фраз для перевода. Для этого выбираем "Каталог -> Свойства ...". Переходим на вкладку "Папки с исходными файлами". Добавляем папку модуля. На вкладке "Ключевые слова исходных файлов" нужно добавить дополнительное ключевое слово "_". Сохраняем проект.
5. Извлекаем фразы для перевода с помощью кнопки "Обновить из кода" на панели инструментов.
6. Переводим фразы в интерфейсе программы.
7. Сохраняем "Файл -> Сохранить"

Для работы с существующим переводом:
1. Открываем .po файл из каталога lang
2. Извлекаем фразы для перевода с помощью кнопки "Обновить из кода" на панели инструментов.
3. Переводим фразы в интерфейсе программы.
4. Сохраняем "Файл -> Сохранить"

