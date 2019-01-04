---
layout: default
title: Sitemap

nav_order: 9
parent: Виджеты
grand_parent: Руководство разработчика
---

# Sitemap

Описание модуля

## Виджет sitemap

### Пример вызова

	\Cetera\Application::getInstance()->getWidget('sitemap', array(
	        'showMaterial' => 0,
	        'showMain'     => 0,
	        'maxLevel'     => 0,
	        'nofollow'     => true,
	))->display();
 
### Описание параметров

Параметр | Описание
---|---
**template**|Шаблон вывода
**showMaterial**|Выводить материалы в карте. По умолчанию **0**
**showMain**|Выводить главную страницу. По умолчанию **1**
**maxLevel**|Максимальный уровень вложенности. 0 - без ограничения. По умолчанию **0**
**nofollow**|Использовать аттрибут *nofollow* в ссылках