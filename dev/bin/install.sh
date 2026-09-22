#!/bin/sh
# Проходит мастер установки CMS (cms/setup.php) теми же запросами, что шлёт его UI.
# Выполняется внутри php-контейнера (dev/dev.sh install). Если CMS уже установлена — ничего не делает.
#
# Шаги сервера (cms/include/setup.php): 2 — проверка окружения, 3 — доступ к БД и запись .prefs,
# 5 — структура и данные БД, 6 — администратор, 9 — стандартная тема и setup_done.
set -eu

URL=http://nginx/cms/include/setup.php?locale=ru
PREFS=/var/www/site/www/.prefs
LOGIN=${DEV_ADMIN_LOGIN:-admin}
PASSWORD=${DEV_ADMIN_PASSWORD:-admin}

if grep -q '^setup_done=1' "$PREFS" 2>/dev/null; then
    echo 'CMS уже установлена'
    exit 0
fi

JAR=$(mktemp)
trap 'rm -f "$JAR"' EXIT

# $1 — номер шага, дальше — поля формы; ошибкой считаем error:true или success:false в ответе
step() {
    n=$1; shift
    printf 'шаг %s... ' "$n"
    args="-d step=$n"
    for p in "$@"; do args="$args --data-urlencode $p"; done
    resp=$(curl -sS -b "$JAR" -c "$JAR" -H 'Host: localhost' $args "$URL")
    if printf '%s' "$resp" | grep -q -e '"error":true' -e '"success":false' || ! printf '%s' "$resp" | grep -q '^{'; then
        echo 'ошибка'
        printf '%s\n' "$resp" | sed 's/<[^>]*>/ /g'
        exit 1
    fi
    echo 'ok'
}

step 2
step 3 dbhost=db dbname=cetera dbuser=cetera dbpass=cetera
step 5
step 6 "login=$LOGIN" "password=$PASSWORD" "password2=$PASSWORD" email=admin@localhost
step 9 create=0

echo "CMS установлена, вход в админку: $LOGIN / $PASSWORD"
