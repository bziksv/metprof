<?php

define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: text/plain; charset=UTF-8');

$key = (string)($_GET['key'] ?? '');
if ($key !== 'polimer-prime-install-2026') {
	http_response_code(403);
	echo "forbidden\n";
	die;
}

$alerts = [
	'enabled' => 'Y',
	'policy_enabled' => 'Y',
	'policy_register' => 'Y',
	'policy_order' => 'Y',
	'notice_everywhere' => 'N',
	'support_email' => 'info@metprof-vrn.ru',
	'support_phone' => '+7 (473) 234-03-01',
	'extra_domains' => '',
	'profile_banner' => 'Y',
	'color_scheme' => 'polimer',
];

$phoneauth = [
	'enabled' => 'Y',
	'call_auth_enabled' => 'N',
	'test_confirm' => 'N',
	'verify_number' => '',
	'webhook_ips' => '37.139.38.215',
];

foreach ($alerts as $name => $value) {
	\Bitrix\Main\Config\Option::set('prime.alerts', $name, $value);
	echo "prime.alerts.{$name} = {$value}\n";
}

foreach ($phoneauth as $name => $value) {
	\Bitrix\Main\Config\Option::set('prime.phoneauth', $name, $value);
	echo "prime.phoneauth.{$name} = {$value}\n";
}

if (function_exists('BXClearCache')) {
	BXClearCache(true);
}

echo "done\n";
