<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$currentProductId = (int)($arParams['CURRENT_PRODUCT_ID'] ?? 0);

foreach ($arResult['ITEMS'] as $key => &$arItem) {
    if ($currentProductId > 0 && (int)$arItem['ID'] === $currentProductId) {
        unset($arResult['ITEMS'][$key]);
        continue;
    }

    $IBLOCK_ID = $arItem['IBLOCK_ID'];
    $ID = $arItem['ID'];
    $arInfo = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);
    if (is_array($arInfo))
    {
        $arItem['OFFERS'] = [];
        $rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $ID), false, false, ['*', 'CATALOG_QUANTITY']);
        while ($arOffer = $rsOffers->GetNext())
        {
            $arItem['OFFERS'][] = $arOffer;
        }
    }

    if (!empty($arItem['OFFERS'][0]['ID'])) {
        $arItem['BASKET_ID'] = $arItem['OFFERS'][0]['ID'];
    }
}
unset($arItem);

?>
