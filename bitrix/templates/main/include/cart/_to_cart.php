<?php
$arOffers = [];
$arOffers = isset($arParams["OFFERS"][0]) ? $arParams["OFFERS"][0] : $arParams["OFFERS"];

if(empty($arOffers)){
	$CCatalogProduct = CCatalogProduct::GetByID($arParams['ID']);
	
	$arOffers['CATALOG_QUANTITY'] = $CCatalogProduct["QUANTITY"];
	$arOffers['ID'] = $arParams["ID"];
}

if(!isset($arOffers["CATALOG_QUANTITY"]))
    throw new ErrorException("CATALOG_QUANTITY must be included!");

if(!isset($arOffers['ID']))
    throw new ErrorException("OFFER ID must be included!");

if(!isset($arParams['PROPERTIES']['CML2_BASE_UNIT']))
    throw new ErrorException("PROPERTIES CML2_BASE_UNIT must be included!");
?>

<? if($arOffers['CATALOG_QUANTITY']): ?>
    <a href="javascript:void(0);" onclick="addToBasket2(<?=$arOffers['ID']?>, 1,this,<?=$arParams['PROPERTIES']['CML2_BASE_UNIT']['DESCRIPTION']?>);" class="cart">Купить</a>
<?else:?>
    <a href="javascript:void(0)" class="cart show-popup" data-id="order-product">Под заказ</a>
<?endif;?>
