---
layout: default
title: Material
nav_order: 12
parent: Виджеты
grand_parent: Руководство разработчика
---

# Material

Показывает материал

*CeteraCMS 3.22+*

## Пример вызова в PHP

	\Cetera\Application::getInstance()->getWidget('Material')->display();
 

## Пример вызова в Twig

	{% raw %}{% widget 'Material' with { share_buttons: 1 } %}{% endraw %}
 

## Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**material_type**|ID типа материалов, которому принадлежит материал. Если параметр не указан, то материал ищется в разделе. По умолчанию **null**
**catalog**|Раздел из которого брать материал. По умолчанию — текущий раздел
**material_id**|ID материала . По умолчанию *null*
**material_alias**|alias материала. По умолчанию используется Application::getUnparsedUrl()
**share_buttons**|`true|false` Показать кнопки расшаривания материалов в соцсети. По умолчанию **false**
**show_meta|`true|false` Передавать meta информацию в head страницы. По умолчанию **false**
**show_pic|`true|false` Показать иллюстрацию (поле pic у материала). По умолчанию **false**