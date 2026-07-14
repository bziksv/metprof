<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);
?>

<?if($arResult['ITEMS']):?>
<div class="slider_product" id="mp__product__popular">

	<? foreach($arResult['ITEMS'] as $item):?>
	<div>
		<div class="product">
			<a href="<?=$item['DETAIL_PAGE_URL']?>" style="display: block;height: 110px;">
				<img src="<?=CFile::ResizeImageGet($item["PREVIEW_PICTURE"]["ID"], array('width' => 150, 'height' => 150), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src']?>" alt="" height="110" style="max-height: 110px;margin: 0 auto;" class="img">
			</a>
			<a href="<?=$item['DETAIL_PAGE_URL']?>" class="name"><?=$item['NAME']?></a>
			<div class="price"><span><?=price($item['ID']);?></span> <?=RUB?>/<?=$item['PROPERTIES']['CML2_BASE_UNIT']['VALUE'];?></div>
			<a href="<?=$item["DETAIL_PAGE_URL"]?>" class="cart">Купить</a>
		</div>
	</div>
	<? endforeach; ?>

</div><!-- end::slider_product -->
<?endif;?>
