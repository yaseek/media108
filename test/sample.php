<?php

require_once dirname( __DIR__ ) . '/lib/class.htmlparse.php';

$url = 'http://habrahabr.ru';
$url = 'https://client.fieldforce.ru';
$url = 'http://www.yandex.ru';
//$url = 'http://www.ya.ru';
//$url = 'http://www.ucoz.ru';
//$url = 'http://lib.ru';
//$url = 'http://taobao.com';

$site = new Yaseek\HTMLParse($url);
//var_dump($site);

file_put_contents(__DIR__ . '/site.html', $site->getText());

echo sprintf('%s SITE CHARSET IS: %s', $url, $site->getCharset('meta')) . PHP_EOL;

