---
layout: default
title: Comments.Add
nav_order: 8
parent: Виджеты
grand_parent: Руководство разработчика
---

# Comments.Add

В модуле «Комментарии» это виджет формы для комментирования материала .

## Пример вызова Twig

	{% raw %}{% widget 'Comments.Add' with { material: article } %}{% endraw %}
 

Параметр | Описание
---|---
**template**|Шаблон вывода
**material**|Материал, к которому оставлять комментарий. Объект класса \Cetera\Material
**material_type**|Тип материала
**material_id**|ID материала
**catalog**|Раздел, в котором находится материал
**material_alias**|Алиас материала
**rating**|`TRUE|FALSE]` Возможность давать оценку материалу. По умолчанию **TRUE**
**rating_text**|Текст поля для оценки материала
**ajax**|`[TRUE|FALSE]` Отправка комментария без перезагрузки страницы. По умолчанию **FALSE**
**submit_text**|Текст на кнопке отправки комментария. По умолчанию «Отправить сообщение»
**success_text**|Сообщение об успешной отправке комментария. По умолчанию «Ваш комментарий принят»
**recaptcha**|`[TRUE|FALSE]` Использовать Google reCAPTCHA. По умолчанию **FALSE**. **Не работает при включенном ajax!**
**recaptcha_site_key**|reCAPTCHA ключ
**recaptcha_secret_key**|reCAPTCHA секретный ключ

Для того, чтобы выбрать материал, к которому будут отправляться комментарии, используйте параметры:

* **material** — явная передача материала
* **material_type и material_id** — поиск материала по типу и ID или
* **catalog и material_alias** — поиск материала по разделу и алиасу