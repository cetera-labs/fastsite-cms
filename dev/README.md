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
| `bash`, `mysql`, `logs`, `ps` | shell в php-контейнере, консоль MySQL, логи, состояние |
| `php ...` | php в контейнере, например `sh dev/dev.sh php -v` |

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
