<?php
class CNigesCookiesAcceptPublic
{
	/**
	 * Inject cookie notice at the end of page generation.
	 */
	public static function OnEpilog()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$html = (string)ob_get_clean();
		if ($html === '') {
			return;
		}

		\Bitrix\Main\Page\Asset::getInstance()->addString(
			$html,
			true,
			\Bitrix\Main\Page\AssetLocation::BODY_END
		);
	}
}
