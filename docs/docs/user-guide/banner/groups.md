---
layout: default
title: Группы баннеров
nav_order: 3
parent: Плагин «Баннерная система»
grand_parent: Руководство пользователя
---

# Группы баннеров

Все баннеры, ротирующиеся на сайте, разбиваются на несколько групп. *Группа* — это набор баннеров определённого размера, предназначенных для показа в определённом месте на сайте с заданной очередностью.

Группы баннеров расположены в левом нижнем фрейме окна страницы.

Свойства группы баннеров:

![Пример]({{site.baseurl}}/images/p-2.png)

1. Имя — Название группы, не более 128 символов, например «Небоскрёбы в правой части страниц»;

2. Псевдоним (alias) — служит для идентификации группы, не более 16 латинских букв и цифр, например «right»:

3. Очерёдность показов баннеров группы:

	1. *случайная* — баннеры показываются в случайном порядке;

	2. *случайная с учётом «веса» баннера* — баннеры показываются в случайном порядке, вероятность показа того или иного баннера задаётся числовым значением в свойствах баннера;

	3. *циклическая* — баннеры показываются по очереди, один за другим.

4. Метод показа:

	1. *обычный* — код баннера напрямую встраивается в HTML-код страницы;

	2. *через Javascript* — в код страницы внедряется Javascript-сценарий, с помощью которого осуществляется вывод кода баннера;

	3. *через IFRAME* — в код страницы внедряется тег \<IFRAME\>, содержащий код баннера.