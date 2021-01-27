<?php
require_once __DIR__ . '/config.php';

$chache = array();
$mail_text = array();

// Проверка доменов
foreach ($domains as $domain) {
	$date = 0;
	$zone = explode('.', $domain);
	$zone = end($zone);

	switch ($zone) { 
		case 'ru': 
		case 'su': 
		case 'рф': $server = 'whois.tcinet.ru'; break;					
		case 'com':		
		case 'net': $server = 'whois.verisign-grs.com'; break;					
		case 'org': $server = 'whois.pir.org'; break;					
	}

	$socket = fsockopen($server, 43);
	if ($socket) {
		fputs($socket, $domain . PHP_EOL);
		while (!feof($socket)) {
			$res = fgets($socket, 128);
			if (mb_stripos($res, 'paid-till:') !== false) {
				$date = explode('paid-till:', $res);
				$date = strtotime(trim($date[1]));
				break;
			}
			if (mb_stripos($res, 'Registry Expiry Date:') !== false) {
				$date = explode('Registry Expiry Date:', $res);
				$date = strtotime(trim($date[1]));
				break;
			}	
		}
		fclose($socket);
	}
	
	if (!empty($date) && time() + $warn > $date) {
		$mail_text[] = $domain . ' - заканчивается ' . date('d.m.Y H:i', $date);
	} elseif (empty($date)) {
		$mail_text[] = $domain . ' - не удалось получить whois';
	} 

	$chache['domains'][$domain] = $date;
}

// Проверка SSL-сертификатов
foreach ($certificates as $domain) {
	$date = 0;
	$url = 'ssl://' . $domain . ':443';
	$context = stream_context_create(array('ssl' => array('capture_peer_cert' => true)));

	$fp = @stream_socket_client($url, $err_no, $err_str, 30, STREAM_CLIENT_CONNECT, $context);
	$cert = @stream_context_get_params($fp);
 
	if (empty($err_no)) {
		$info = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
		$date = $info['validTo_time_t'];
	}
	
	if (!empty($date) && time() + $warn > $date) {
		$mail_text[] = $domain . ' - заканчивается сертификат ' . date('d.m.Y H:i', $date);
	} elseif (empty($date)) {
		$mail_text[] = $domain . ' - не удалось получить сертификат';
	}
	
	$chache['certificates'][$domain] = $date;
}

// Сохранение в файл.
file_put_contents(__DIR__ . '/chache.json', json_encode($chache));

// Вывод данных в браузер.
echo '<pre>' . print_r($chache, true) . '</pre>';

// Отправка уведомления.
if (!empty($mail_text)) {
	mb_send_mail(
		$email,
		'Мониторинг срока действия доменов и SSL-сертификатов', 
		implode('<br>', $mail_text), 
		"MIME-Version: 1.0\r\nContent-Type: text/html;"
	);
}