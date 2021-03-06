---
layout: default
title: Модуль «Веб-формы»
nav_order: 2
parent: Модули
grand_parent: Руководство пользователя
---

# Модуль «Веб-формы»

## Установка плагина

1. В меню «Навигация» выбрать пункт «Сервисы» → «Плагины».
2. Нажать кнопку «Добавить плагин».
3. Выбрать плагин «Веб формы» в списке и нажать кнопку «Установить».
4. После успешной установки плагина, в меню «Навигация» → «Сайт» появится пункт «Конструктор форм».
5. Если доступны обновления формы, рядом с плагином появится текст «доступна свежая версия». Для обновления надо нажать кнопку «Обновить/Переустановить». Всегда следует устанавливать последние обновления. Список созданных форм при обновлении сохраняется, но всегда стоит проверить работу всех форм на сайте после обновления.

## Создание новой веб-формы

1. Выбрать в меню «Навигация» пункт «Сайт» → «Конструктор форм».
2. Нажать кнопку «Добавить» в верхнем меню.
3. В появившемся окне доступны три вкладки: «Шаблон формы», «Письмо» и «Уведомления».

## Редактирование шаблона формы

На вкладке «Шаблон формы» в режиме визуального редактора доступны следующие стандартные типы полей:

* Text — однострочное текстовое поле;
* Email — однострочное текстовое поле для ввода E-mail;
* Checkbox — поле-флажок с множественным выбором;
* Radio — поле радиокнопка с единичным выбором (пример установки: <label>[radio name | value="Да" checked="checked"] Да</label> <label>[radio name | value="Нет" ] Нет</label>);
* Textarea — многострочное текстовое поле;
* File — поле для файлов;
* Captcha Img — CAPTCHA-изображение;
* Captcha Strong Img – CAPTCHA-изображение с дополнительным условием (необходимо вручную добавить в форму текст под картинкой — «вводите код с картинки без первого и последнего символа» - для лучшей защиты от спама);
* Captcha Input — поле для ввода кода CAPTCHA;
* Recaptcha — CAPTCHA от Google;
* Select — выпадающий список;
* Submit — кнопка отправки формы (<input type=”submit” />);
* Submit Btn — кнопка отправки формы {{%% raw %%}}(<button type=”submit”>HTML код</button);{{%% endraw %%}}
* Form error — ошибки формы;
* Form result — результат работы формы.

{{%% raw %%}}При клике на необходимый тип поля (кнопки над визуальным редактором кода шаблона), откроется окно, в котором необходимо задать: имя (допускается использование латинских букв и цифр), тип поля (обязательное/необязательное), варианты значений (для поля типа Select), плейсходер (для полей типа Text, Email, Textarea), имя класса (возможно несколько значений через пробел). 

Использование типов Submit и Submit Btn не является обязательным, их можно заменить на любую подобную html-конструкцию (включая javascript-сценарии). В таком случае, следует учитывать вариант установки нескольких веб форм на одну страницу.

Для установки Recaptcha, необходимо перейти по ссылке, нажать на кнопку "Get reCaptcha", в открывшейся форме указать адрес сайта и тип "reCaptcha V2", после чего будет сгенерирован Public key и Private key, которые надо указать в настройках формы.

Вставка выбранного поля происходит на текущее место установки курсора в шаблоне формы. В шаблоне можно использовать любой html-код для разметки, код стандартных полей заключается между квадратных скобок [код поля].

{{%% raw %%}}Его не следует изменять напрямую (за исключением указания дополнительных атрибутов и JS-кода — их можно дописать после символа «\|» — все данные попадают в код формы без преобразования).{{%% endraw %%}}

Для удаления поля из шаблона формы, следует удалить код поля, включая квадратные скобки.

## Настройка отправки e-mail оповещений

Доступна отправка результатов формы в 2-х разных вариантах писем (одно можно использовать для оповещения посетителя, другое – для администратора). 

Шаблоны писем доступны для редактирования на вкладке «Письмо».

В шаблоне письма можно использовать любое значения поля из формы. Для подстановки введенного в форме значения в письмо, следует использовать конструкцию [имя_поля] (обратите внимание — формат поля в шаблоне письма отличается от формата поля в шаблоне формы). 

Имя поля должно совпадать с именем поля, указанным в шаблоне формы. После заполнения формы, указанные конструкции в шаблоне будут заменены введенными значениями. Доступны следующие константы и конструкции:

* server.name — имя сайта;
* server.url — URL сайта;
* server.alias — алиас сервера;
* userVar(‘имя переменной’) — значение пользовательской переменной.

При выборе чекбокса «Отправлять два письма», отобразятся настройки для отправки второго письма, аналогичные первому. 

При выборе опции «Отправлять файлы во вложении», к письму будут приложены все файлы, которые отправил пользователь при заполнении формы, использование в шаблоне письма конструкции [имя_поля] для типа файлов игнорируется, файлы прикладываются вложениями.

## Настройка текста уведомлений

Во вкладке «Уведомления» можно указать стандартные текстовые константы для следующих значений:

* Ошибки заполнения.
* Некоторое поле должно быть заполнено.

## Создание виджета формы

Для отображения веб-формы на странице, необходимо создать соответствующий виджет. Для разных форм следует создавать отдельные виджеты. Одну и ту же форму можно использовать на разных страницах, создавать отдельные виджеты для одной и той же формы нет необходимости. 

1. Перейти на страницу «Виджеты»
2. Нажать на кнопку «Новый виджет»
3. Выбрать тип виджета «Веб-форма»
4. В открывшемся окне ввести название виджета и выбрать нужную веб-форму из списка. 
5. Сохранить изменения

## Вставка виджета формы на страницу

В Fastsite CMS доступны два варианта включения виджетов на страницу: в контенте страницы и добавление виджета в область.

## Вставка виджета в контент страницы

Для вставки виджета непосредственно в код страницы, необходимо использовать тег:

`<cms action=«widget» widgetname=«forms» widgetparams=«form=FORM_ID»></cms>

где FORM_ID — идентификатор формы.

## Добавление виджета в область

1. Открыть в меню «Навигация» пункт «Сайт» → «Области».
2. Добавить область или выбрать существующую.
3. Нажать на кнопку «Создать виджет».
4. В списке выбрать виджет «Веб-формы»
5. В настройках виджета выбрать ID нужной формы.