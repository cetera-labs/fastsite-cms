---
layout: default
title: Модуль «Форум»
nav_order: 10
parent: Плагины (модули) Cetera CMS
grand_parent: Руководство разработчика
---

# Модуль «Форум»

Добавляет возможность создавать темы и комменитировать их.

При установке модуль добавляет тип материалов «forum_topics», в котором хранятся темы, и тип материалов «forum_posts», в котором хранятся комментарии форума.

Для работы с темами форума используется класс \Forum\Topic, расширяющий \Cetera\Material. Для работы с комментариями форума используется класс \Forum\Post, расширяющий \Cetera\Material.

## Класс \Forum\Topic

Метод | Описание
---|---
**getPosts()**|Возвращает итератор \Cetera\Iterator\Material с комментариями к теме
**getPostsCount()**|Возвращает кол-во комментариев, синоним getPosts()→getCountAll()
**addPost($text,$publish=true,$user=null,$nickname)**|Добавляет комментарий к теме. $text — текст комментария, $publish — флаг публикации комментария, $user — автор, комментария (если не указан, то используется \Cetera\Application::getUser())), $nickname — псевдоним автора (для неавторизованных пользователей)

## Класс \Forum\Post

Метод | Описание
---|---
**getNickname()**|Возвращает имя пользователя оставившего комментарий

## Виджет Forum.Category

В модуле «Форум» это виджет позволяет организовать просмотр тем раздела форума. Для отображения тем используется виджет «Forum.Topic.Item», для отображения списка тем— виджет «Forum.Topic.List»

### Пример вызова в PHP

	\Cetera\Application::getInstance()->getWidget('Forum.Category', array(
	))->display();

### Пример вызова в Twig

	{% raw %}{% widget 'Forum.Category' with {  } %}{% endraw %}

### Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**catalog**|Раздел форума. По умолчанию — текущий раздел
**material_template**|Шаблон виджета 'Forum.Topic.Item' для отображения темы
**material_id**|ID темы. По умолчанию **null**
**material_alias**|alias материала. По умолчанию используется Application::getUnparsedUrl()
**list_template**|Шаблон виджета 'Forum.Topic.List' для отображения списка тем
**list_limit**|Ограничение кол-ва тем. 0 — без ограничения. По умолчанию **10**
**list_page**|№ страницы, если используется ограничение кол-ва тем. По умолчанию **1**
**list_order**|Поле, по которому сортировать список. По умолчанию dat_update
**list_sort**|`[ASC|DESC]` Порядок сортировки. По умолчанию **DESC**
**list_where**|Фильтр списка тем. В SQL запрос добавляется условие указанное условие WHERE
**list_paginator**|`[TRUE|FALSE]` Показать постраничную навигацию. По умолчанию **TRUE**
**posts_limit**|Ограничение кол-ва комментариев. 0 — без ограничения. По умолчанию **0**
**paginator_template**|Шаблон виджета постраничной навигации
**page404_template**|Шаблон с текстом в случае, если материал не найден

## Виджет Forum.Topic.Add

В модуле «Форум» это виджет формы для добавления темы.

Параметр | Описание
---|---
**template**|Шаблон вывода
**catalog**|Раздел, в котором находится тема
**publish**|`[TRUE|FALSE]` Сразу публиковать отправленный комментарий. По умолчанию **TRUE**
**submit_text**|Текст на кнопке отправки комментария. По умолчанию «Отправить сообщение»
**success_text**|Сообщение об успешной отправке комментария. По умолчанию «Ваш комментарий принят»

## Виджет Forum.Topic.List

В модуле «Форум» это виджет выводит список тем для текущего раздела

