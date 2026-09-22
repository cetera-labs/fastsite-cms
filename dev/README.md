# Локальное окружение для разработки CMS

Docker-окружение, в котором CMS из этого репозитория установлена на чистый сайт.
Нужен только Docker (Docker Desktop на Windows). Команды запускаются из корня репозитория
в Git Bash, WSL или Linux: `sh dev/dev.sh <команда>` или `make <команда>`.

```sh
sh dev/dev.sh up      # первый запуск: composer install, сборка www, установка CMS (~1,5 мин)
```

| Адрес | Что там |
|---|---|
| http://localhost:8090/ | сайт (фронт-офис) |
| http://localhost:8090/cms/ | админка, вход `admin` / `admin` |
| http://localhost:10081/ | mailcatcher — все письма сайта |
| `127.0.0.1:53307` | MySQL, база/логин/пароль `cetera` |

Порты меняются переменными `DEV_HTTP_PORT`, `DEV_MAIL_PORT`, `DEV_DB_PORT`,
версия PHP — `DEV_PHP_VERSION` (тег образа `registry.cetera.su/boilerplate/docker/php`, по умолчанию `8.4-fpm`).

## Команды

| Команда | Что делает |
|---|---|
| `up` | поднять окружение; при первом запуске собрать сайт и установить CMS |
| `down` | остановить (сайт и БД сохраняются) |
| `reset` | удалить сайт и БД и поставить CMS заново |
| `build` | пересобрать сайт: composer install, `www/cms`, `css/global.css`, `js/vendor.js` |
| `install` | пройти мастер установки, если CMS ещё не установлена |
| `test ...` | тесты; аргументы уходят в phpunit, например `sh dev/dev.sh test --filter Http` |
| `bash`, `mysql`, `logs`, `ps` | shell в php-контейнере, консоль MySQL, логи, состояние |
| `php ...` | php в контейнере, например `sh dev/dev.sh php -v` |

## Тесты

PHPUnit 10 (ставится в dev-сайт), конфиг `phpunit.xml.dist`, тесты в `tests/`. Прогон занимает ~20 секунд.
Набор `smoke` проверяет CMS, установленную в окружении:

| Тест | Что ловит |
|---|---|
| `PhpSyntaxTest` | `php -l` по всем PHP-файлам: синтаксис и ошибки компиляции на текущей версии PHP |
| `ClassLoadTest` | загрузку каждого класса ядра: несовместимость с родителем/интерфейсом, нереализованные абстрактные методы. Классы грузятся в дочернем процессе (`tests/bin/load-classes.php`), потому что такие ошибки фатальные |
| `TwigTemplatesTest` | компиляцию Twig-шаблонов ядра с тегами и фильтрами CMS |
| `SchemaTest` | совпадение БД после установки с XML-схемами — как панель «Repair» |
| `HttpTest` | фронт-офис, вход в админку, стартовую страницу и `data_*.php` админки с параметрами, как их шлёт UI |

### Проверки в браузере (e2e)

`sh dev/dev.sh e2e` — Playwright 1.58 в контейнере `e2e` (образ `mcr.microsoft.com/playwright`, `node_modules` — в docker-томе),
сценарии в `e2e/tests/`, прогон ~50 секунд. Каждый сценарий падает при JS-ошибке на странице или ответе сайта с кодом >= 400.
Покрыто: главная сайта, вход и выход из админки, неверный пароль, открытие каждого раздела навигации старой админки.
Отчёт с трассами упавших сценариев — `e2e/playwright-report/index.html`, скриншоты разделов — `e2e/test-results/sections/`.

Новый back-office (`/cms/ui.html`) пока не проверяется: в репозитории `back-office/build/ui.html` пустой с 2021 года.

Другую версию PHP можно проверить так: `DEV_PHP_VERSION=8.2-fpm sh dev/dev.sh reset && sh dev/dev.sh test`.

## Как устроено

- Сайт лежит в docker-томе `site` (`/var/www/site`): `composer.json` из `dev/site`, `vendor`, `www`, `.prefs`.
- Репозиторий подмонтирован в `vendor/cetera-labs/cetera-cms` — туда, где CMS стоит на обычных сайтах,
  поэтому работает composer-режим установки (`cms/include/path_detect.php`). Зависимости ставятся
  composer'ом из `composer.json` репозитория.
- `www/cms` собирает `dev/bin/build.sh` по образцу `build.xml`, но из симлинков на `cms/` репозитория:
  **правки PHP, JS и Twig видны сразу**. Пересобирать (`build`) нужно только после изменения
  `cms/app/**/*.css` и `cms/css/main.css`/`setup.css` (склеиваются в `global.css`) или `composer.json`.
- Новый back-office берётся из `back-office/build` — после `npm run build:desktop` виден без пересборки сайта.
- CMS ставится `dev/bin/install.sh`: он проходит мастер `cms/setup.php` теми же запросами, что его UI,
  без установки темы (сайт с темой по умолчанию).
- nginx — образ и конфиг ceteracms из boilerplate сайтов, php-fpm — образ boilerplate.

## Подводные камни

- Docker Desktop на Hyper-V при первом монтировании каталога спрашивает разрешение на доступ к файлам.
  Если `up` висит на старте контейнеров, проверьте, нет ли такого окна; если нет — перезапустите Docker Desktop.
