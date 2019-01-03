---
layout: default
title: Работа с изображениями
nav_order: 8
parent: Cetera CMS изнутри
grand_parent: Руководство разработчика
---

# Работа с изображениями

В CeteraCMS с версии v3.29.3 добавлен новый класс для манипуляции с изображениями — \Cetera\ImageTransform.

Поддерживается механизм генерации картинок «на лету». Чтобы его задействовать, стройте путь к картинкам по следующему шаблону:

/imagetransform/<параметры ресайза>/<путь к оригинальной картинке>

<параметры ресайза> строятся по принципу параметр1_значение1_параметр2_значение2 и т.д.

<путь к оригинальной картинке> — относительно корня сервера, без «/»

## Параметры

* width — требуемая ширина
* height — требуемая высота
* quality — качество результата (степень сжатия) [0..100]. По умолчанию 100 - максимальное качество.
* enlarge — принудительно увеличивать изображение. По умолчанию 1
* aspect — сохранять соотношение сторон. По умолчанию 1
* watermark_N — добавить водяной знак конфигурации N, определенной в .prefs (см. ниже)
* fit — вписать картинку точно в указанные width и height при сохранении aspect ratio. По умолчанию 0.

## Настройка водяных знаков:

Предусмотрено создание неограниченного количества конфигураций для добавления водяных знаков к картинкам. Каждая конфигурация описывается в файле .prefs в секции [watermark_N],где N — номер конфигурации. Параметры конфигурации:

* src — файл с изображением водяного знака (jpeg или png)
* alpha — прозрачность от 0 до 100 (по умолчанию - 0)
* left — отступ по горизонтали, положительные числа - отступ слева, отрицательные — справа, 0 — центрировать (по умолчанию — 0)
* top — отступ по вертикали, положительные числа - отступ сверху, отрицательные — снизу, 0 — центрировать (по умолчанию - 0)
* size — масштабирование водяного знака. 0 — исходные размеры, 1 — растягивается до размеров целевого изображения, без сохранения пропорций, 2 — растягивается до размеров целевого изображения, с сохранением пропорций вариант А, 3 — растягивается до размеров целевого изображения, с сохранением пропорций вариант Б, 4 — целевое изображение полностью заливается водяными знаками. (по умолчанию — 0)
* width — ширина водяного знака (работает только при size=0 или size=4)
* height — высота водяного знака (работает только при size=0 или size=4)
* nostore — 1 — не кэшировать результат. Используется только для отладки, т.к. при включенном кэшировании изменение параметров не будет влиять на уже закэшированные картинки. (по умолчанию — 0)

---
### Важно!

Параметры left,top,width,height можно указывать в % от основного изображения.

---

Пример:

[watermark]
src=/images/logo.png
left=-20
top=-20
alpha=50
 
[watermark_2]
src=/images/logo_b.png
alpha=15
size=4

## Примеры:

Оригинал
https://cetera.ru/images/manager.png 675px × 249px

![675px × 249px](https://cetera.ru/images/manager.png)

Уменьшить до 150px по ширине
https://cetera.ru/imagetransform/width_150/images/manager.png

![150px](https://cetera.ru/imagetransform/width_150/images/manager.png)

Уменьшить до 100px по высоте
https://cetera.ru/imagetransform/height_100/images/manager.png

![100px](https://cetera.ru/imagetransform/height_100/images/manager.png)

Сделать 200px x 200px
https://cetera.ru/imagetransform/width_200_height_200_fit_1/images/manager.png

![200px x 200px](https://cetera.ru/imagetransform/width_200_height_200_fit_1/images/manager.png)

Сделать 200px x 200px, но центрировать
https://cetera.ru/imagetransform/width_200_height_200_fit_2/images/manager.png

[200px x 200px](https://cetera.ru/imagetransform/width_200_height_200_fit_2/images/manager.png)

Сделать 200px x 200px, но ничего не обрезать
https://cetera.ru/imagetransform/width_200_height_200_fit_3/images/manager.png

[200px x 200px](https://cetera.ru/imagetransform/width_200_height_200_fit_3/images/manager.png)

Наложить водяной знак
https://cetera.ru/imagetransform/watermark/images/manager.png

[Водяной знак](https://cetera.ru/imagetransform/watermark/images/manager.png)

Наложить водяной знак 2, и сделать 400×400
https://cetera.ru/imagetransform/watermark_2_width_400_height_400_fit_2/images/manager.png

[Водяной знак](https://cetera.ru/imagetransform/watermark_2_width_400_height_400_fit_2/images/manager.png)

Запросто комбинируем водяные знаки
https://cetera.ru/imagetransform/watermark_2_watermark/images/manager.png

[Водяной знак](https://cetera.ru/imagetransform/watermark_2_watermark/images/manager.png)