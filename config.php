<?php

// Ошибки PHP.
error_reporting(E_ALL);
ini_set('display_errors', 1); 

// Локаль.
setlocale(LC_ALL, 'ru_RU.utf8');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');

// E-mail для уведомлений.
$email = 'mail@example.com';

// За сколько отправлять уведомление.
$warn = 259200; // 3 дня 

$domains = array(
	'php.ru',
	'php.su',
	'php.net',
	'habr.com',
	'wikipedia.org',
);

$certificates = array(
	'php.ru',
	'php.su',
	'php.net',
	'habr.com',
	'wikipedia.org',
);