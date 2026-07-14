<?php

if($arResult['PROPERTIES']['CODE']['VALUE'] && is_numeric($arParams['CATALOG_IBLOCK_ID'])) {
    $arSelect = Array(
        "ID",
        "IBLOCK_ID",
        "NAME",
        "DETAIL_PAGE_URL",
        "PREVIEW_PICTURE",
        "PROPERTY_CML2_BASE_UNIT"
    );
    $arFilter = Array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams['CATALOG_IBLOCK_ID'], "PROPERTY_SVOYSTVA_TSVETA_VALUE" => $arResult['PROPERTIES']['CODE']['VALUE']);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {



        $arFields = $ob->GetFields();

$res2 = CIBlockSection::GetByID($arFields["IBLOCK_SECTION_ID"]);
if($ar_res2 = $res2->GetNext())
  $active_section = $ar_res2["ACTIVE"];

		if ($active_section == "Y"):


        $arProps = $ob->GetProperties();
		//echo "<pre>"; print_r($arFields); echo "</pre>";
        $arResult['ITEMS'][$arFields['ID']] = $arFields;
        $arResult['ITEMS'][$arFields['ID']]['PREVIEW_PICTURE'] = CFile::GetPath($arFields['PREVIEW_PICTURE']);
        $arResult['ITEMS'][$arFields['ID']]['PROPERTIES'] = $arProps;
        $arResult['SECTIONS'][$arFields['IBLOCK_SECTION_ID']]['ID'] = $arFields['IBLOCK_SECTION_ID'];
        $arResult['SECTIONS'][$arFields['IBLOCK_SECTION_ID']]['VALUE_XML_ID'] = $arProps['SVOYSTVA_TSVETA']['VALUE_XML_ID'];
        $arResult['SECTIONS'][$arFields['IBLOCK_SECTION_ID']]['SVOYSTVA_TSVETA_CODE'] = mb_strtolower($arProps['SVOYSTVA_TSVETA']['CODE']);

		endif;
    }

    if(count_all($arResult['SECTIONS']) > 0){
        foreach($arResult['SECTIONS'] as &$arSection){
            $res = CIBlockSection::GetByID($arSection["ID"]);
            if($ar_res = $res->GetNext()){
                $ar_res['VALUE_XML_ID'] = $arSection['VALUE_XML_ID'];
                $ar_res['SVOYSTVA_TSVETA_CODE'] = $arSection['SVOYSTVA_TSVETA_CODE'];
                $arSection = $ar_res;
                $arSection['PICTURE'] = CFile::GetPath($arSection['PICTURE']);
            }
        }
    }
}
