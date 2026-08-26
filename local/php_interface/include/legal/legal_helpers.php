<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

function metprofLegalH($value): string
{
	return htmlspecialcharsbx((string) $value);
}

function metprofLegalLink(string $url, ?string $text = null): string
{
	$text = $text ?? $url;

	return '<a href="' . metprofLegalH($url) . '" target="_blank" rel="noopener">' . metprofLegalH($text) . '</a>';
}

function metprofLegalMailto(string $email): string
{
	return '<a href="mailto:' . metprofLegalH($email) . '">' . metprofLegalH($email) . '</a>';
}

function metprofLegalTel(string $phone, string $telHref): string
{
	return '<a href="tel:' . metprofLegalH($telHref) . '">' . metprofLegalH($phone) . '</a>';
}

function metprofLegalInternalLink(string $path, string $host): string
{
	return '<a href="' . metprofLegalH($path) . '">' . metprofLegalH($host . $path) . '</a>';
}

function metprofLegalOperatorLine(array $legal): string
{
	return metprofLegalH($legal['operator_name'])
		. ' (ИНН: ' . metprofLegalH($legal['inn'])
		. ', КПП: ' . metprofLegalH($legal['kpp'])
		. ', ОГРН: ' . metprofLegalH($legal['ogrn'])
		. ', адрес: ' . metprofLegalH($legal['address_legal']) . ')';
}

function metprofLegalFormConsentLabel(): string
{
	static $legal = null;
	if ($legal === null) {
		$legal = include __DIR__ . '/config.php';
	}

	$consentUrl = metprofLegalH($legal['urls']['consent']);
	$policyUrl = metprofLegalH($legal['urls']['personal_data']);

	return 'Даю '
		. '<a href="' . $consentUrl . '" target="_blank" rel="nofollow noopener">согласие на обработку персональных данных</a> '
		. 'и ознакомлен с '
		. '<a href="' . $policyUrl . '" target="_blank" rel="nofollow noopener">Политикой обработки персональных данных</a>.';
}
