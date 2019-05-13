---
layout: default
title: Руководство администратора Cetera CMS
nav_order: 5
---
# Установка Cetera CMS

## Requirements

1. PHP 5.5.0
2. A recent version of cURL >= 7.19.4 compiled with OpenSSL and zlib.
3. MySQL database >= 5.0

## Composer install

1. To create a new Cetera CMS project, run this command (substituting <Path> with the path where Composer should create the project):
```
composer create-project cetera-labs/website-skeleton <Path>
```    
2. Set up your web server to host your project. Its document root should point to your <Path>/www/ directory
    
3. Open http://server/install.php

4. Follow onscreen setup guide.    

## Install release version

1. Unpack [/cetera_cms/install.php.zip](https://cetera.ru/cetera_cms/install.php.zip) at your webserver home folder.

2. Open http://server/install.php

3. Follow onscreen setup guide.
