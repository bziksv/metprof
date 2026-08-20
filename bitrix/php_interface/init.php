<?php
define('RUB', 'руб');
define('IBLOCK_CATALOG', 24);

require_once __DIR__ . '/search_helpers.php';

function count_all($value) {
	if (empty($value)) {
		$value = [];
	}
	
	return count($value);
}

function price($id){
    $res = CCatalogSKU::getOffersList(
        $id,
        $iblockID = 24,
        $skuFilter = array("ACTIVE" => "Y"),
        $fields = array("CATALOG_PRICE_4"),
        $propertyFilter = array()
    );
	
	if (is_array($res) && array_key_exists($id, $res)) {
		$id_offer = array_keys($res[$id])[0];
		$ar_res_price = CPrice::GetBasePrice($id_offer, false, false);
		
		if ($ar_res_price['PRICE']) {
			return $ar_res_price['PRICE'];
		}
	}
	
	return false;
}
function priceDiscount($id){
    global $USER;
    $ar_res_price = CCatalogProduct::GetOptimalPrice($id, 1, $USER->GetUserGroupArray(), 'N');
    if($ar_res_price['DISCOUNT_PRICE']){
        return $ar_res_price['DISCOUNT_PRICE'];
    }else{
        return false;
    }
}

function getUrlProd($url){
    if($url){
        $code = explode('/',$url);
        if($code[3]){
            $code = $code[3];
        }else{
           $code = $code[2];
        }

        if(CModule::IncludeModule("iblock")) {
            $arSelect = Array("ID", "IBLOCK_ID","DETAIL_PAGE_URL","PREVIEW_PICTURE","DETAIL_PICTURE", "NAME", "PROPERTY_*");//IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
            $arFilter = Array("IBLOCK_ID" => 24, "CODE" => $code);
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            if($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                $arResults = array_merge($arFields,$arProps);
                return $arResults;
            }
        }
    }

}

function resizeImage($id, $w, $h){
    if(!is_numeric($id) || empty($id))
        return '/bitrix/templates/main/img/no_photo.png';

    return CFile::ResizeImageGet(
        $id,
        ["width" => $w, "height" => $h],
        BX_RESIZE_IMAGE_PROPORTIONAL,
        true,
        false,
        false,
        85
    )['src'];
}

function isRootFolder($id,$root){

    CModule::IncludeModule( 'catalog' );
    $res = CIBlockElement::GetByID($id);
    if($ar_res = $res->GetNext()){
        $nav = CIBlockSection::GetNavChain(false, $ar_res['IBLOCK_SECTION_ID']);
        $code_section = $nav->arResult[0]['CODE'];

        if($code_section == $root){
            return true;
        }else{
            return false;
        }
    }
}

/**
 * Товары в м² / с выбором длины листа — только через детальную карточку.
 */
function productNeedsDetailBuy($productId): bool
{
	$productId = (int)$productId;
	if ($productId <= 0 || !CModule::IncludeModule('catalog')) {
		return false;
	}

	$product = CCatalogProduct::GetByID($productId);
	if (is_array($product) && (int)($product['MEASURE'] ?? 0) === 6) {
		return true;
	}

	$unit = '';
	$res = CIBlockElement::GetProperty(IBLOCK_CATALOG, $productId, [], ['CODE' => 'CML2_BASE_UNIT']);
	if ($prop = $res->Fetch()) {
		$unit = (string)($prop['VALUE_ENUM'] ?? $prop['VALUE'] ?? '');
		if ((int)($prop['DESCRIPTION'] ?? 0) === 6) {
			return true;
		}
	}
	$unitNorm = mb_strtolower(str_replace(['²', ' '], ['2', ''], $unit));
	if ($unitNorm !== '' && (strpos($unitNorm, 'м2') !== false || $unitNorm === 'm2')) {
		return true;
	}

	if (!class_exists('CCatalogSKU') || !CCatalogSKU::IsExistOffers($productId, IBLOCK_CATALOG)) {
		return false;
	}

	$offers = CCatalogSKU::getOffersList(
		$productId,
		IBLOCK_CATALOG,
		['ACTIVE' => 'Y'],
		['ID', 'CATALOG_MEASURE'],
		['CODE' => ['DLINA']]
	);
	if (empty($offers[$productId]) || !is_array($offers[$productId])) {
		return false;
	}

	foreach ($offers[$productId] as $offer) {
		if ((int)($offer['CATALOG_MEASURE'] ?? 0) === 6) {
			return true;
		}
		$dlina = $offer['PROPERTIES']['DLINA']['VALUE'] ?? null;
		if ($dlina !== null && $dlina !== '' && $dlina !== false) {
			return true;
		}
	}

	return false;
}


function buttonName($IBLOCK_ID,$SECTION_ID){

    $IDs = array();
    $nav = CIBlockSection::GetNavChain($IBLOCK_ID, $SECTION_ID);
    foreach($nav->arResult as $item){
        $IDs[] = (int)$item['ID'];
    }
	// $SECTION = array(3191,3266,3205);
    foreach($SECTION as $s_id){
        if(in_array($s_id,$IDs)){
            return "Под заказ";

        }
    }
    return "Купить";
}


function AddOrderProperty($code, $value, $order)    {
    if (!strlen($code)) {
        return false;
    }
    if (CModule::IncludeModule('sale')) {
        if ($arProp = CSaleOrderProps::GetList(array(), array('CODE' => $code))->Fetch()) {

            $db_vals = CSaleOrderPropsValue::GetList(
                array(),
                array(
                    "ORDER_ID" => $order,
                    "ORDER_PROPS_ID" => $arProp["ID"]
                )
            );
            if ($arVals = $db_vals->Fetch()) {
                CSaleOrderPropsValue::Update($arVals["ID"], array("VALUE"=>$value));
            } else {
                CSaleOrderPropsValue::Add(array(
                    'NAME' => $arProp['NAME'],
                    'CODE' => $arProp['CODE'],
                    'ORDER_PROPS_ID' => $arProp['ID'],
                    'ORDER_ID' => $order,
                    'VALUE' => $value,
                ));
            }
            //  тут можно увидеть ошибку, если что
//                global $APPLICATION;
//                var_dump($APPLICATION->GetException());
        }
    }
}
