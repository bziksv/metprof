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
		'На сайте используются cookie-файлы для работы, статистики и улучшения сервиса. '
		. 'Продолжая пользоваться сайтом, вы соглашаетесь на обработку персональных данных на условиях '
		. '<a href="/legal/metprof-soglasie-obrabotki-pd/" rel="nofollow" target="_blank">Согласия на обработку персональных данных</a> '
		. 'и подтверждаете ознакомление с '
		. '<a href="/legal/metprof-politika-obrabotki-pd/" rel="nofollow" target="_blank">Политикой обработки персональных данных</a>. '
		. 'Подробнее о cookie — в '
		. '<a href="/legal/metprof-politika-cookie/" rel="nofollow" target="_blank">Политике использования cookie-файлов</a>. '
		. 'На сайте также действуют '
		. '<a href="/legal/metprof-pravila-rekomendatelnyh-tehnologiy/" rel="nofollow" target="_blank">рекомендательные технологии</a>.'
	);
}

$arResult['COOKIE_NAME'] = CNigesCookiesAcceptHelper::getCookieName($arResult['TEXTVER']);
$arResult['OPACITY'] = round(((int)$arResult['BTNOPACITY']) / 100, 2);

$this->IncludeComponentTemplate();
