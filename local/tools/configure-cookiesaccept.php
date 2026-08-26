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

$mainText = 'Данный сайт применяет технологию cookie для аналитических целей и персонализации рекламы. '
	. 'Оставаясь на странице, вы подтверждаете своё согласие в соответствии с действующей '
	. '<a href="/legal/metprof-politika-cookie/" rel="nofollow" target="_blank">Политикой использования cookie-файлов</a>.';

$stored = CNigesCookiesAcceptHelper::prepareForStorage('MAINTEXT', $mainText);

$rs = CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
while ($site = $rs->Fetch()) {
	$siteId = (string)$site['LID'];
	COption::SetOptionString('niges.cookiesaccept', 'MAINTEXT', $stored, '', $siteId);
	echo "MAINTEXT updated for site {$siteId} ({$site['NAME']})\n";
}

echo strip_tags($mainText) . "\n";
