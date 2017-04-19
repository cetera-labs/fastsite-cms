<?php
namespace Cetera;

include('../include/common.php');

$application->connectDb();
$application->initSession();
$application->initPlugins();

$translator = $application->getTranslator();  
foreach (Theme::enum() as $theme)
	$theme->addTranslation($translator);
		
$data = array(

			'templateExistsReplace' => $translator->_('Такой шаблон уже существует. Перезаписать?'),
			'materialLocked' 		=> $translator->_('материал редактируется пользователем <{0}>'),
			'resetDefault' 			=> $translator->_('Сбросить к первоначальному значению'),

			'prev' => $translator->_('Назад'),
			'next' => $translator->_('Дальше'),

			'propsMain' 	=> $translator->_('Основные'),
			'synonym' 	=> $translator->_('Синонимы'),

			'materialNotSaved' 	=> $translator->_('Материал не сохранен'),
			'materialSaved' 		=> $translator->_('Материал сохранен'),
			'materialFixFields' 	=> $translator->_('Исправьте неправильно заполненные поля'),

			'color_lengthText' => $translator->_('3 или 6 знаков'),
			'color_blankText'  => $translator->_('Неправильный формат'),

			'num' => $translator->_('Только целые положительные числа или 0'),
			'page' => $translator->_('Страница'),
			'fileNotFound' => $translator->_('Файл не существует'),
			'fileCreate' => $translator->_('Создать файл?'),
			'accessDenied' => $translator->_('Доступ запрещен'),
			'fileChanged' => $translator->_('Файл изменен'),
			'saveChanges' => $translator->_('Сохранить изменения?'),

			'expandAll' => $translator->_('Развернуть все'),
			'requestException' => $translator->_('Ошибка соединения с сервером.'),

			'addTheme' => $translator->_('Установить тему'),
			'activate' => $translator->_('Активировать'),
			'activity' => $translator->_('Активность'),
			'actvt' => $translator->_('Акт.'),
			'theme' => $translator->_('Тема'),
			'used' => $translator->_('Используется'),
			'apply' => $translator->_('Применить'),
			'themePrefs' => $translator->_('Настройки темы'),

			'addArea' => $translator->_('Добавить область'),
			'add' => $translator->_('Область'),
			'addWidget' => $translator->_('Создать виджет'),
			'template' => $translator->_('Шаблон'),
			'menu' => $translator->_('Меню'),
			'depth' => $translator->_('Глубина'),
			'rootFolder' => $translator->_('Корневой раздел'),
			'catalog' => $translator->_('Раздел'),
			'order' => $translator->_('Порядок'),
			'sort' => $translator->_('Сортировка'),
			'srt' => $translator->_('Сорт.'),
			'matCount' => $translator->_('Кол-во материалов'),

			'domainName' => $translator->_('Доменное имя'),
			'linkTo' => $translator->_('Ссылка на'),
			'setup' => $translator->_('Настроить'),
			'save' => $translator->_('Сохранить'),
			'saveAs' => $translator->_('Сохранить как'),
			'properties' => $translator->_('Свойства'),

			'typeDeleteWarning' => $translator->_('Внимание!<br>Вы удаляете тип {0}. Вместе с этим будут удалены все разделы этого типа,<br>а также все материалы из этих разделов.\n\nПродолжить?'),
			'fieldAdd' => $translator->_('Добавить поле'),
			'fieldEdit' => $translator->_('Изменить поле'),
			'description' => $translator->_('Описание'),
			'typeCreate' => $translator->_('Новый тип материалов'),
			'typeFixed' => $translator->_('Встроенные типы'),
			'typeUser' => $translator->_('Пользовательские типы'),
			'dataType' => $translator->_('Тип данных'),
			'fields' => $translator->_('Поля'),
			'invalidSize' => $translator->_('Неправильный размер'),
			'requiredField' => $translator->_('Обязательное поле'),
			'hiddenField' => $translator->_('Скрытое поле'),
			'showField' => $translator->_('Видимое'),
			'variants' => $translator->_('Варианты значений'),
			'defaultValue' => $translator->_('Значение по умолчанию'),
			'editor' => $translator->_('Редактор'),
			'fromCatalog' => $translator->_('Из раздела'),
			'fromCurrentCatalog' => $translator->_('Из текущего раздела'),
			'fromCatalog2' => $translator->_('От раздела'),
			'tipa' => $translator->_('типа'),
			'yes' => $translator->_('да'),
			'no' => $translator->_('нет'),
			'materialType' => $translator->_('Тип материалов'),

			'no' => $translator->_('№'),
			'title' => $translator->_('Заголовок'),
			'author' => $translator->_('Автор'),
			'published' => $translator->_('Опубликован'),
			'unpublished' => $translator->_('Не опубликован'),
			'attention' => $translator->_('Внимание!'),
			'msgCatLink' => $translator->_('Этот раздел является ссылкой. Перейти в исходный раздел для просмотра материалов?'),
			'materialDelete' => $translator->_('Удалить материал'),
			'newMaterial'   => $translator->_('Создать'),
			'newMaterialAs' => $translator->_('Создать по образцу'),
			'delete'    => $translator->_('Удалить'),
			'publish'   => $translator->_('Опубликовать'),
			'unpublish' => $translator->_('Распубликовать'),
			'preview' => $translator->_('Посмотреть на сервере'),
			'move' => $translator->_('Переместить'),
			'materialDeep' => $translator->_('Показать материалы из подразделов'),

			'usersBo'  => $translator->_('Показывать только пользователей BackOffice'),
			'filter'   => $translator->_('Фильтр'),
			'nickname' => $translator->_('Псевдоним'),
			'total'    => $translator->_('Всего'),
			'users'    => $translator->_('Пользователи'),

			'sites' => $translator->_('Сайты'),
			'createServer' => $translator->_('Создать сервер'),
			'newCatalog' => $translator->_('Новый раздел'),
			'newLink' => $translator->_('Создать ссылку на раздел'),
			'catProps' => $translator->_('Свойства раздела'),
			'copy' => $translator->_('Копировать'),
			'copySub' => $translator->_('Копировать подразделы'),
			'copyMaterials' => $translator->_('Копировать материалы'),
			'copyTo' => $translator->_('Копировать в'),
			'confirmation' => $translator->_('Подтверждение'),
			'navigation' => $translator->_('Навигация'),

			'login' 		=> $translator->_('Вход'),
			'logout' 	=> $translator->_('Выход'),
			'username' => $translator->_('Пользователь'),
			'password' => $translator->_('Пароль'),
			'lang' => $translator->_('Язык'),
			'remember' => $translator->_('запомнить'),
			'forgetPassword' => $translator->_('Забыли пароль'),
			'recoverPassword' => $translator->_('Создать новый пароль и отправить его на e-mail?'),
			'needUsername' => $translator->_('Введите имя пользователя'),

			'add' => $translator->_('Добавить'),
			'close' => $translator->_('Закрыть'),
			'refresh' => $translator->_('Обновить'),
			'wait' => $translator->_('Подождите ...'),
			'ok' => $translator->_('OK'),
			'cancel' => $translator->_('Отмена'),
			
			'upper' => $translator->_('Выше'),
			'downer' => $translator->_('Ниже'),
			
			'add' => $translator->_('Добавить'),
			'edit' => $translator->_('Изменить'),
			'remove' => $translator->_('Удалить'), 
			'removing' => $translator->_('Удаление'),   
			
			'upload' => $translator->_('Загрузка файла'),
			'upload2' => $translator->_('Загрузить с компьютера'),
			'upload3' => $translator->_('Загрузка файлов'),
			'uploadCat' => $translator->_('Каталог на сервере'),
			'file' => $translator->_('Файл'),
			'chooseFile' => $translator->_('Выберите файл'),
			'doUpload' => $translator->_('Загрузить'),
			'doUpload2' => $translator->_('Загрузить файл'),
			'doUpload3' => $translator->_('Загрузить несколько файлов'),
			'fileSelect' => $translator->_('Выбор файла'),
			'selectFile' => $translator->_('Выбрать из структуры'),
			'dirCreate' => $translator->_('Создать каталог'),
			'dirDelete' => $translator->_('Удалить каталог'),
			'fileDelete' => $translator->_('Удалить файл'),
			'fvTable' => $translator->_('В виде таблицы'),
			'fvPreview' => $translator->_('Эскизы'),
			'directories' => $translator->_('Каталоги'),
			'noFiles' => $translator->_('нет файлов'),
			'name' => $translator->_('Имя'),
			'date' => $translator->_('Дата'),
			'size' => $translator->_('Размер'),
			'recent' => $translator->_('Недавние'),

			'server' => $translator->_('Сервер'),
			'error' => $translator->_('Ошибка'),
			'more'  => $translator->_('Подробнее'),
			'material' => $translator->_('Материал'),
			'undelete' => $translator->_('Отменить удаление'),
			'noname' => $translator->_('- без заголовка -'),
			
			'picToBeDeleted' => $translator->_('Изображение будет удалено'),
			
			'off' 		=> $translator->_('отключен'),
			'do_off' 	=> $translator->_('Отключить'),
			'do_on' 	=> $translator->_('Включить'),
			'reload' 	=> $translator->_('Обновить'),
			'r_u_sure' 	=> $translator->_('Вы уверены?'),
			
			'addPlugin' 	=> $translator->_('Добавить плагин'),
			'installed' 	=> $translator->_('установлен'),
			'installed_f' 	=> $translator->_('установлена'),
			'version' 		=> $translator->_('Версия'),
			'author' 		=> $translator->_('Автор'),
			'needCms' 		=> $translator->_('CMS'),
			'any' 			=> $translator->_('любая'),
			'from' 			=> $translator->_('от'),
			'to' 			=> $translator->_('до'),
			'install' 		=> $translator->_('Установить'),
			'upgrade' 		=> $translator->_('Обновить/Переустановить'),
			'pluginInstall' => $translator->_('Установка/обновление'),
			'upgradeAvail' 	=> $translator->_('доступна свежая версия'),
			'loading' 		=> $translator->_('Загрузка ...'),
			'reloading'     => $translator->_('Перезагрузка ...'),   
			'wait'          => $translator->_('Подождите ...'),
			'toFrontOffice' => $translator->_('Перейти на сайт'),

);    

	$data = array_merge($data, $translator->getMessages());


echo json_encode($data);		 