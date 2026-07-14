<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
use Bitrix\Main\Loader;
global $APPLICATION;
if (isset($templateData['TEMPLATE_THEME']))
{
  $APPLICATION->SetAdditionalCSS($templateData['TEMPLATE_THEME']);
}
if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY']))
{
  $loadCurrency = false;
  if (!empty($templateData['CURRENCIES']))
    $loadCurrency = Loader::includeModule('currency');
  CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
  if ($loadCurrency)
  {
  ?>
  <script type="text/javascript">
    BX.Currency.setCurrencies(<? echo $templateData['CURRENCIES']; ?>);
  </script>
<?
  }
}

$arFilter = array('IBLOCK_ID' => $arParams["IBLOCK_ID"],"CODE" => $arParams['SECTION_CODE']);
$rsSect = CIBlockSection::GetList(Array("SORT"=>"ASC"),$arFilter,false,
    array(
        'UF_H1'
    )
);
$arSect = $rsSect->GetNext();


if($arSect['UF_H1']){
	global $ufH;
	$ufH=$arSect['UF_H1'];
	$APPLICATION->SetTitle($arSect['UF_H1']);
}
?>

