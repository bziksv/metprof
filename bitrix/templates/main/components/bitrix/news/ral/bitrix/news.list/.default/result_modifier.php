<?php

foreach($arResult['ITEMS'] as $arItem){
	//$letterOLD = (substr(trim($arItem['NAME']),0, 1)) ?: "*";

	$letter = mb_strtoupper(substr($arItem["NAME"],0,2), "utf-8"); // это первый символ


    $arResult['SECTION_ITEMS'][strtoupper($letter)][] = $arItem;

	//$arResult['SECTION_ITEMS'][strtoupper($letterOLD)][] = $arItem;
}

if(count($arResult['SECTION_ITEMS']) > 0)
    $arResult['FILTER_LITTER'] = array_keys($arResult['SECTION_ITEMS']);

foreach ($arResult['FILTER_LITTER'] as $k => $let){
    if(is_numeric($let))
        $arResult['FILTER_LITTER']['int'][] = $let;
    elseif (preg_match('/[A-za-z]/u', $let))
        $arResult['FILTER_LITTER']['str_eng'][] = $let;
    else
        $arResult['FILTER_LITTER']['str_rus'][] = $let;

    unset($arResult['FILTER_LITTER'][$k]);
}

