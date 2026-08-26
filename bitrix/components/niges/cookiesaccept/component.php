<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!CModule::IncludeModule('niges.cookiesaccept')) {
	return;
}

$arResult = CNigesCookiesAcceptHelper::loadSettings(SITE_ID);

if ($arResult['TEXTBTN'] === '') {
	$arResult['TEXTBTN'] = 'Принять';
}

if ($arResult['MAINTEXT'] === '') {
	$arResult['MAINTEXT'] = CNigesCookiesAcceptHelper::sanitizeHtml(
		'Данный сайт применяет технологию cookie для аналитических целей и персонализации рекламы. '
		. 'Оставаясь на странице, вы подтверждаете своё согласие в соответствии с действующей '
		. '<a href="/legal/metprof-politika-cookie/" rel="nofollow" target="_blank">Политикой использования cookie-файлов</a>.'
	);
}

$arResult['COOKIE_NAME'] = CNigesCookiesAcceptHelper::getCookieName($arResult['TEXTVER']);
$arResult['OPACITY'] = round(((int)$arResult['BTNOPACITY']) / 100, 2);

$this->IncludeComponentTemplate();
