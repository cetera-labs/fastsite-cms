---
layout: default
title: Поля материалов и редакторы полей
nav_order: 1
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Поля материалов и редакторы полей

У каждого типа материалов присутствует свой набор полей.

Текстовые поля:

* **Текстовый (1-65535 байт)** - максимальный размер поля задается на этапе создания
* **Большой текст (до 16 Мб)**
* **Огромный текст (до 4 Гб)**
* **Файл** - стандатрное текстовой поле, подразумевающее, что в нем будет хранится ссылка на файл, хранящийся на сервере

Числовые поля:

* **Целочисленный**
* **Действительное число**

Специальные поля:

* **Дата/Время**
* **Логическое**
* **Выбор** - значение поля может принималь только одно из определенных на этапе создания величин

Ссылочные поля:

* **Ссылка на другой материал** - значение поля - ссылка на уже существующий материал из определенного раздела в структуре сайта
* **Материал** - значение поля - ссылка на материал определенного типа, который создан только для данного поля
* **Пользователь** - значение поля - ссылка на пользователя системы
* **Ссылка на раздел** - значение поля - ссылка на раздел структуры
* **Ссылка на группу материалов** - значение поля - набор ссылок на уже существующие материалы одного типа из определенного раздела в структуре сайта
* **Ссылка на группу материалов 2** - значение поля - набор ссылок на уже существующие материалы любых типов в структуре сайта
* **Группа материалов** - значение поля - материалы одного типа, созданные только для данного поля
* **Ссылка на разделы** - значение поля - набор ссылок на разделы в структуре сайта
* **Ключевые слова** - значение поля - набор ссылок на материалы типа "Ключеные слова"
* **Набор пользователей** - значение поля - группа пользователей системы

## Редакторы полей
 

Для каждого поля возможен выбор редактора, с помощью которого устанавливается значение поля в окне редактирования материала.

**Однострочный редактор текста**

Стандартное текстовое поле ввода. Доступен для всех текстовых полей.

**Однострочный редактор текста (только лат. и цифр. символы)**

Стандартное текстовое поле ввода с органичением вводимых символов. Доступен для всех текстовых полей.

**Многострочный редактор текста**

Стандартное многострочное текстовое поле ввода. Доступен для всех текстовых полей.

**Пароль**

Стандартное текстовое поле ввода со скрытием вводимого значения. Доступен для всех текстовых полей.

**CKEditor**

Визуальный редактор. Занимает отдельную вкладку в окне редактирования материалов. Доступен для всех текстовых полей.

**CKEditor малый**

Визуальный редактор уменьшенного размера. Доступен для всех текстовых полей.

**Выбор файла**

Позволяет выбирать файлы, загруженные на сервер. Имеется возможность загрузки своих файлов. Доступен для поля "Файл".

**Выбор рисунка**

Позволяет выбирать изображения, загруженные на сервер. Имеется возможность загрузки своих изображений. Имеется функция кадрирования. Доступен для поля "Файл".

**Ввод числовых значений**

Стандартное текстовое поле ввода с ограничением вводимых значений. Доступен для числовых полей.

**Редактирование действительных чисел**

Стандартное текстовое поле ввода с ограничением вводимых значений. Доступен для числовых полей.

**Флажок (Checkbox)**

Поле в виде флажка Checkbox. Доступен для поля "Логическое".

**Выбор да/нет (Radio)**

Поле в виде двух радиокнопок. Доступен для поля "Логическое".

**Выпадающий список**

Стандартное поле типа Combobox. Доступен для полей "Выбор", "Пользователь".

**Выбор даты/времени**

Позваляем выбрать дату с помощью выпадающего календаря. Доступен для поля "Дата/время".

**Выбор материала из структуры**

Дает возможность материала из структуры. Доступен для поля "Ссылка на другой материал".

**Выбор группы материалов**

Дает возможность выбора материалов из структуры, их сортировки. Доступен для поля "Ссылка на группу материалов", "Ссылка на группу материалов 2".

**Выбор группы материалов флажками**

Дает возможность выбора материалов из структуры помечая их флажками. Доступен для поля "Ссылка на группу материалов".

**Выбор группы материалов с возможностью редактирования**

Дает возможность выбора материалов из структуры, их сортировки и редактирования. Доступен для поля "Ссылка на группу материалов".

**Группа материалов**

Дает возможность создания, удаления, сортировки материалов. Доступен для поля "Группа материалов".

**Редактируемая группа материалов**

Дает возможность создания, удаления, сортировки материалов. Занимает отдельную вкладку в окне редактирования материалов. Доступен для поля "Группа материалов".

**Выбор пользователя**

Дает возможность выбора пользователя. Доступен для поля "Пользователь".

**Выбор пользователей**

Дает возможность выбора и сортировки пользователей. Доступен для поля "Набор пользователей".

**Редактирование материала**

Возможность создания материала. Доступен для поля "Материал".

**Редактирование ключевых слов**

Редактор в виде многострочного текстового поля. Позволяет вводить ключевые слова через ",". Регистр игнорируется, т.е. если в одном материале написать Test, а в другом test, то оба пометятся как Test. Доступен для поля "Ключевые слова".