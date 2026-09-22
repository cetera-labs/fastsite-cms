#!/bin/sh
# Выпуск версии CMS: sh dev/release.sh 3.81.0
# Меняет VERSION в cms/include/common.php, коммитит, ставит тег с тем же номером
# и пушит master вместе с тегом (Packagist подхватывает тег сам).
set -eu

FILE=cms/include/common.php

die() { echo "$*" >&2; exit 1; }

[ $# -eq 1 ] || die "Использование: sh dev/release.sh X.Y.Z"
NEW=$1
echo "$NEW" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$' || die "Версия должна быть вида X.Y.Z, без префикса v: $NEW"

cd "$(dirname "$0")/.."

[ "$(git rev-parse --abbrev-ref HEAD)" = master ] || die "Релиз выпускается из master"
git diff --quiet HEAD -- "$FILE" || die "В $FILE есть незакоммиченные изменения"

git fetch -q --tags origin
[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/master)" ] || die "master расходится с origin/master — сначала git pull --rebase / git push"
git rev-parse -q --verify "refs/tags/$NEW" >/dev/null && die "Тег $NEW уже существует"

CUR=$(sed -n "s/^define('VERSION', '\([^']*\)');.*/\1/p" "$FILE")
[ -n "$CUR" ] || die "Не нашёл define('VERSION', ...) в $FILE"
[ "$(printf '%s\n%s\n' "$CUR" "$NEW" | sort -V | tail -1)" = "$NEW" ] && [ "$CUR" != "$NEW" ] \
    || die "Новая версия $NEW должна быть больше текущей $CUR"

sed -i "s/^define('VERSION', '$CUR');/define('VERSION', '$NEW');/" "$FILE"
git add "$FILE"
git commit -q -m "Версия $NEW"
git tag "$NEW"
git push -q --atomic origin master "$NEW"

echo "Выпущена версия $NEW ($CUR -> $NEW)"
