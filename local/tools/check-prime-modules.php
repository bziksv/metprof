<?php

define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: text/plain; charset=UTF-8');

foreach (['prime.alerts', 'prime.phoneauth'] as $moduleId) {
	echo $moduleId . ': ' . (\Bitrix\Main\ModuleManager::isModuleInstalled($moduleId) ? 'installed' : 'NOT installed') . "\n";
}

$opts = [
	'prime.alerts' => ['enabled', 'support_email', 'support_phone', 'color_scheme'],
	'prime.phoneauth' => ['enabled', 'call_auth_enabled', 'webhook_ips'],
];
foreach ($opts as $mod => $names) {
	foreach ($names as $name) {
		echo $mod . '.' . $name . ' = ' . \Bitrix\Main\Config\Option::get($mod, $name, '(default)') . "\n";
	}
}

if (\Bitrix\Main\Loader::includeModule('prime.phoneauth')) {
	echo 'webhook_secret: ' . (\Prime\PhoneAuth\Config::getWebhookSecret() !== '' ? 'yes' : 'no') . "\n";
	echo 'webhook_path: ' . \Prime\PhoneAuth\Config::webhookPath() . "\n";
}
