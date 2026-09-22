#!/bin/sh
# Управление локальным окружением CMS, см. dev/README.md.
# Работает из Git Bash, WSL и Linux; нужен только docker compose.
set -eu

# Git Bash иначе превращает /var/www/... в пути Windows; поэтому и путь
# к каталогу берём в виде D:/... (pwd -W), который docker понимает сам
export MSYS_NO_PATHCONV=1
DIR=$(cd "$(dirname "$0")" && { pwd -W 2>/dev/null || pwd; })

dc() { docker compose -f "$DIR/docker-compose.yml" "$@"; }
php_exec() { dc exec php "$@"; }

usage() {
    cat <<'EOF'
Использование: dev/dev.sh <команда>

  up       поднять окружение; при первом запуске собрать сайт и установить CMS
  down     остановить окружение (данные сохраняются)
  reset    удалить сайт и БД и поставить CMS заново
  build    пересобрать сайт: composer install, www/cms, css/global.css, js/vendor.js
  install  пройти мастер установки CMS, если она ещё не установлена
  test     запустить тесты (PHPUnit), аргументы передаются phpunit: dev/dev.sh test --filter Http
  bash     shell в php-контейнере (/var/www/site)
  mysql    консоль MySQL
  logs     логи контейнеров (docker compose logs -f)
  ps       состояние контейнеров
  php ...  выполнить php в контейнере, например: dev/dev.sh php -v
EOF
}

cmd=${1:-}
[ $# -gt 0 ] && shift

case $cmd in
    up)
        dc up -d
        if ! php_exec test -f /var/www/site/vendor/autoload.php; then
            php_exec sh vendor/cetera-labs/cetera-cms/dev/bin/build.sh
        fi
        php_exec sh vendor/cetera-labs/cetera-cms/dev/bin/install.sh
        echo
        echo "Сайт:    http://localhost:${DEV_HTTP_PORT:-8090}/"
        echo "Админка: http://localhost:${DEV_HTTP_PORT:-8090}/cms/  (admin / admin)"
        echo "Почта:   http://localhost:${DEV_MAIL_PORT:-10081}/"
        ;;
    down)    dc down ;;
    reset)   dc down -v && "$0" up ;;
    build)   php_exec sh vendor/cetera-labs/cetera-cms/dev/bin/build.sh ;;
    install) php_exec sh vendor/cetera-labs/cetera-cms/dev/bin/install.sh ;;
    test)    php_exec php vendor/bin/phpunit -c vendor/cetera-labs/cetera-cms/phpunit.xml.dist "$@" ;;
    bash)    php_exec bash ;;
    mysql)   dc exec db mysql -ucetera -pcetera cetera ;;
    logs)    dc logs -f "$@" ;;
    ps)      dc ps ;;
    php)     php_exec php "$@" ;;
    *)       usage; [ -z "$cmd" ] || exit 1 ;;
esac
