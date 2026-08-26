<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

return [
	'operator_name' => 'ООО «Металлинвест Профиль»',
	'operator_short' => 'ООО «Металлинвест Профиль»',
	'inn' => '3663104063',
	'kpp' => '366301001',
	'ogrn' => '1143668020462',
	'site' => 'https://metprof-vrn.ru/',
	'site_host' => 'metprof-vrn.ru',
	'email' => 'info@metprof-vrn.ru',
	'phone' => '+7 (473) 250-22-10',
	'phone_tel' => '+74732502210',
	'address_legal' => '394028, Россия, г. Воронеж, проезд Монтажный, д. 26',
	'address_postal' => '394028, Россия, г. Воронеж, проезд Монтажный, д. 26',
	'contacts_url' => '/contacts/',
	'urls' => [
		'personal_data' => '/legal/metprof-politika-obrabotki-pd/',
		'consent' => '/legal/metprof-soglasie-obrabotki-pd/',
		'cookie' => '/legal/metprof-politika-cookie/',
		'recommendation' => '/legal/metprof-pravila-rekomendatelnyh-tehnologiy/',
	],
	'third_parties' => include __DIR__ . '/third_parties_data.php',
];
