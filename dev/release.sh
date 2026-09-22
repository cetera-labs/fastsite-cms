#!/bin/sh
# Выпуск версии CMS: sh dev/release.sh 3.81.0 [--dry-run]
# Меняет VERSION в cms/include/common.php, дописывает в начало Changelog.txt раздел версии
# (заголовки коммитов с прошлого тега), коммитит, ставит тег с тем же номером
# и пушит master вместе с тегом (Packagist подхватывает тег сам).
# С --dry-run только показывает раздел Changelog.txt, ничего не меняя.
set -eu

FILE=cms/include/common.php
CHANGELOG=Changelog.txt

die() { echo "$*" >&2; exit 1; }

DRY=
case "${2:-}" in
    '') ;;
    --dry-run) DRY=1 ;;
    *) die "Неизвестный параметр: $2" ;;
esac
[ $# -ge 1 ] && [ $# -le 2 ] || die "Использование: sh dev/release.sh X.Y.Z [--dry-run]"
NEW=$1
echo "$NEW" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$' || die "Версия должна быть вида X.Y.Z, без префикса v: $NEW"

cd "$(dirname "$0")/.."

[ "$(git rev-parse --abbrev-ref HEAD)" = master ] || die "Релиз выпускается из master"
git diff --quiet HEAD -- "$FILE" "$CHANGELOG" || die "В $FILE или $CHANGELOG есть незакоммиченные изменения"

git fetch -q --tags origin
[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/master)" ] || die "master расходится с origin/master — сначала git pull --rebase / git push"
git rev-parse -q --verify "refs/tags/$NEW" >/dev/null && die "Тег $NEW уже существует"

CUR=$(sed -n "s/^define('VERSION', '\([^']*\)');.*/\1/p" "$FILE")
[ -n "$CUR" ] || die "Не нашёл define('VERSION', ...) в $FILE"
[ "$(printf '%s\n%s\n' "$CUR" "$NEW" | sort -V | tail -1)" = "$NEW" ] && [ "$CUR" != "$NEW" ] \
    || die "Новая версия $NEW должна быть больше текущей $CUR"

PREV=$(git describe --tags --abbrev=0 HEAD) || die "Не нашёл предыдущий тег"
ITEMS=$(git log --no-merges --reverse --format='    * %s' "$PREV..HEAD" | grep -Ev '^    \* Версия [0-9.]+$' || true)
[ -n "$ITEMS" ] || die "С тега $PREV нет новых коммитов"
ENTRY=$(printf '%s\n\n%s\n' "$NEW" "$ITEMS")

if [ -n "$DRY" ]; then
    echo "Раздел $CHANGELOG ($PREV..HEAD):"
    echo
    echo "$ENTRY"
    exit 0
fi

# Раздел пишется с LF; в рабочей копии Windows (CRLF) git всё равно сохранит файл с LF
{ printf '%s\n\n' "$ENTRY"; cat "$CHANGELOG"; } > "$CHANGELOG.tmp"
mv "$CHANGELOG.tmp" "$CHANGELOG"

sed -i "s/^define('VERSION', '$CUR');/define('VERSION', '$NEW');/" "$FILE"
git add "$FILE" "$CHANGELOG"
git commit -q -m "Версия $NEW"
git tag "$NEW"
git push -q --atomic origin master "$NEW"

echo "Выпущена версия $NEW ($CUR -> $NEW)"
