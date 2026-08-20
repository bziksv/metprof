<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if (!$arResult['ITEMS']) {
    return;
}

$sidebarMode = ($arParams['SIDEBAR_MODE'] === 'Y');

if ($sidebarMode):
    $sidebarItems = array_slice($arResult['ITEMS'], 0, 3);
?>
<div class="pc__prod-also pc__prod-also_similar">
    <div class="pc__vertical-carousel">
        <div class="vc-title">Похожие товары</div>
        <div class="vc-shell">
            <div class="vc-block-similar">
                <? foreach ($sidebarItems as $arItem):
                    $offerId = !empty($arItem['BASKET_ID']) ? $arItem['BASKET_ID'] : $arItem['ID'];
                    $itemPrice = priceDiscount($arItem['ID']) ?: price($arItem['ID']);
                    $previewSrc = $arItem['PREVIEW_PICTURE']['SRC'] ?? '';
                ?>
                <div class="vc-block-similar__item">
                    <div class="item cl">
                        <? if ($previewSrc): ?>
                            <a href="<?=$arItem['DETAIL_PAGE_URL']?>"><span><img src="<?=$previewSrc?>" alt="<?=htmlspecialcharsbx($arItem['NAME'])?>"></span></a>
                        <? endif; ?>
                        <div class="cost"><span><?=$itemPrice?></span> <?=RUB?>/<?=$arItem['PROPERTIES']['CML2_BASE_UNIT']['VALUE']?></div>
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="txt"><?=$arItem['NAME']?></a>
                        <? if ((float)$itemPrice): ?>
                            <? if (productNeedsDetailBuy((int)$arItem['ID'])): ?>
                                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="add2cart">Купить</a>
                            <? else: ?>
                                <a href="javascript:void(0)" onclick="addToBasket2(<?=$offerId?>,1,this,5);" class="add2cart">Купить</a>
                            <? endif; ?>
                        <? else: ?>
                            <a href="javascript:void(0)" class="add2cart show-popup" data-id="order-product">под заказ</a>
                        <? endif; ?>
                    </div>
                </div>
                <? endforeach; ?>
            </div>
        </div>
    </div>
</div><!--end::pc__prod-also-->
<? else: ?>
<div class="h2"><?=$arParams['PAGER_TITLE']?></div>
<div class="slider_product_show_all slider_product" id="mp__product__action">

    <?foreach($arResult["ITEMS"] as $arItem):?>
    <div>
        <div class="product">
            <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" style="display: block;height: 120px">
                <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$arItem["NAME"]?>" style="max-height: 110px;margin: 0 auto;" class="img">
            </a>
            <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="name"><?=$arItem["NAME"]?></a>
            <div class="price">
                <?if(priceDiscount($arItem['ID'])){?>
                    <span><?=priceDiscount($arItem['ID']);?></span> <?=RUB?>/<?=$arItem['PROPERTIES']['CML2_BASE_UNIT']['VALUE'];?>
                <?}else{?>
                    <span><?=price($arItem['ID']);?></span> <?=RUB?>/<?=$arItem['PROPERTIES']['CML2_BASE_UNIT']['VALUE'];?>
                <?}?>
            </div>

            <? if(!empty($arItem['OFFERS']) && count($arItem['OFFERS']) > 1): ?>
                <? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . "/include/cart/_to_cart_offer.php", $arItem, Array(
                    "SHOW_BORDER" => false
                )); ?>
            <?else:?>
                <? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . "/include/cart/_to_cart.php", $arItem, Array(
                    "SHOW_BORDER" => false
                )); ?>
            <?endif;?>
        </div>
    </div>
    <? endforeach; ?>
</div><!-- end::slider_product -->
<? endif; ?>
