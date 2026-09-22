# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Что это

Fastsite CMS (бывш. Cetera CMS) — PHP-CMS/eCommerce. Репозиторий — composer-пакет `fastsite-labs/fastsite-cms` (type `library`),
который ставится в `vendor/cetera-labs/cetera-cms` сайта (на Packagist — под старым именем `cetera-labs/cetera-cms`).
Тестов, линтеров и CI в репозитории нет.
Задачи ведутся в Jira-проекте **CCTM** («NA 8 CeteraCMSTM»): https://pm.cetera.ru/projects/CCTM
(REST: `https://pm.cetera.ru/rest/api/2/...`, ключи задач `CCTM-N`).
Документация для разработчиков (на русском) — `docs/docs/developer-guide/` (виджеты, плагины, темы, внутреннее устройство).

## Локальный запуск

`sh dev/dev.sh up` (или `make up`) — Docker-окружение с чистым сайтом, на который установлена CMS из репозитория:
сайт http://localhost:8090/, админка `/cms/` (`admin` / `admin`), почта http://localhost:10081/. Правки PHP/JS/Twig видны
сразу, `dev.sh build` нужен после изменения CSS старого UI или `composer.json`, `dev.sh reset` — переустановка с нуля.
Подробности — `dev/README.md`. `make` на Windows нет, поэтому основная команда — `dev/dev.sh` из Git Bash.

## Сборка

- PHP: `composer install` (PHP >= 8.1). Пакет `cetera-labs/library` скачивается zip-архивом с cms.cetera.ru (ExtJS 4, ace, cropper и пр.).
- `build.xml` (Phing, target `dist`) — деплой в соседний сайт: пути относительные (`../../../www/cms`, `../library`),
  т.е. рассчитан на запуск из `www/../vendor/cetera-labs/cetera-cms`. Склеивает `cms/js/vendor.js`, `cms/css/global.css`,
  копирует `cms/*` в `www/cms`, собранный UI из `back-office/build` и статику включённых плагинов (через `\Cetera\Phing`).
- Новый back-office (`back-office/`, Ext JS 7 + webpack + Sencha ext-webpack-plugin):
  `npm run dev:desktop` (dev-сервер), `npm run build:desktop` (прод-сборка в `back-office/build/cms/ui` + `ui.html`).
  Собранный `back-office/build/` закоммичен — после изменений в `back-office/app` нужно пересобирать.
  Подводные камни сборки back-office:
  - `back-office/.npmrc` задаёт `legacy-peer-deps=true`: без него npm 7+ ставит внутрь `@sencha/ext-webpack-plugin` лишний webpack 4 (peer от старого html-webpack-plugin).
  - Пакеты `@sencha/*` берутся из публичного GPL-фида `sencha.myget.org` (прописан в lock-файле).
  - Postinstall `@sencha/cmd` на Node 20+ молча не срабатывает (spawn `npm.cmd` без shell), и сборка падает с `sencha.exe ENOENT`.
    Поставить вручную: в `node_modules/@sencha/cmd` выполнить `npm install --no-save @sencha/cmd-windows-64-jre@7.0.0 --@sencha:registry=https://sencha.myget.org/F/gpl/npm/`,
    затем `node -e "require('@sencha/cmd-windows-64-jre/install.js')(process.cwd())"`.
  - Sencha Cmd 7.0 виснет на «Processing Build Descriptor», потому что не может дочитать каталог `cdn.sencha.com`. Нужные пакеты лежат локально, так что удалённые репозитории можно отключить:
    `node_modules/@sencha/cmd/dist/sencha repo remove -n sencha` (и `-n sencha-beta`).
  - Прод-сборка перезаписывает исходные `back-office/index.html` (это шаблон HtmlWebpackPlugin) и `main.js`, а `rimraf build` удаляет закоммиченный `build/` —
    не коммитить эти побочные изменения без необходимости (`git checkout -- index.html main.js`).

## Архитектура

