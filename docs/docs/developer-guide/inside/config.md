---
layout: default
title: Конфигурация Cetera CMSS
nav_order: 3
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Конфигурация Cetera CMS

Глобальный параметры конфигурации Cetera CMS находятся в файле .prefs в корневом каталоге сервера

	{% raw %}; Параметры базы данных
	dbhost=localhost
	dbname=xbit_new
	dbuser=root
	dbpass=
	dbdriver=pdo_mysql

	; Указывает, что дистрибутив установлен
	; Если этот параметр отсутствует, то работает перенаправление на скрипт установки
	setup_done=1

	; Посылать отладочные сообщения в FirePHP
	debug_level=1

	; Использовать memcached (если установлен) для кэширования
	cache_memcache=1
	; Использовать файлы для кэширования (каталог для кэширования WWWROOT/.cache/filecache)
	cache_file=0

	; Принудительно отключить соответствующий модуль
	module_disable[module_banners]=1

	; Проверять наличие и устанавливать beta-версии CMS (v3.9.0+)
	beta_versions=1

	; Редактирование материалов
	; Автоматически разбивать поля материала по вкладкам (v3.10.0+)
	editor.autoflow=1
	; Свой файл конфигурации CKEditor (http://docs.ckeditor.com/#!/guide/dev_configuration)
	htmleditor.config=<path_to_file>
	; http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-contentsCss
	htmleditor.css=<path_to_file>
	; http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-bodyClass
	htmleditor.body_class=<classname>
	; http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-stylesSet
	htmleditor.styles=<path_to_file>

	; Ограничение доступа к FO
	;fo_close=0
	;fo_close_msg=Site closed
	;fo_allow_users_bo=1
	;fo_allow_users=1
	;fo_allow_user=test
	;fo_allow_pw=test

	; Не считать статистику для определенных IP
	; список IP через ,
	;stats_filter=0.0.0.0

	;Загрузка файлов
	;Максимальные размеры загружаемых фото. При превышении происходит автоматическое уменьшение.
	file_upload_max_width=300
	file_upload_max_height=300{% endraw %}