Параметр | Описание
---|---
**template**|Шаблон вывода
**catalog**|Раздел, в котором находится тема
**iterator**|Объект \Cetera\Iterator\Material по списком тем
**order**|Поле сортировки комментариев. По умолчанию **dat_update**
**sort**|`[ASC|DESC]` Порядок сортировки комментариев. По умолчанию **DESC**
**limit**|Количество комментариев на странице. 0 — показать все. По умолчанию **0**
**where**|Фильтр материалов. В SQL запрос добавляется условие указанное условие WHERE
**page**|Показать страницу, при установленном параметре limit, По умолчанию — из $_REQUEST[page_param]
**page_param**|Имя параметра, в котором передавать № страницы. По умолчанию **page**
**paginator**|`[TRUE|FALSE]` Показать постраничную навигацию. По умолчанию **FALSE**
**paginator_url**|Формат ссылок на страницы. {material} — заменяется на url материала, {page} — на № страницы. По умолчанию {material}?page={page}
**paginator_template**|Шаблон пагинатора
**form**|`[TRUE|FALSE]` Показать форму добавления комментария **TRUE**
**form_template**|Шаблон формы
**form_title**|Заголовок формы. По умолчанию **Добавить комментарий**
**form_publish**|`[TRUE|FALSE]` Сразу публиковать отправленный комментарий. По умолчанию **TRUE**
**form_submit_text**|Текст на кнопке отправки комментария. По умолчанию «Отправить сообщение»
**form_success_text**|Сообщение об успешной отправке комментария. По умолчанию «Ваш комментарий принят»

## Виджет Forum.Topic.Item

В модуле «Форум» это виджет выводит тему и комментарии к ней.

Параметр|Описание
---|---
**template**|Шаблон вывода
**posts_limit**|Количество комментариев на странице. 0 — показать все. По умолчанию **0**

## Виджет Forum.Post.Add

В модуле «Форум» это виджет формы для комментирования темы.

Параметр|Описание
---|---
**template**|Шаблон вывода
**material**|Тема форума. Объект класса \Forum\Topic наследник класса \Cetera\Material
**material_type**|Тип материала темы
**material_id**|ID темы
**catalog**|Раздел, в котором находится тема
**material_alias**|Алиас темы
**publish**|`[TRUE|FALSE]` Сразу публиковать отправленный комментарий. По умолчанию **TRUE**
**ajax**|`[TRUE|FALSE]` Отправка комментария без перезагрузки страницы. По умолчанию **FALSE**
**submit_text**|Текст на кнопке отправки комментария. По умолчанию «Отправить сообщение»
**success_text**|Сообщение об успешной отправке комментария. По умолчанию «Ваш комментарий принят»

Для того, чтобы выбрать тему, к которой будут отправляться комментарии, используйте параметры:

* material — явная передача объекта темы
* material_type и material_id — поиск темы по типу и ID или
* catalog и material_alias — поиск темы по разделу и алиасу

## Виджет Forum.Post.List

В модуле «Форум» это виджет списка комментариев к теме.

Параметр|Описание
---|---
**template**|Шаблон вывода
**material**|Тема форума. Объект класса \Forum\Topic наследник класса \Cetera\Material
**material_type**|Тип материала темы
**material_id**|ID материала темы
**catalog**|Раздел, в котором находится тема
**material_alias**|Алиас темы
**order**|Поле сортировки комментариев. По умолчанию **dat**
**sort**|`[ASC|DESC]` Порядок сортировки комментариев. По умолчанию **ASC**
**limit**|Количество комментариев на странице. 0 — показать все. По умолчанию **0**
**page**|Показать страницу, при установленном параметре limit, По умолчанию — из $_REQUEST[page_param]
**page_param**|Имя параметра, в котором передавать № страницы. По умолчанию **page**
**paginator**|`[TRUE|FALSE]` Показать постраничную навигацию. По умолчанию **FALSE**
**paginator_url**|Формат ссылок на страницы. {material} — заменяется на url материала, {page} — на № страницы. По умолчанию {material}?page={page}
**paginator_template**|Шаблон пагинатора
**form**|`[TRUE|FALSE]` Показать форму добавления комментария **TRUE**
**form_template**|Шаблон формы
**form_title**|Заголовок формы. По умолчанию Добавить комментарий
**form_publish**|`[TRUE|FALSE]` Сразу публиковать отправленный комментарий. По умолчанию **TRUE**
**form_submit_text**|Текст на кнопке отправки комментария. По умолчанию «Отправить сообщение»
**form_success_text**|Сообщение об успешной отправке комментария. По умолчанию «Ваш комментарий принят»