**Два режима установки** (`cms/include/path_detect.php`): если существует `DOCROOT/../vendor/cetera-labs/cetera-cms` —
`COMPOSER_INSTALL=true`, `CMSROOT` указывает в vendor; иначе — legacy-режим с `DOCROOT/library`. Весь код должен
работать через константы (`DOCROOT`, `CMSROOT`, `CMS_DIR`, `VENDOR_PATH` и т.д. из `cms/include/constants.php`), а не через жёсткие пути.
Конфиг сайта — ini-файл `DOCROOT/.prefs` (+ `.prefs.local`), читается через `$application->getVar()`; параметры описаны в `FAQ.txt`.

**PHP-ядро** — `cms/include/classes/Cetera/` (PSR-0, namespace `Cetera\`). Центральный синглтон —
`Cetera\Application` (~2000 строк): БД (Doctrine DBAL/ORM), сессия, аутентификация (laminas-authentication), роутер (laminas-router),
переводчик, кэш, плагины, виджеты, текущий сервер/раздел/пользователь. Доменная модель: `Server` → `Catalog`/`Section`
(дерево nested set, `CDBTree`, таблица `dir_structure`) → `Material` (`DynamicFieldsObject` с полями, заданными типами
материалов `ObjectDefinition`/`ObjectField`). Выборки — через `Iterator/*`.

**Точки входа** (`cms/`):
- `fo.php` — фронт-офис. Регистрирует маршруты (`/imagetransform/...`, `/api/entity/...`, `/api/...` → `Cetera\Api\*Controller`),
  инициализирует приложение, затем выполняет шаблоны активной темы (`bootstrap.php` темы, классы темы из `<theme>/classes`),
  контроллеры по совпавшему маршруту.
- `index.php` — back-office; `include/common_bo.php` — bootstrap BO с проверкой `allowBackOffice()`.
- Старый BO API — плоские скрипты `cms/include/data_*.php` (чтение, JSON для ExtJS-сторов) и `cms/include/action_*.php` (изменения).
- `include/common.php` — общий bootstrap (автозагрузка, константы, `Application::getInstance()`).

**Два UI back-office:** `cms/app/` + `cms/app.js` — старый ExtJS 4 (идёт в `cms/js/app.js` через Phing);
`back-office/app/{desktop,modern,shared}` — новый Ext JS 7 (namespace `Cetera`). Компоненты часто продублированы в обоих
(например `cms/app/field/*` ↔ `back-office/app/desktop/src/field/*`) — при правке проверять, нужно ли менять оба.

**Виджеты:** PHP-классы `Cetera\Widget\*` (наследуют `Widget\Widget`, шаблонные — от `Widget\Templateable`) +
Twig-шаблоны `cms/twig_templates/widgets/<name>/` + редакторы в BO (`widget/*.js`). Получение — `Application::getWidget($name, $params)`,
регистрация — `registerWidget()`; в Twig — тег `{% widget 'Name' with {...} %}` (`Twig/TokenParser/Widget.php`, `Twig/Node/Widget.php`).

**Плагины** — каталоги в `DOCROOT/plugins/<name>` или composer-пакеты типа `cetera-cms-plugin` (`cms/plugins`).
Структура: `info.json`, `config.php` (подключается в `Application::initPlugins()`, там регистрируются виджеты, меню BO и т.п.),
`install.php`, `schema.xml`, `classes/`, `ext/` (JS для BO), `widgets/`, `lang/`. Темы — `DOCROOT/themes/<name>` с аналогичной схемой.

**Схема БД** описывается XML (`cms/.dbschema/core.xml`, `schema.xml` плагинов/тем) и приводится к реальной БД через
`Cetera\Schema` (сравнение и генерация ALTER-запросов, BO-панель «Repair»). При изменении таблиц ядра правится `core.xml`, а не миграции.

**Локализация:** исходные строки — на русском (`$translator->_('...')`), переводы — `cms/lang/`.
