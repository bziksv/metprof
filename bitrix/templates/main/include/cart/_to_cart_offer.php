<?php
if(!isset($arParams["DETAIL_PAGE_URL"]))
    throw new ErrorException("DETAIL_PAGE_URL must be included!");

$arOffers = [];
foreach ($arParams["OFFERS"] as $offer){
    $arOffers['CATALOG_QUANTITY'] += $offer['CATALOG_QUANTITY'];
}

if(!isset($arOffers["CATALOG_QUANTITY"]))
    throw new ErrorException("CATALOG_QUANTITY must be included!");
?>

<? if($arOffers['CATALOG_QUANTITY']): ?>
    <a href="<?=$arParams["DETAIL_PAGE_URL"]?>" class="cart">Купить</a>
<? else: ?>
    <a href="javascript:void(0)" class="cart show-popup" data-id="order-product">Под заказ</a>
<? endif; ?>
