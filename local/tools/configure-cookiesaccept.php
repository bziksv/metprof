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

if (!CModule::IncludeModule('niges.cookiesaccept')) {
	echo "module niges.cookiesaccept not found\n";
	die;
}

$mainText = 'На сайте используются cookie-файлы для работы, статистики и улучшения сервиса. '
	. 'Продолжая пользоваться сайтом, вы соглашаетесь на обработку персональных данных на условиях '
	. '<a href="/legal/metprof-soglasie-obrabotki-pd/" rel="nofollow" target="_blank">Согласия на обработку персональных данных</a> '
	. 'и подтверждаете ознакомление с '
	. '<a href="/legal/metprof-politika-obrabotki-pd/" rel="nofollow" target="_blank">Политикой обработки персональных данных</a>. '
	. 'Подробнее о cookie — в '
	. '<a href="/legal/metprof-politika-cookie/" rel="nofollow" target="_blank">Политике использования cookie-файлов</a>. '
	. 'На сайте также действуют '
	. '<a href="/legal/metprof-pravila-rekomendatelnyh-tehnologiy/" rel="nofollow" target="_blank">рекомендательные технологии</a>.';

$stored = CNigesCookiesAcceptHelper::prepareForStorage('MAINTEXT', $mainText);

$rs = CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
while ($site = $rs->Fetch()) {
	$siteId = (string)$site['LID'];
	COption::SetOptionString('niges.cookiesaccept', 'MAINTEXT', $stored, '', $siteId);
	echo "MAINTEXT updated for site {$siteId} ({$site['NAME']})\n";
}

echo strip_tags($mainText) . "\n";
