<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

require_once __DIR__ . '/legal_helpers.php';

function metprofLegalThirdPartiesData(): array
{
	static $data = null;
	if ($data === null) {
		$data = include __DIR__ . '/third_parties_data.php';
	}

	return $data;
}

function metprofLegalRenderThirdPartyPolicyLine(array $service): string
{
	$parts = [metprofLegalH($service['name'])];
	if (!empty($service['inn'])) {
		$parts[0] = metprofLegalH($service['name']) . ' (ИНН ' . metprofLegalH($service['inn']) . ')';
	}

	$line = $parts[0] . ' — ';
	$line .= '<a href="' . metprofLegalH($service['url']) . '" target="_blank" rel="noopener">'
		. metprofLegalH($service['link_label']) . '</a>';
	if (!empty($service['suffix'])) {
		$line .= ', ' . metprofLegalH($service['suffix']);
	}

	return $line;
}

function metprofLegalRenderThirdPartyConsentLine(array $service): string
{
	$name = metprofLegalH($service['name']);
	if (!empty($service['inn'])) {
		$name = metprofLegalH($service['name']) . ' (ИНН ' . metprofLegalH($service['inn']) . ')';
	}

	$description = $service['link_label'];
	if (!empty($service['suffix'])) {
		$description .= ', ' . $service['suffix'];
	}

	return $name . ' (' . metprofLegalH($description) . ') — '
		. '<a href="' . metprofLegalH($service['url']) . '" target="_blank" rel="noopener">'
		. metprofLegalH($service['url']) . '</a>';
}

function metprofLegalRenderThirdPartyRecommendationLine(array $service): string
{
	$name = metprofLegalH($service['name']);
	if (!empty($service['inn'])) {
		$name .= ' (ИНН ' . metprofLegalH($service['inn']) . ')';
	}

	$parts = [];
	foreach ($service['recommendation'] as $block) {
		$links = [];
		foreach ($block['urls'] as $url) {
			$links[] = '<a href="' . metprofLegalH($url) . '" target="_blank" rel="noopener">'
				. metprofLegalH($url) . '</a>';
		}
		$parts[] = implode(', ', $links) . ' — ' . metprofLegalH($block['text']);
	}

	return $name . ' — ' . implode('; ', $parts);
}
