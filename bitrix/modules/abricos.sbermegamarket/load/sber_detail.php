<?
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Iblock,
	Bitrix\Currency;

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_yandex.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/abricos.sbermegamarket/sber_detail.php');
IncludeModuleLangFile(__FILE__);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if (!check_bitrix_sessid())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$APPLICATION->SetTitle(GetMessage('YANDEX_DETAIL_TITLE'));
CModule::IncludeModule("abricos.sbermegamarket");
CModule::IncludeModule('catalog');

if (!$USER->CanDoOperation('catalog_export_edit'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_EXPORT'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if ((!isset($_REQUEST['IBLOCK_ID'])) || ($_REQUEST['IBLOCK_ID'] == ''))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("YANDEX_ERR_NO_IBLOCK_CHOSEN"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$intIBlockID = $_REQUEST['IBLOCK_ID'];
$intIBlockIDCheck = intval($intIBlockID);
if ($intIBlockIDCheck.'|' != $intIBlockID.'|' || $intIBlockIDCheck <= 0)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("YANDEX_ERR_NO_IBLOCK_CHOSEN"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
else
{
	$intIBlockID = $intIBlockIDCheck;
	unset($intIBlockIDCheck);
}

$strPerm = 'D';
$rsIBlocks = CIBlock::GetByID($intIBlockID);
if (($arIBlock = $rsIBlocks->Fetch()))
{
	$bBadBlock = !CIBlockRights::UserHasRightTo($intIBlockID, $intIBlockID, "iblock_admin_display");
	if ($bBadBlock)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_IBLOCK'));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(str_replace('#ID#',$intIBlockID,GetMessage("YANDEX_ERR_NO_IBLOCK_FOUND_EXT")));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$boolOffers = false;
$arOffers = false;
$arOfferIBlock = false;
$intOfferIBlockID = 0;
$arSelectOfferProps = array();
$arSelectedPropTypes = array(
	Iblock\PropertyTable::TYPE_STRING,
	Iblock\PropertyTable::TYPE_NUMBER,
	Iblock\PropertyTable::TYPE_LIST,
	Iblock\PropertyTable::TYPE_ELEMENT,
	Iblock\PropertyTable::TYPE_SECTION
);
$arOffersSelectKeys = array(
	YANDEX_SKU_EXPORT_ALL,
	YANDEX_SKU_EXPORT_MIN_PRICE,
	YANDEX_SKU_EXPORT_PROP,
);

$arOffers = CCatalogSKU::GetInfoByProductIBlock($intIBlockID);
if (!empty($arOffers['IBLOCK_ID']))
{
	$intOfferIBlockID = $arOffers['IBLOCK_ID'];
	$strPerm = 'D';
	$rsOfferIBlocks = CIBlock::GetByID($intOfferIBlockID);
	if ($arOfferIBlock = $rsOfferIBlocks->Fetch())
	{
		$bBadBlock = !CIBlockRights::UserHasRightTo($intOfferIBlockID, $intOfferIBlockID, "iblock_admin_display");
		if ($bBadBlock)
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_IBLOCK_SKU'));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(str_replace('#ID#',$intIBlockID,GetMessage("YANDEX_ERR_NO_IBLOCK_SKU_FOUND")));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	$boolOffers = true;

}
$arCondSelectProp = array(
	'ZERO' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_ZERO'),
	'NONZERO' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_NONZERO'),
	'EQUAL' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_EQUAL'),
	'NONEQUAL' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_NONEQUAL'),
);

$arTypesConfig = array(
	'none' => array(
		'articul','articulSCU','picture','vendor','vendorCode', 'barcode', 'model'
	)
);

$arTypesConfigKeys = array_keys($arTypesConfig);

$vatRates = array(
	'-' => GetMessage('YANDEX_BASE_VAT_EMPTY'),
	'0%' => '0%',
	'10%' => '10%',
	'20%' => '20%'
);

$defaultVatExport = array(
	'ENABLE' => 'N',
	'BASE_VAT' => ''
);

$dbRes = CIBlockProperty::GetList(
	array('SORT' => 'ASC'),
	array('IBLOCK_ID' => $intIBlockID, 'ACTIVE' => 'Y')
);
$arIBlock['PROPERTY'] = array();
$arIBlock['OFFERS_PROPERTY'] = array();
while ($arRes = $dbRes->Fetch())
{
	$arIBlock['PROPERTY'][$arRes['ID']] = $arRes;
}
if ($boolOffers)
{
	$rsProps = CIBlockProperty::GetList(array('SORT' => 'ASC'),array('IBLOCK_ID' => $intOfferIBlockID,'ACTIVE' => 'Y'));
	while ($arProp = $rsProps->Fetch())
	{
		if ($arOffers['SKU_PROPERTY_ID'] != $arProp['ID'])
		{
			if ($arProp['PROPERTY_TYPE'] == 'L')
			{
				$arProp['VALUES'] = array();
				$rsPropEnums = CIBlockProperty::GetPropertyEnum($arProp['ID'],array('sort' => 'asc'),array('IBLOCK_ID' => $intOfferIBlockID));
				while ($arPropEnum = $rsPropEnums->Fetch())
				{
					$arProp['VALUES'][$arPropEnum['ID']] = $arPropEnum['VALUE'];
				}
			}
			$arIBlock['OFFERS_PROPERTY'][$arProp['ID']] = $arProp;
			if (in_array($arProp['PROPERTY_TYPE'],$arSelectedPropTypes))
				$arSelectOfferProps[] = $arProp['ID'];
		}
	}
}


if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (!empty($_REQUEST['save']))
	{
		$arErrors = array();
		$arCurrency = array('RUB' => array('rate' => 1));
		if (is_array($_POST['CURRENCY']) && count($_POST['CURRENCY']) > 0)
		{
			$arCurrency = array();
			foreach ($_POST['CURRENCY'] as $CURRENCY)
			{
				$arCurrency[$CURRENCY] = array(
					'rate' => $_POST['CURRENCY_RATE'][$CURRENCY],
					'plus' => $_POST['CURRENCY_PLUS'][$CURRENCY]
				);
			}
		}

		$type = trim($_POST['type']);
		if ($type == '' || ($type != 'none' && !isset($arTypesConfig[$type])))
			$type = 'none';

		$addParams = array(
			'PARAMS' => array(),
		);
		if (isset($_POST['PARAMS_COUNT']) && intval($_POST['PARAMS_COUNT']) > 0)
		{
			$intCount = intval($_POST['PARAMS_COUNT']);
			if (isset($_POST['XML_DATA']['PARAMS']) && is_array($_POST['XML_DATA']['PARAMS']))
			{
				$arTempo = $_POST['XML_DATA']['PARAMS'];
				for ($i = 0; $i < $intCount; $i++)
				{
					if (empty($arTempo['ID_'.$i]))
						continue;
					$value = $arTempo['ID_'.$i];
					if (array_key_exists($value,$arIBlock['PROPERTY']) || array_key_exists($value,$arIBlock['OFFERS_PROPERTY']))
					{
						$addParams['PARAMS'][] = $value;
					}
				}
			}
		}

		$arTypeParams = array();
		if (isset($_POST['XML_DATA'][$type]) && is_array($_POST['XML_DATA'][$type]))
		{
			$arTypeParams = $_POST['XML_DATA'][$type];
/* 			foreach ($arTypeParams as $key => $value)
			{
				if (!in_array($key,$arTypesConfig[$type]))
				{
					unset($arTypeParams[$key]);
				}
				elseif (!array_key_exists($value,$arIBlock['PROPERTY']) && !array_key_exists($value,$arIBlock['OFFERS_PROPERTY']))
				{
					$arTypeParams[$key] = '';
				}
			} */
		}
		$XML_DATA = array_merge($arTypeParams, $addParams);

		foreach ($XML_DATA as $key => $value)
		{
			if (!$value)
				unset($XML_DATA[$key]);
		}

		$commonFields = [];
		if (!empty($_POST['COMMON_FIELDS']) && is_array($_POST['COMMON_FIELDS']))
		{
			foreach ($_POST['COMMON_FIELDS'] as $index => $value)
			{
				if (empty($value))
					continue;
				$commonFields[$index] = $value;
			}
			unset($index, $value);
		}

		$arSKUExport = false;
		if ($boolOffers)
		{
			$arSKUExport = array(
				'SKU_EXPORT_COND' => YANDEX_SKU_EXPORT_ALL,
				'SKU_PROP_COND' => array(
					'PROP_ID' => 0,
					'COND' => '',
					'VALUES' => array(),
				),
			);

			if (!empty($_POST['SKU_EXPORT_COND']) && in_array($_POST['SKU_EXPORT_COND'],$arOffersSelectKeys))
			{
				$arSKUExport['SKU_EXPORT_COND'] = $_POST['SKU_EXPORT_COND'];
			}
			else
			{
				$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_CONDITION_ABSENT');
			}
			if (YANDEX_SKU_EXPORT_PROP == $arSKUExport['SKU_EXPORT_COND'])
			{
				$boolCheck = true;
				$intPropID = 0;
				$strPropCond = '';
				$arPropValues = array();
				if (empty($_POST['SKU_PROP_COND']) || !in_array($_POST['SKU_PROP_COND'],$arSelectOfferProps))
				{
					$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_ABSENT');
					$boolCheck = false;
				}
				if ($boolCheck)
				{
					$intPropID = $_POST['SKU_PROP_COND'];
					if (empty($_POST['SKU_PROP_SELECT']) || empty($arCondSelectProp[$_POST['SKU_PROP_SELECT']]))
					{
						$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_COND_ABSENT');
						$boolCheck = false;
					}
				}
				if ($boolCheck)
				{
					$strPropCond = $_POST['SKU_PROP_SELECT'];
					if ($strPropCond == 'EQUAL' || $strPropCond == 'NONEQUAL')
					{
						if (!isset($_POST['SKU_PROP_VALUE_'.$intPropID]) || !is_array($_POST['SKU_PROP_VALUE_'.$intPropID]))
						{
							$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT');
							$boolCheck = false;
						}

						if ($boolCheck)
						{
							foreach($_POST['SKU_PROP_VALUE_'.$intPropID] as $strValue)
								if ($strValue <> '')
									$arPropValues[] = $strValue;
						}
						if (empty($arPropValues))
						{
							$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT');
							$boolCheck = false;
						}
					}
				}
				if ($boolCheck)
				{
					$arSKUExport['SKU_PROP_COND'] = array(
						'PROP_ID' => $intPropID,
						'COND' => $strPropCond,
						'VALUES' => $arPropValues,
					);
				}
			}
		}

		$vatExport = $defaultVatExport;
		if (isset($_POST['USE_VAT_EXPORT']) && is_string($_POST['USE_VAT_EXPORT']))
		{
			if ($_POST['USE_VAT_EXPORT'] == 'Y')
				$vatExport['ENABLE'] = 'Y';
			if ($vatExport['ENABLE'] == 'Y')
			{
				if (isset($_POST['BASE_VAT']) && is_string($_POST['BASE_VAT']) && $_POST['BASE_VAT'] !== '')
				{
					if (isset($vatRates[$_POST['BASE_VAT']]))
						$vatExport['BASE_VAT'] = $_POST['BASE_VAT'];
				}
				if ($vatExport['BASE_VAT'] === '')
				{
					$arErrors[] = GetMessage('YANDEX_VAT_ERR_BASE_VAT_ABSENT');
					$boolCheck = false;
				}
			}
		}

		if (empty($arErrors))
		{
			$arXMLData = array(
				'TYPE' => $type,
				'XML_DATA' => $XML_DATA,
				'STORE_DATA' => $STORE_DATA,
				'SBER_NOPHOTO' => $SBER_NOPHOTO,
				'SBER_NODESCR' => $SBER_NODESCR,
				'FILTER_QUANTYTY' => $FILTER_QUANTYTY,
				'FILTER_PRICE_MIN' => $FILTER_PRICE_MIN,
				'FILTER_PRICE_MAX' => $FILTER_PRICE_MAX,
				'STORE_INPUT' => $STORE_INPUT,
				'FILTER_DATA' => $FILTER_DATA,
				'FILTER_INPUT' => $FILTER_INPUT,
				'FILTER_PREF' => $FILTER_PREF,
				'SBER_DESCR_TEMPL_PL' => $SBER_DESCR_TEMPL_PL,
				'SBER_DESCR_TEMPL' => $SBER_DESCR_TEMPL,
				'SBER_TITLE_TEMPL' => $SBER_TITLE_TEMPL,
				'CURRENCY' => $arCurrency,
				'PRICE' => $_POST['PRICE'],
				'TITLE' => $TITLE,
				'CORR_1' => $CORR_1,
				'CORR_2' => $CORR_2,
				'CORR_3' => $CORR_3,
				'SKU_EXPORT' => $arSKUExport,
				'VAT_EXPORT' => $vatExport,
				'COMMON_FIELDS' => $commonFields
			);
?><script type="text/javascript">
top.BX.closeWait();
top.BX.WindowManager.Get().Close();
top.setDetailData('<?=CUtil::JSEscape(base64_encode(serialize($arXMLData)));?>');
</script>
<?
			die();
		}
		else
		{
			$e = new CAdminException(array(array('text' => implode("\n",$arErrors))));
			$message = new CAdminMessage(GetMessage("YANDEX_SAVE_ERR"), $e);
			echo $message->Show();
		}
	}
	else
	{
		$aTabs = array(
			array("DIV" => "yandex-settings-types", "TAB" => GetMessage('SBER_TAB1_TITLE'), "TITLE" => GetMessage('SBER_TAB1_DESC')),
			array("DIV" => "yandex-settings-filter", "TAB" => GetMessage('SBER_FILTER_TITLE'), "TITLE" => GetMessage('SBER_FILTER_DESC')),
			array("DIV" => "yandex-settings-store", "TAB" => GetMessage('SBER_STORE_TITLE'), "TITLE" => GetMessage('SBER_STORE_DESC')),
			array("DIV" => "yandex-settings-prices", "TAB" => GetMessage('SBER_TAB2_TITLE'), "TITLE" => GetMessage('SBER_TAB2_DESC')),
			array("DIV" => "yandex-settings-vats", "TAB" => GetMessage('SBER_TAB_VAT_TITLE'), "TITLE" => GetMessage('SBER_TAB_VAT_DESC')),
		);
		$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		function __yand_show_selector($group, $key, $IBLOCK, $value = "")
		{
			?><select name="XML_DATA[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>]">
			<option value=""<? echo ($value == "" ? ' selected' : ''); ?>><?=GetMessage('YANDEX_SKIP_PROP')?></option>
			<?
			if (!empty($IBLOCK['OFFERS_PROPERTY']))
			{
				?><option value=""><? echo GetMessage('YANDEX_PRODUCT_PROPS')?></option><?
			}
			foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
			{
				?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
			}
			if (!empty($IBLOCK['OFFERS_PROPERTY']))
			{
				?><option value=""><? echo GetMessage('YANDEX_OFFERS_PROPS')?></option><?
				foreach ($IBLOCK['OFFERS_PROPERTY'] as $key => $arProp)
				{
					?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
				}
			}
			?></select><?
		}
		function prop_option($group, $key, $IBLOCK, $value = "")
		{
			?>
			<option disabled="disabled"><?=GetMessage('SBER_FROM_PROP')?></option>
			<?
			foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
			{
				?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
			}
			if (!empty($IBLOCK['OFFERS_PROPERTY']))
			{
				?><option disabled="disabled"><? echo GetMessage('SBER_FROM_OFFERS_PROP')?></option><?
				foreach ($IBLOCK['OFFERS_PROPERTY'] as $key => $arProp)
				{
					?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
				}
			}
			?><?
		}
				function prop_price($group, $key, $IBLOCK, $value = false)
		{
			?>
			<option disabled="disabled"><?=GetMessage('SBER_FROM_PROP')?></option>
			<?
			foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
			{
				?><option value="prop_<?=$arProp['ID']?>"<? echo (($value and $value == ("prop_".$arProp['ID'])) ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
			}

			?><?
		}

		function __addParamCode()
		{
			return '<small>(param)</small>';
		}

		function __addParamName(&$IBLOCK, $intCount, $value)
		{
			ob_start();
			__yand_show_selector('PARAMS','ID_'.$intCount, $IBLOCK, $value);
			$strResult = ob_get_contents();
			ob_end_clean();
			return $strResult;
		}

		function __addParamUnit(&$IBLOCK, $intCount, $value)
		{
			return '<input type="text" size="3" name="XML_DATA[PARAMS][UNIT_'.$intCount.']" value="'.htmlspecialcharsbx($value).'">';
		}

		function __addParamRow(&$IBLOCK, $intCount, $strParam, $strUnit)
		{
			return '<tr id="yandex_params_tbl_'.$intCount.'">
				<td style="text-align: center;">'.__addParamCode().'</td>
				<td>'.__addParamName($IBLOCK, $intCount, $strParam).'</td>
				</tr>';
		}

/***************************************************************************
HTML form
****************************************************************************/
		$type = 'none';
		$arTypeValues = array();
		foreach ($arTypesConfigKeys as $key)
		{
			$arTempo = array();
			foreach ($arTypesConfig[$key] as $value)
				$arTempo[$value] = '';
			$arTypeValues[$key] = $arTempo;
		}
		$arAddParams = array();
		$params = array(
			'PARAMS' => array(),
		);
		$PRICE = 0;
		$CURRENCY = array();
		$arSKUExport = array(
			'SKU_EXPORT_COND' => 0,
			'SKU_PROP_COND' => array(
				'PROP_ID' => 0,
				'COND' => '',
				'VALUES' => array(),
			),
		);
		$vatExport = $defaultVatExport;
		$commonFields = [
			'DESCRIPTION' => 'PREVIEW_TEXT'
		];

		$arXmlData = array();
		if (!empty($_REQUEST['XML_DATA']))
		{
			$xmlData = base64_decode($_REQUEST['XML_DATA']);
			if (CheckSerializedData($xmlData))
				$arXmlData = unserialize($xmlData, ['allowed_classes' => false]);
			unset($xmlData);
		}
        if (isset($arXmlData['TITLE']))
			$TITLE = $arXmlData['TITLE'];
		if (isset($arXmlData['PRICE']))
			$PRICE = $arXmlData['PRICE'];
		if (isset($arXmlData['CORR_1']))
			$CORR_1 = $arXmlData['CORR_1'];
		if (isset($arXmlData['CORR_2']))
			$CORR_2 = (int)$arXmlData['CORR_2'];
		if (isset($arXmlData['CORR_3']))
			$CORR_3 = $arXmlData['CORR_3'];
		if (isset($arXmlData['CORR_4']))
			$CORR_4 = $arXmlData['CORR_4'];
		if (isset($arXmlData['CORR_5']))
			$CORR_5 = $arXmlData['CORR_5'];
		if (isset($arXmlData['CURRENCY']))
			$CURRENCY = $arXmlData['CURRENCY'];
		if (isset($arXmlData['TYPE']))
			$type = $arXmlData['TYPE'];
		if ($type != 'none' && !in_array($type,$arTypesConfigKeys))
			$type = 'none';
		if (isset($arXmlData['COMMON_FIELDS']) && is_array($arXmlData['COMMON_FIELDS']))
			$commonFields = array_merge($commonFields, $arXmlData['COMMON_FIELDS']);
		if (isset($arXmlData['XML_DATA']))
		{
			foreach ($arXmlData['XML_DATA'] as $key => $value)
			{
				if ($key == 'PARAMS')
				{
					$params[$key] = $value;
				}
				else
				{
					$arTypeValues[$type][$key] = $value;
				}
			}
		}
		if (is_array($params['PARAMS']) && !empty($params['PARAMS']))
		{
			foreach ($params['PARAMS'] as $strParam)
			{
				$arAddParams[] = array(
					'PARAM' => $strParam,
				);
			}
		}
		if (isset($arXmlData['STORE_DATA']))
		{
			foreach ($arXmlData['STORE_DATA'] as $key => $value)
					$store[$key] = $value;
		}
        if (isset($arXmlData['STORE_INPUT']))
		{
			foreach ($arXmlData['STORE_INPUT'] as $key => $value)
					$storeId[$key] = $value;
		}
				if (isset($arXmlData['FILTER_DATA']))
		{
			foreach ($arXmlData['FILTER_DATA'] as $key => $value)
					$filter[$key] = $value;
		}
        if (isset($arXmlData['FILTER_PREF']))
		{
			foreach ($arXmlData['FILTER_PREF'] as $key => $value)
					$filterPref[$key] = $value;
		}
		if (isset($arXmlData['FILTER_PRICE_MAX']))
		{
					$FILTER_PRICE_MAX = $arXmlData['FILTER_PRICE_MAX'];
		}
		if (isset($arXmlData['FILTER_INPUT']))
		{
			foreach ($arXmlData['FILTER_INPUT'] as $key => $value)
					$filterInput[$key] = $value;
		}
		if (isset($arXmlData['FILTER_PRICE_MAX']))
		{
					$FILTER_PRICE_MAX = $arXmlData['FILTER_PRICE_MAX'];
		}
		if (isset($arXmlData['FILTER_PRICE_MIN']))
		{
					$FILTER_PRICE_MIN = $arXmlData['FILTER_PRICE_MIN'];
		}
		if (isset($arXmlData['FILTER_QUANTYTY']))
		{
					$FILTER_QUANTYTY = $arXmlData['FILTER_QUANTYTY'];
		}
		if (isset($arXmlData['SBER_NODESCR']))
		{
					$SBER_NODESCR = $arXmlData['SBER_NODESCR'];
		}
		if (isset($arXmlData['SBER_NOPHOTO']))
		{
					$SBER_NOPHOTO = $arXmlData['SBER_NOPHOTO'];
		}
		if (isset($arXmlData['SBER_TITLE_TEMPL']))
		{
					$SBER_TITLE_TEMPL = $arXmlData['SBER_TITLE_TEMPL'];
		}
		if (isset($arXmlData['SBER_DESCR_TEMPL']))
		{
					$SBER_DESCR_TEMPL = $arXmlData['SBER_DESCR_TEMPL'];
		}
		if (isset($arXmlData['SBER_DESCR_TEMPL_PL']))
		{
					$SBER_DESCR_TEMPL_PL = $arXmlData['SBER_DESCR_TEMPL_PL'];
		}
		if (!empty($arXmlData['SKU_EXPORT']))
		{
			if (!empty($arXmlData['SKU_EXPORT']['SKU_EXPORT_COND']))
				$arSKUExport['SKU_EXPORT_COND'] = $arXmlData['SKU_EXPORT']['SKU_EXPORT_COND'];
			if (!empty($arXmlData['SKU_EXPORT']['SKU_PROP_COND']))
				$arSKUExport['SKU_PROP_COND'] = $arXmlData['SKU_EXPORT']['SKU_PROP_COND'];
		}
		if (!empty($arXmlData['VAT_EXPORT']) && is_array($arXmlData['VAT_EXPORT']))
		{
			$vatExport['ENABLE'] = $arXmlData['VAT_EXPORT']['ENABLE'];
			if ($vatExport['ENABLE'] != 'Y' && $vatExport['ENABLE'] != 'N')
				$vatExport['ENABLE'] = 'N';
			$vatExport['BASE_VAT'] = $arXmlData['VAT_EXPORT']['BASE_VAT'];
			if ($vatExport['BASE_VAT'] !== '' && !isset($vatRates[$vatExport['BASE_VAT']]))
				$vatExport['BASE_VAT'] = '';
		}

		?>
		<script type="text/javascript">
		var currentSelectedType = '<? echo $type; ?>';

		function switchType(type)
		{
			BX('config_' + currentSelectedType).style.display = 'none';
			currentSelectedType = type;
			BX('config_' + currentSelectedType).style.display = 'block';
		}
		</script>
		<form name="yandex_form" method="POST" action="<?=$APPLICATION->GetCurPage(); ?>">
			<input type="hidden" name="lang" value="<?=LANGUAGE_ID; ?>">
			<input type="hidden" name="bxpublic" value="Y">
			<input type="hidden" name="Update" value="Y" />
			<input type="hidden" name="IBLOCK_ID" value="<? echo $intIBlockID; ?>" />
			<? echo bitrix_sessid_post(); ?>
<?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
?>
		<tr class="heading">
			<td colspan="2"><?=GetMessage('SBER_PROPS_TYPE')?></td>
		</tr>

		<tr>
			<td colspan="2">
<?
		foreach ($arTypesConfig as $key => $arConfig):
?>
				<div id="config_<?=htmlspecialcharsbx($key)?>" style="padding: 10px; display: <? echo ($type == $key ? 'block' : 'none'); ?>;">
					<table width="90%" class="inner" style="text-align: center;">
					 		<tr>
			<td align="right"><?=htmlspecialcharsbx(GetMessage('SBER_PROP_TITLE'))?>&nbsp;</td>
			<td style="white-space: nowrap;">
			<select name="TITLE">
			<option value=""><?=GetMessage('SBER_SKIP_PROP'); ?></option>
			<?prop_option($key, 'TITLE', $arIBlock, (isset($TITLE) ? $TITLE : ''))?>
			</select>
			</td>
			</tr>
					<tr>
					<td align="right"><?=GetMessage('BX_CATALOG_EXPORT_SBER_DESCRIPTION'); ?></td>
					<td style="white-space: nowrap;"><select name="COMMON_FIELDS[DESCRIPTION]">
								<option value="PREVIEW_TEXT"<?=($commonFields['DESCRIPTION'] == 'PREVIEW_TEXT' ? ' selected' : '');?>><?=GetMessage('BX_CATALOG_EXPORT_SBER_DESCRIPTION_PREVIEW_TEXT'); ?></option>
								<option value="DETAIL_TEXT"<?=($commonFields['DESCRIPTION'] == 'DETAIL_TEXT' ? ' selected' : '');?>><?=GetMessage('BX_CATALOG_EXPORT_SBER_DESCRIPTION_DETAIL_TEXT'); ?></option>
							<? prop_option($key, $prop, $arIBlock, (isset($arTypeValues[$key][$prop]) ? $arTypeValues[$key][$prop] : ''))?>

							</select></td>
							</tr>

							<tr>
	<td colspan="2" align="center">
	<p id="first" onclick="first()"><?=GetMessage('SBER_TITLE_GEN'); ?></p>
	<p id="first_yelloy"; style="display:none" onclick="first_yelloy()"><?=GetMessage('SBER_TITLE_GEN_CLOSE'); ?></p>
	</td>
</tr>
<tr <? if(!$SBER_TITLE_TEMPL and !$SBER_DESCR_TEMPL) {?>id="second_hide" style="display:none" <?}?>>
	<td width="40%"><?=GetMessage('SBER_TITLE_TEMPL'); ?></td>
	<td width="60%">
	<textarea name="SBER_TITLE_TEMPL" rows="2" cols="40" ><?
	if($SBER_TITLE_TEMPL)
	  echo $SBER_TITLE_TEMPL;
	?></textarea>
	</td>
</tr>
<tr <? if(!$SBER_TITLE_TEMPL and !$SBER_DESCR_TEMPL) {?>id="tree_hide" style="display:none" <?}?>>
	<td width="40%"><?=GetMessage('SBER_DESCR_TEMPL'); ?></td>
	<td width="60%" >
	<textarea name="SBER_DESCR_TEMPL" rows="5" cols="40" ><?
	if($SBER_DESCR_TEMPL)
	  echo $SBER_DESCR_TEMPL;
	else echo $SBER_DESCR_TEMPL0;
	?></textarea>
<br />
   <small><?=GetMessage('SBER_TITLE_TEMPL_NOTE'); ?></small>
	</td>
</tr>
<tr <? if(!$SBER_TITLE_TEMPL and !$SBER_DESCR_TEMPL) {?>id="for_hide" style="display:none" <?}?>>
	<td width="40%"><?=GetMessage('SBER_DESCR_TEMPL_PL'); ?></td>
	<td width="60%" >
	<select name="SBER_DESCR_TEMPL_PL">
								<option value="none"<?=($SBER_DESCR_TEMPL_PL == 'none' ? ' selected' : '');?>><?=GetMessage('SBER_DESCR_TEMPL_PL_NONE'); ?></option>
								<option value="top"<?=($SBER_DESCR_TEMPL_PL == 'top' ? ' selected' : '');?>><?=GetMessage('SBER_DESCR_TEMPL_PL_TOP'); ?></option>
							<option value="bottom"<?=($SBER_DESCR_TEMPL_PL == 'bottom' ? ' selected' : '');?>><?=GetMessage('SBER_DESCR_TEMPL_PL_BOTTOM'); ?></option>
                             <br/>
							</select>

	 <br/>&nbsp;
	</td>
</tr>
<tr><td colspan=2><hr></td></tr>
<script>
function first() {
document.getElementById("second_hide").setAttribute("style", "opacity:1; transition: 1s; height: 100%;");
document.getElementById("tree_hide").setAttribute("style", "opacity:1; transition: 1s; height: 100%;");
document.getElementById("for_hide").setAttribute("style", "opacity:1; transition: 1s; height: 100%;");
document.getElementById("first").setAttribute("style", "display: none");
document.getElementById("first_yelloy").setAttribute("style", "display: block");
}

function first_yelloy() {
document.getElementById("second_hide").setAttribute("style", "display: none");
document.getElementById("tree_hide").setAttribute("style", "display: none");
document.getElementById("for_hide").setAttribute("style", "display: none");
document.getElementById("first_yelloy").setAttribute("style", "display: none");
document.getElementById("first").setAttribute("style", "display: block");
}
</script>
<tr>
						<td align="right"><?=htmlspecialcharsbx(GetMessage('SBER_PROP_articul'))?>&nbsp;<small>&lt;articul&gt;: </td>
						<td style="white-space: nowrap;"><?CAbricosSbermegamarket::articul('articul', $arIBlock, $arTypeValues['none']['articul'])?></small></td>
						</tr>
						<tr>
						<td align="right"><?=htmlspecialcharsbx(GetMessage('SBER_PROP_articulSCU'))?>&nbsp;<small>&lt;articul&gt;: </td>
						<td style="white-space: nowrap;"><?CAbricosSbermegamarket::articulSCU('articulSCU', $arIBlock, $arTypeValues['none']['articulSCU'])?></small></td>
						</tr>
<?
			foreach ($arConfig as $prop):
			if($prop=='articul' or $prop=='articulSCU')
			 continue;
?>
						<tr>
						<td align="right"><?=htmlspecialcharsbx(GetMessage('SBER_PROP_'.$prop))?>&nbsp;<small>&lt;<?=htmlspecialcharsbx($prop)?>&gt;: </td>
						<td style="white-space: nowrap;"><?__yand_show_selector($key, $prop, $arIBlock, (isset($arTypeValues[$key][$prop]) ? $arTypeValues[$key][$prop] : ''))?></small></td>
						</tr>
<?
			endforeach;
?>

					</table>
				</div>
<?
		endforeach;
?>
			</td>
		</tr>
		<tr class="heading">
			<td colspan="2" valign="top"><?=GetMessage('YANDEX_PROPS_ADDITIONAL')?></td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="config_param" style="padding: 10px auto; text-align: center;">
				<table class="inner" id="yandex_params_tbl" style="text-align: center; margin: 0 auto;">
					<thead>
					<tr><td style="text-align: center;"> </td>
					<td style="text-align: center;"><? echo GetMessage('YANDEX_PARAMS_TITLE'); ?></td>
					</tr>
					</thead>
					<tbody>
						<?
						$intCount = 0;
						foreach ($arAddParams as $arParamDetail)
						{
							echo __addParamRow($arIBlock, $intCount, $arParamDetail['PARAM'], '');
							$intCount++;
						}
						if ($intCount == 0)
						{
							echo __addParamRow($arIBlock, $intCount, '', '');
							$intCount++;
						}
						?>
					</tbody>
				</table>
				<input type="hidden" name="PARAMS_COUNT" id="PARAMS_COUNT" value="<? echo $intCount; ?>">
				<div style="width: 100%; text-align: center;"><input type="button" onclick="__addYP(); return false;" name="yandex_params_add" value="<? echo GetMessage('YANDEX_PROPS_ADDITIONAL_MORE'); ?>"></div>
				</div>
<script type="text/javascript">
function changeVatExport()
{
	var vatRates = BX('tr_BASE_VAT');

	if (!BX.type.isElementNode(vatRates))
		return;
	BX.style(vatRates, 'display', (this.checked ? 'table-row' : 'none'));
}

BX.ready(function(){
	var vatRates = BX('tr_BASE_VAT'),
		vatEnable = BX('USE_VAT_EXPORT');

	if (BX.type.isElementNode(vatRates) && BX.type.isElementNode(vatEnable))
		BX.bind(vatEnable, 'click', changeVatExport);

		setTimeout(function(){
			window.oParamSet1 = {
				pTypeTbl: BX("yandex_params_tbl"),
				curCount: <? echo ($intCount); ?>,
				intCounter: BX("PARAMS_COUNT")
			};
		},50);
});

function __addYP()
{
	var id0 = window.oParamSet1.curCount++,
		newRow,
		oCell,
		strContent;
	id0 = id0.toString();
	window.oParamSet1.intCounter.value = window.oParamSet1.curCount;
	newRow = window.oParamSet1.pTypeTbl.insertRow(window.oParamSet1.pTypeTbl.rows.length);
	newRow.id0 = 'yandex_params_tbl_'+id0;

	oCell = newRow.insertCell(-1);
	oCell.style.textAlign = 'center';
	strContent = '<? echo CUtil::JSEscape(__addParamCode()); ?>';
	strContent = strContent.replace(/tmp_xxx2/ig, id0);
	oCell.innerHTML = strContent;
	oCell = newRow.insertCell(-1);
	strContent = '<? echo CUtil::JSEscape(__addParamName($arIBlock, 'tmp_xxx2', '')); ?>';
	strContent = strContent.replace(/tmp_xxx2/ig, id0);
	oCell.innerHTML = strContent;
	console.log();
}
</script>
			</td>
		</tr>
<?
		if ($boolOffers)
		{
?>
			<tr class="heading">
				<td colspan="2"><? echo GetMessage('YANDEX_SKU_SETTINGS');?></td>
			</tr>
			<tr>
			<td valign="top"><? echo GetMessage('YANDEX_OFFERS_SELECT') ?></td><td><?
			$arOffersSelect = array(
				0 => '--- '.ToLower(GetMessage('YANDEX_OFFERS_SELECT')).' ---',
				YANDEX_SKU_EXPORT_ALL => GetMessage('YANDEX_SKU_EXPORT_ALL_TITLE'),
				YANDEX_SKU_EXPORT_MIN_PRICE => GetMessage('YANDEX_SKU_EXPORT_MIN_PRICE_TITLE'),
			);
			if (!empty($arSelectOfferProps))
			{
				$arOffersSelect[YANDEX_SKU_EXPORT_PROP] = GetMessage('YANDEX_SKU_EXPORT_PROP_TITLE');
			}
			?><select name="SKU_EXPORT_COND" id="SKU_EXPORT_COND"><?
			foreach ($arOffersSelect as $key => $value)
			{
				?><option value="<? echo htmlspecialcharsbx($key);?>" <? echo ($key == $arSKUExport['SKU_EXPORT_COND'] ? 'selected' : '');?>><? echo htmlspecialcharsEx($value); ?></option><?
			}
			?></select><?
			if (!empty($arSelectOfferProps))
			{
				?><div id="PROP_COND_CONT" style="display: <? echo (YANDEX_SKU_EXPORT_PROP == $arSKUExport['SKU_EXPORT_COND'] ? 'block' : 'none'); ?>;"><?
				?><table class="internal"><tbody>
				<tr class="heading">
					<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_ID'); ?></td>
					<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_COND'); ?></td>
					<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_VALUE'); ?></td>
				</tr>
				<tr>
					<td valign="top"><select name="SKU_PROP_COND" id="SKU_PROP_COND">
					<option value="0" <? echo (empty($arSKUExport['SKU_PROP_COND']) ? 'selected' : ''); ?>><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_EMPTY') ?></option>
					<?
					foreach ($arSelectOfferProps as $intPropID)
					{
						$strSelected = '';
						if (!empty($arSKUExport['SKU_PROP_COND']['PROP_ID']) && ($intPropID == $arSKUExport['SKU_PROP_COND']['PROP_ID']))
						{
							$strSelected = 'selected';
						}
						?><option value="<?=$intPropID; ?>" <? echo $strSelected; ?>><? echo htmlspecialcharsEx($arIBlock['OFFERS_PROPERTY'][$intPropID]['NAME']);?></option><?
					}
					?></select></td>
					<td valign="top"><select name="SKU_PROP_SELECT" id="SKU_PROP_SELECT"><option value="">--- <? echo ToLower(GetMessage('YANDEX_SKU_EXPORT_PROP_COND')); ?> ---</option><?
					foreach ($arCondSelectProp as $key => $value)
					{
						?><option value="<? echo htmlspecialcharsbx($key);?>" <? echo ($key == $arSKUExport['SKU_PROP_COND']['COND'] ? 'selected' : ''); ?>><? echo htmlspecialcharsEx($value); ?></option><?
					}
					?></select></td>
					<td><div id="SKU_PROP_VALUE_DV"><?
					foreach ($arSelectOfferProps as $intPropID)
					{
						$arProp = $arIBlock['OFFERS_PROPERTY'][$intPropID];
						?><div id="SKU_PROP_VALUE_DV_<? echo $arProp['ID']?>" style="display: <? echo ($intPropID == $arSKUExport['SKU_PROP_COND']['PROP_ID'] ? 'block' : 'none'); ?>;"><?
						if (!empty($arProp['VALUES']))
						{
							?><select name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" multiple><?
							foreach ($arProp['VALUES'] as $intValueID => $strValue)
							{
								?><option value="<? echo htmlspecialcharsbx($intValueID); ?>" <? echo (!empty($arSKUExport['SKU_PROP_COND']['VALUES']) && in_array($intValueID,$arSKUExport['SKU_PROP_COND']['VALUES']) ? 'selected' : ''); ?>><? echo htmlspecialcharsEx($strValue); ?></option><?
							}
							?></select><?
						}
						else
						{
							if (!empty($arSKUExport['SKU_PROP_COND']['VALUES']))
							{
								foreach ($arSKUExport['SKU_PROP_COND']['VALUES'] as $strValue)
								{
									?><input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value="<? echo htmlspecialcharsbx($strValue);?>"><br><?
								}
							}
							?><input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
							<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
							<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
							<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
							<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
							<?
						}
						?></div><?
					}
					?></div></td>
				</tr>
				</tbody></table><?
				?><script type="text/javascript">
				var obExportConds = null,
					obPropCondCont = null,
					obSelectProps = null,
					arPropLayers = [];
				<?
				$intCount = 0;
				foreach ($arSelectOfferProps as $intPropID)
				{
					?> arPropLayers[<? echo $intCount; ?>] = {'ID': <? echo $intPropID; ?>, 'OBJ': null};
					<?
					$intCount++;
				}
				?>

				function changeValueDiv()
				{
					if (obSelectProps)
					{
						var intCurPropID = obSelectProps.options[obSelectProps.selectedIndex].value;
						for (i = 0; i < arPropLayers.length; i++)
							if (arPropLayers[i].OBJ)
								BX.style(arPropLayers[i].OBJ, 'display', (intCurPropID == arPropLayers[i].ID ? 'block' : 'none'));
					}
				}

				function changePropCondCont()
				{
					if (obExportConds && obPropCondCont)
					{
						var intTypeCond = obExportConds.options[obExportConds.selectedIndex].value;
						BX.style(obPropCondCont, 'display', (intTypeCond == <? echo YANDEX_SKU_EXPORT_PROP; ?> ? 'block' : 'none'));
					}
				}

				BX.ready(function(){
					for (i = 0; i < arPropLayers.length; i++)
					{
						arPropLayers[i].OBJ = BX('SKU_PROP_VALUE_DV_'+arPropLayers[i].ID);
					}

					obSelectProps = BX('SKU_PROP_COND');
					if (obSelectProps)
						BX.bind(obSelectProps, 'change', changeValueDiv);
					obExportConds = BX('SKU_EXPORT_COND');
					obPropCondCont = BX('PROP_COND_CONT');
					if (obExportConds && obPropCondCont)
					{
						BX.bind(obExportConds, 'change', changePropCondCont);
					}
				});
				</script><?
				?></div><?
			}
			?></td>
			</tr>
<?
		}
       		function addInputRow($intCount)
			{
				$strResult='<input name=\"STORE_INPUT['.$intCount.']\" type=\"text\" value=\"\">';

				return $strResult;
			}
	       function addParamRow(&$IBLOCK, $intCount, $strParam, $strUnit,$filterInput="")
			{
				?><tr id="SBER_STORE_tbl_<?=$intCount?>">
					<td><?=CAbricosSbermegamarket::addRowQant($strUnit, "STORE_DATA[$intCount]",$strParam,$IBLOCK_ID)?>
					</td>
					<td>
					<input name="STORE_INPUT[<?=$intCount?>]" type="text" value="<?=$filterInput?>">
					</td>
					</tr>
					<?
			}
			function addParamRow2(&$IBLOCK, $intCount, $strParam, $strUnit,$filterInput="")
			{
				?><tr id="SBER_STORE_tbl_<?=$intCount?>">
					<td><?=CAbricosSbermegamarket::addRowQant($strUnit, "STORE_DATA[$intCount]",$strParam,$IBLOCK_ID)?>
					</td>

					</tr>
					<?
			}
			function addParamRowJs(&$IBLOCK, $intCount, $strParam, $strUnit,$filterInput="")
			{
				ob_start();
				addParamRow2($IBLOCK, $intCount, $strParam, $strUnit,$filterInput);
				$strResult = ob_get_contents();
				ob_end_clean();
				return $strResult;
			}
			function addParamName(&$IBLOCK, $intCount, $value)
			{
				ob_start();
				yand_show_selector('PARAMS',$intCount, $IBLOCK, $value);
				$strResult = ob_get_contents();
				ob_end_clean();
				return $strResult;
			}
			function yand_show_selector($group, $key, $IBLOCK, $value = "")
			{
				?><select name="STORE_DATA[<? echo htmlspecialcharsbx($key)?>]">
				<option value=""<? echo ($value == "" ? ' selected' : ''); ?>><?=GetMessage('SBER_SKIP_PROP')?></option>
				<?
				if (!empty($IBLOCK['OFFERS_PROPERTY']))
				{
					?><option value=""><? echo GetMessage('SBER_PRODUCT_PROPS')?></option><?
				}
				foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
				{
					?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
				}
				if (!empty($IBLOCK['OFFERS_PROPERTY']))
				{
					?><option value=""><? echo GetMessage('SBER_OFFERS_PROPS')?></option><?
					foreach ($IBLOCK['OFFERS_PROPERTY'] as $key => $arProp)
					{
						?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
					}
				}
				?></select>
				<?
			}

?>
		<?
		$tabControl->EndTab();
		?>
<?
		$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage('SBER_FILTER_TITLE');?></td>
	</tr>

<tr>
	<td width="40%"><? echo GetMessage('SBER_NOPHOTO'); ?></td>
	<td width="60%">
		<input type="hidden" name="SBER_NOPHOTO" value="N">
		<input type="checkbox" name="SBER_NOPHOTO" value="Y"<? echo ($SBER_NOPHOTO == 'Y' ? ' checked' : ''); ?>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('SBER_NODESCR'); ?></td>
	<td width="60%">
		<input type="hidden" name="SBER_NODESCR" value="N">
		<input type="checkbox" name="SBER_NODESCR" value="Y"<? echo ($SBER_NODESCR == 'Y' ? ' checked' : ''); ?>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('FILTER_QUANTYTY'); ?></td>
	<td width="60%">
		<input type="text" name="FILTER_QUANTYTY" value="<?=$FILTER_QUANTYTY;?>">
	</td>
</tr>
<tr class="price_block">
	<td width="40%"><? echo GetMessage('FILTER_PRICE_MIN'); ?></td>
	<td width="60%">
		<input type="text" name="FILTER_PRICE_MIN" value='<?=$FILTER_PRICE_MIN?>'>
    </td>
</tr>
<tr class="price_block">
	<td width="40%"><? echo GetMessage('FILTER_PRICE_MAX'); ?></td>
	<td width="60%">
		<input type="text" name="FILTER_PRICE_MAX" value='<?=$FILTER_PRICE_MAX?>'>
    </td>
</tr>


	<tr>
<td colspan=2 align="center">
<?
if($IBLOCK_ID>0)
{?>
<table class="inner" id="SBER_FILTER_tbl">
					<thead>
					<tr><td colspan=2><b><? echo GetMessage('SBER_FILTER_TITLE'); ?></b></td>
					</tr>
					</thead>
					<tbody>
<?
						$intCountF = 0;
						if($filter)
						{
							foreach ($filter as $arParamDetail)
							{
								echo CAbricosSbermegamarket::addParamRow($arIBlock, $intCountF, $arParamDetail, '',$filterPref[$intCountF],$filterInput[$intCountF]);
								?>

								<?
								$intCountF++;
							}
						}
						if ($intCountF == 0)
						{
							echo CAbricosSbermegamarket::addParamRow($arIBlock, $intCountF, '', '','','');
							$intCountF++;
						}
						?>
                  </tbody>
				</table>
				<input type="hidden" name="FILTER_COUNT" id="FILTER_COUNT" value="<? echo $intCount; ?>">
				<div style="width: 100%; text-align: center;"><input type="button" onclick="addYP3(); return false;" name="SBER_FILTER_add" value="<? echo GetMessage('SBER_FILTER_ADDITIONAL_MORE'); ?>"></div>
 <? }?>
	</td>
</tr>
<? if(!$intCountF) $intCountF=1; ?>
<script type="text/javascript">
BX.ready(function(){
		setTimeout(function(){
			window.oParamSet = {
				pTypeTbl: BX("SBER_FILTER_tbl"),
				curCount: <? echo ($intCountF); ?>,
				intCounter: BX("FILTER_COUNT")

			};
		},50);
});
function addYP3()
{
	var id = window.oParamSet.curCount++,
		newRow,
		oCell,
		strContent;
	id = id.toString();
	window.oParamSet.intCounter.value = window.oParamSet.curCount;
	newRow = window.oParamSet.pTypeTbl.insertRow(window.oParamSet.pTypeTbl.rows.length);
	newRow.id = 'SBER_FILTER_tbl_'+id;
	oCell = newRow.insertCell(-1);
	<?
	if($IBLOCK_ID>0)
	{	?>
		strContent = '<? echo CUtil::JSEscape(CAbricosSbermegamarket::addParamName($arIBlock, 'tmp_xxx', '')); ?>';
		strContent = strContent.replace(/tmp_xxx/ig, id);
		oCell.innerHTML = strContent;

	<?}
	else {
	?>
		strContent = document.getElementById('SBER_FILTER_tbl_0').getElementsByTagName('td')[0].innerHTML;
	    strContent = strContent.replace('FILTER_DATA[0]', 'FILTER_DATA['+id+']');
		oCell.innerHTML = strContent;
	<? }?>
	   	oCell = newRow.insertCell(-1);
		strContent = '<?=CAbricosSbermegamarket::addPrefRow($intCountF)?>';
		strContent = strContent.replace(/tmp_xxx/ig, id);
		oCell.innerHTML = strContent;

		oCell = newRow.insertCell(-1);
		strContent = '<?=CAbricosSbermegamarket::addInputRow($intCountF)?>';
		strContent = strContent.replace(/tmp_xxx/ig, id);
		oCell.innerHTML = strContent;
	}

</script>

		<?
		$tabControl->EndTab();
		$tabControl->BeginNextTab();
		?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage('SBER_STORE_TITLE'); ?></td>
	</tr>
		<tr>
		<td colspan="2">

	<?
if($IBLOCK_ID>0)
{?>
<table class="inner" id="SBER_STORE_tbl">
					<thead>
					<tr><td style='width: 300px;'><b><? echo GetMessage('SBER_STORE_1'); ?></b></td>
					<td><b><? echo GetMessage('SBER_STORE_2'); ?></b></td>
					</tr>
					<tr><td><? echo GetMessage('SBER_STORE_1_NOTE'); ?></td>
					<td><? echo GetMessage('SBER_STORE_2_NOTE'); ?></td>
					</tr>
					</thead>
					<tbody>
			<?
			$dbRes = CIBlockProperty::GetList(
				array('SORT' => 'ASC'),
				array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y')
			);
			$arIBlock['PROPERTY'] = array();
			$arIBlock['OFFERS_PROPERTY'] = array();
			while ($arRes = $dbRes->Fetch())
			{
				$arIBlock['PROPERTY'][$arRes['ID']] = $arRes;
			}
			if ($boolOffers)
			{
				$rsProps = CIBlockProperty::GetList(array('SORT' => 'ASC'),array('IBLOCK_ID' => $intOfferIBlockID,'ACTIVE' => 'Y'));
				while ($arProp = $rsProps->Fetch())
				{
					if ($arOffers['SKU_PROPERTY_ID'] != $arProp['ID'])
					{
						if ($arProp['PROPERTY_TYPE'] == 'L')
						{
							$arProp['VALUES'] = array();
							$rsPropEnums = CIBlockProperty::GetPropertyEnum($arProp['ID'],array('sort' => 'asc'),array('IBLOCK_ID' => $intOfferIBlockID));
							while ($arPropEnum = $rsPropEnums->Fetch())
							{
								$arProp['VALUES'][$arPropEnum['ID']] = $arPropEnum['VALUE'];
							}
						}
						$arIBlock['OFFERS_PROPERTY'][$arProp['ID']] = $arProp;
						if (in_array($arProp['PROPERTY_TYPE'],$arSelectedPropTypes))
							$arSelectOfferProps[] = $arProp['ID'];
					}
				}
			}
			$intCount2 = 0;
			if($storeId)
			{
				foreach ($store as $key => $value)
				{
					echo addParamRow($arIBlock, $intCount2, $value, $arIBlock['PROPERTY'],$storeId[$key]);
					$intCount2++;
				}
			}
			else
			{
				echo addParamRow($arIBlock, $intCount2, '', $arIBlock['PROPERTY'],'');
				$intCount2++;
			}
			?>
                 </tbody>
			</table>
			<input type="hidden" name="STORE_COUNT" id="STORE_COUNT" value="<? echo $intCount2; ?>">
			<div style="width: 100%; text-align: center;"><input type="button" onclick="addYP2(); return false;" name="SBER_STORE_add" value="<? echo GetMessage('SBER_FILTER_ADDITIONAL_MORE'); ?>"></div>
 <? }?>
 <script type="text/javascript">

BX.ready(function(){
		setTimeout(function(){
			window.oParamSet2 = {
				pTypeTbl: BX("SBER_STORE_tbl"),
				curCount: <? echo ($intCount2); ?>,
				intCounter: BX("STORE_COUNT")
			};
		},50);
});
function addYP2()
{
	var id = window.oParamSet2.curCount++,
		newRow,
		oCell,
		strContent;

	id = id.toString();
	window.oParamSet2.intCounter.value = window.oParamSet2.curCount;
	newRow = window.oParamSet2.pTypeTbl.insertRow(window.oParamSet2.pTypeTbl.rows.length);
	newRow.id = 'SBER_STORE_tbl_'+id;
	oCell = newRow.insertCell(-1);
    oCell2 = newRow.insertCell(-1);
		strContent = '<? echo CUtil::JSEscape(addParamRowJs($arIBlock,'tmp_xxx','',$arIBlock['PROPERTY'],'',''));?>';
		strContent2 = '<td><input name="STORE_INPUT['+id+']" type="text" value=""></td>';
		strContent = strContent.replace(/tmp_xxx/ig, id);
		oCell.innerHTML = strContent;
		oCell2.innerHTML = strContent2;
		 console.log(strContent);
	}

</script>
 </td></tr>
	<?
		$tabControl->BeginNextTab();

	$arGroups = array();
	$dbRes = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2));
	while ($arRes = $dbRes->Fetch())
	{
		if ($arRes['BUY'] == 'Y')
			$arGroups[] = $arRes['CATALOG_GROUP_ID'];
	}
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PRICES')?></td>
	</tr>

	<tr>
		<td><?=GetMessage('YANDEX_PRICE_TYPE');?>: </td>
		<td><br /><select name="PRICE">
			<option value=""<? echo ($PRICE == "" || $PRICE == 0 ? ' selected' : '');?>><?=GetMessage('YANDEX_PRICE_TYPE_NONE');?></option>
<?
	if (!empty($arGroups))
	{
		$dbRes = CCatalogGroup::GetListEx(
			array('SORT' => 'ASC'),
			array('ID' => $arGroups),
			false,
			false,
			array('ID', 'NAME', 'BASE')
		);
		while ($arRes = $dbRes->Fetch())
		{
			?>
			<option value="<?= $arRes['ID'] ?>"<? echo($PRICE == $arRes['ID'] ? ' selected' : ''); ?>><?= '[' . $arRes['ID'] . '] ' . htmlspecialcharsEx($arRes['NAME']); ?></option>
			
			<?
		}
		?>
		<?prop_price($key, 'PRICE', $arIBlock, (isset($PRICE) ? $PRICE : ''))?>
		<?
	}
?>
		</select><br /><br /></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('SBER_CORR')?></td>
	</tr>

    <tr>
		<td colspan="2"  style="text-align: center;"><br />
		<?=GetMessage('SBER_CORR_1')?>
		<select name="CORR_1">
		<option value="+" <? echo($CORR_1 == "+" ? ' selected' : ''); ?>><?=GetMessage('SBER_CORR_2')?></option>
		<option value="-" <? echo($CORR_1 == "-" ? ' selected' : ''); ?>><?=GetMessage('SBER_CORR_3')?></option>
		</select>
		<input type="text" size="3" name="CORR_2" value="<? echo $CORR_2;?>">
		<select name="CORR_3">
		<option value="rur" <? echo($CORR_3 == "rur" ? ' selected' : ''); ?>><?=GetMessage('SBER_CORR_4')?></option>
		<option value="%" <? echo($CORR_3 == "%" ? ' selected' : ''); ?>><?=GetMessage('SBER_CORR_5')?></option>
		</select>
		</td>
	</tr>
	<tr>
		<td colspan="2"  style="text-align: center;"><br />
		<?=GetMessage('SBER_CORR_NOTE')?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCIES')?></td>
	</tr>

	<tr>
		<td colspan="2"><br />
<?
	$arCurrencyList = array();
	$arCurrencyAllowed = array(
		'RUR' => true,
		'RUB' => true,
		'USD' => true,
		'EUR' => true,
		'UAH' => true,
		'BYR' => true,
		'BYN' => true,
		'KZT' => true
	);
	$existCurrrencies = Currency\CurrencyManager::getCurrencyList();
	$arCurrencyList = array_intersect_key(Currency\CurrencyManager::getCurrencyList(), $arCurrencyAllowed);

	$arValues = array(
		'SITE' => GetMessage('YANDEX_CURRENCY_RATE_SITE'),
		'CBRF' => GetMessage('YANDEX_CURRENCY_RATE_CBRF'),
		'NBU' => GetMessage('YANDEX_CURRENCY_RATE_NBU'),
		'NBK' => GetMessage('YANDEX_CURRENCY_RATE_NBK'),
		'CB' => GetMessage('YANDEX_CURRENCY_RATE_CB')
	);
?>
<table cellpadding="2" cellspacing="0" border="0" class="internal" style="text-align: center;">
<thead>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCY')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_RATE')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_PLUS')?></td>
	</tr>
</thead>
<tbody>
<?
	foreach ($arCurrencyList as $strCurrency => $strCurrencyName)
	{
?>
	<tr>
		<td><input type="checkbox" name="CURRENCY[]" id="CURRENCY_<?=$strCurrency?>" value="<?=$strCurrency?>"<? echo (empty($CURRENCY) || isset($CURRENCY[$strCurrency]) ? ' checked="checked"' : ''); ?> /></td>
		<td><label for="CURRENCY_<?=$strCurrency?>" class="text"><?=$strCurrencyName?></label></td>
		<td><select name="CURRENCY_RATE[<?=$strCurrency?>]" onchange="BX('CURRENCY_PLUS_<?=$strCurrency?>').disabled = this[this.selectedIndex].value == 'SITE'">
<?
		$strRate = 'SITE';
		if (isset($CURRENCY[$strCurrency]) && isset($CURRENCY[$strCurrency]['rate']))
			$strRate = $CURRENCY[$strCurrency]['rate'];
		if (!array_key_exists($strRate,$arValues))
			$strRate = 'SITE';
		foreach ($arValues as $key => $title)
		{
?>
			<option value="<?=htmlspecialcharsbx($key)?>"<? echo ($strRate == $key ? ' selected' : ''); ?>>(<?=htmlspecialcharsEx($key)?>) <?=htmlspecialcharsEx($title)?></option>
<?
		}
?>
		</select></td>
		<?
		$strPlus = '';
		if (isset($CURRENCY[$strCurrency]) && isset($CURRENCY[$strCurrency]['plus']))
			$strPlus = $CURRENCY[$strCurrency]['plus'];
		?>
		<td>+<input type="text" size="3" id="CURRENCY_PLUS_<?=$strCurrency?>" name="CURRENCY_PLUS[<?=$strCurrency?>]"<? echo ($strRate == 'SITE' ? ' disabled="disabled"' : ''); ?> value="<? echo htmlspecialcharsbx($strPlus); ?>">%</td>
	</tr>
<?
	}
?>
</tbody>
</table>

		</td>
	</tr>
<?
		$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage('YANDEX_VAT_SETTINGS');?></td>
	</tr>

	<tr>
		<td width="40%"><?=GetMessage('YANDEX_USE_VAT_EXPORT'); ?></td>
		<td>
			<input type="hidden" name="USE_VAT_EXPORT" value="N">
			<input type="checkbox" name="USE_VAT_EXPORT" value="Y" id="USE_VAT_EXPORT"<?=($vatExport['ENABLE'] == 'Y' ? ' checked' : '');?>>
		</td>
	</tr>
	<tr id="tr_BASE_VAT" style="display: <?=($vatExport['ENABLE'] == 'Y' ? 'table-row' : 'none');?>;">
		<td><?=GetMessage('YANDEX_BASE_VAT') ?></td>
		<td><select name="BASE_VAT">
				<option value=""<?=($vatExport['BASE_VAT'] === '' ? ' selected' : '');?>><?=GetMessage('YANDEX_BASE_VAT_ABSENT'); ?></option>
				<?
				foreach ($vatRates as $value => $title)
				{
					?><option value="<?=htmlspecialcharsbx($value); ?>"<?=($vatExport['BASE_VAT'] === $value ? ' selected' : '');?>><?=htmlspecialcharsbx($title);?></option><?
				}
				unset($value, $title);
				?>
			</select></td>
	</tr>
		<?
		$tabControl->EndTab();
		$tabControl->Buttons(array());
		$tabControl->End();

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
}