#!/bin/sh
# Собирает сайт в томе /var/www/site: composer install, каталог www и статику back-office.
# Выполняется внутри php-контейнера (dev/dev.sh build). Повторный запуск безопасен.
#
# Повторяет target dist из build.xml, но вместо копирования делает симлинки на код
# из репозитория, чтобы правки PHP/JS были видны без пересборки. Заново собирать
# нужно только склеенные файлы: css/global.css и js/vendor.js.
set -eu

SITE=/var/www/site
SRC=$SITE/vendor/cetera-labs/cetera-cms
LIB=$SITE/vendor/cetera-labs/library
WWW=$SITE/www
CMS=$WWW/cms

step() { printf '\n== %s\n' "$*"; }

step 'composer install'
cp "$SRC/dev/site/composer.json" "$SITE/composer.json"
composer install --no-interaction --no-progress --working-dir="$SITE"

step 'www'
mkdir -p "$SITE/tmp" "$WWW/plugins" "$WWW/themes" "$WWW/uploads" "$WWW/.cache" "$CMS/css" "$CMS/js"
ln -sfn ../vendor/cetera-labs/library "$WWW/library"

# Всё содержимое cms/, кроме css и скрытых файлов, — симлинками
for f in "$SRC"/cms/*; do
    name=$(basename "$f")
    [ "$name" = css ] && continue
    ln -sfn "$f" "$CMS/$name"
done
for f in "$SRC"/cms/css/*; do
    ln -sfn "$f" "$CMS/css/$(basename "$f")"
done
ln -sfn "$SRC/cms/admin-panel.js" "$CMS/js/admin-panel.js"
ln -sfn "$SRC/cms/app.js" "$CMS/js/app.js"

# Новый back-office: собранный UI из back-office/build (target ui в build.xml)
if [ -d "$SRC/back-office/build/cms/ui" ]; then
    ln -sfn "$SRC/back-office/build/cms/ui" "$CMS/ui"
    ln -sfn "$SRC/back-office/build/ui.html" "$CMS/ui.html"
else
    echo "back-office/build/cms/ui не найден — новый back-office будет недоступен"
fi

step 'css/global.css, js/vendor.js'
{
    cat "$SRC/cms/css/main.css" "$SRC/cms/css/setup.css"
    find "$SRC/cms/app" -name '*.css' | sort | xargs -r cat
    cat "$LIB/cropper/cropper.min.css" "$LIB/extjs4/resources/ext-theme-classic/ext-theme-classic-all.css"
} > "$CMS/css/global.css"
rm -rf "$CMS/css/images"
cp -r "$LIB/extjs4/resources/ext-theme-classic/images" "$CMS/css/images"

for f in extjs4/ext-all.js beautify/beautify-css.js beautify/beautify-html.js beautify/beautify.js \
         minify/htmlminifier.min.js ace/ace.js cropper/cropper.min.js; do
    cat "$LIB/$f"
done > "$CMS/js/vendor.js"

# php-fpm работает от www-data: ему нужна запись в .prefs, кэш, загрузки, темы и плагины
touch "$WWW/.prefs"
chown -R www-data:www-data "$SITE/tmp" "$WWW/.prefs" "$WWW/.cache" "$WWW/uploads" "$WWW/themes" "$WWW/plugins"
chown www-data:www-data "$WWW"

step 'готово'
