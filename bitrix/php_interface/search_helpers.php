<?php

function inCompare($IBLOCK_ID, $ID)
{
    return isset($_SESSION["CATALOG_COMPARE_LIST"][$IBLOCK_ID]["ITEMS"][$ID]);
}


function metprofGetOffersStockBatch(array $productIds, $iblockId = null)
{
    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
    $result = [];

    foreach ($productIds as $id)
    {
        $result[$id] = [
            'quantity' => 0.0,
            'offer_id' => 0,
        ];
    }

    if (empty($productIds) || !CModule::IncludeModule('catalog'))
        return $result;

    if ($iblockId === null)
        $iblockId = defined('IBLOCK_CATALOG') ? (int)IBLOCK_CATALOG : 24;

    $offersMap = CCatalogSKU::getOffersList(
        $productIds,
        $iblockId,
        ['ACTIVE' => 'Y'],
        ['ID'],
        []
    );

    if (empty($offersMap))
    {
        $productRes = CCatalogProduct::GetList(
            [],
            ['ID' => $productIds],
            false,
            false,
            ['ID', 'QUANTITY']
        );

        while ($row = $productRes->Fetch())
        {
            $id = (int)$row['ID'];
            $qty = (float)$row['QUANTITY'];
            $result[$id]['quantity'] = $qty;
            if ($qty > 0)
                $result[$id]['offer_id'] = $id;
        }

        return $result;
    }

    $offerIds = [];
    foreach ($offersMap as $offers)
    {
        foreach (array_keys($offers) as $offerId)
            $offerIds[] = (int)$offerId;
    }

    $offerIds = array_values(array_unique($offerIds));
    $offerQuantities = [];

    if (!empty($offerIds))
    {
        $productRes = CCatalogProduct::GetList(
            [],
            ['ID' => $offerIds],
            false,
            false,
            ['ID', 'QUANTITY']
        );

        while ($row = $productRes->Fetch())
            $offerQuantities[(int)$row['ID']] = (float)$row['QUANTITY'];
    }

    foreach ($offersMap as $productId => $offers)
    {
        $productId = (int)$productId;
        $totalQty = 0.0;
        $firstOfferId = 0;
        $firstInStockOfferId = 0;

        foreach (array_keys($offers) as $offerId)
        {
            $offerId = (int)$offerId;
            if ($firstOfferId === 0)
                $firstOfferId = $offerId;

            $offerQty = $offerQuantities[$offerId] ?? 0.0;
            $totalQty += $offerQty;

            if ($offerQty > 0 && $firstInStockOfferId === 0)
                $firstInStockOfferId = $offerId;
        }

        $result[$productId]['quantity'] = $totalQty;
        $result[$productId]['offer_id'] = $firstInStockOfferId > 0 ? $firstInStockOfferId : $firstOfferId;
    }

    return $result;
}

function checkProduct($id){
    $id = (int)$id;
    if ($id <= 0 || (float)price($id) <= 0)
        return false;

    $stock = metprofGetOffersStockBatch([$id]);

    return ($stock[$id]['quantity'] ?? 0) > 0;
}

function productMeasureUnit($productId, array $properties = [])
{
    $productId = (int)$productId;
    if ($productId <= 0)
        return 'шт';

    if (!empty($properties['CML2_BASE_UNIT']['VALUE']))
        return trim((string)$properties['CML2_BASE_UNIT']['VALUE']);

    if (!CModule::IncludeModule('catalog'))
        return 'шт';

    $product = CCatalogProduct::GetByID($productId);
    if (empty($product['MEASURE']))
        return 'шт';

    $measure = CCatalogMeasure::getList([], ['ID' => (int)$product['MEASURE']])->Fetch();
    if ($measure)
    {
        $unit = trim((string)($measure['SYMBOL_RUS'] ?: $measure['MEASURE_TITLE'] ?: ''));
        if ($unit !== '')
            return $unit;
    }

    return 'шт';
}

function polimerGetProductAvailability($id)
{
    $id = (int)$id;
    if ($id <= 0)
        return 'unavailable';

    if (checkProduct($id))
        return 'available';

    if ((float)price($id) > 0)
        return 'order';

    return 'unavailable';
}

/**
 * Батч-загрузка данных для выпадающего поиска: картинка, раздел, цена, наличие.
 * Вместо N× (GetByID + GetBasePrice + GetByID catalog) — 2–3 запроса на всю пачку.
 *
 * @return array<int, array{SECTION_ID:int,PICTURE:string,PRICE_SORT:?float,FORMAT_INT:?string,STOCK_STATUS:string,CAN_BUY:bool}>
 */
function polimerBatchLoadSearchProductData(array $productIds, $noPhoto = '/bitrix/templates/main/img/no_photo.png')
{
    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
    $result = [];

    if (empty($productIds))
        return $result;

    foreach ($productIds as $id)
    {
        $result[$id] = [
            'SECTION_ID' => 0,
            'PICTURE' => $noPhoto,
            'PRICE_SORT' => null,
            'FORMAT_INT' => null,
            'STOCK_STATUS' => 'unavailable',
            'CAN_BUY' => false,
            'OFFER_ID' => $id,
        ];
    }

    if (!CModule::IncludeModule('iblock'))
        return $result;

    $pictureIds = [];
    $res = CIBlockElement::GetList(
        [],
        ['ID' => $productIds],
        false,
        false,
        ['ID', 'IBLOCK_SECTION_ID', 'PREVIEW_PICTURE']
    );

    while ($row = $res->Fetch())
    {
        $id = (int)$row['ID'];
        if (!isset($result[$id]))
            continue;

        $result[$id]['SECTION_ID'] = (int)$row['IBLOCK_SECTION_ID'];
        $previewId = (int)$row['PREVIEW_PICTURE'];
        if ($previewId > 0)
            $pictureIds[$id] = $previewId;
    }

    foreach ($pictureIds as $id => $previewId)
    {
        $resized = CFile::ResizeImageGet(
            $previewId,
            ['width' => 56, 'height' => 56],
            BX_RESIZE_IMAGE_EXACT,
            true
        );
        if (!empty($resized['src']))
            $result[$id]['PICTURE'] = $resized['src'];
    }

    $prices = [];
    $quantities = [];

    if (CModule::IncludeModule('catalog'))
    {
        $baseGroup = CCatalogGroup::GetBaseGroup();
        $baseGroupId = (int)($baseGroup['ID'] ?? 0);

        if ($baseGroupId > 0)
        {
            $priceRes = CPrice::GetList(
                [],
                [
                    'PRODUCT_ID' => $productIds,
                    'CATALOG_GROUP_ID' => $baseGroupId,
                ],
                false,
                false,
                ['PRODUCT_ID', 'PRICE']
            );

            while ($priceRow = $priceRes->Fetch())
            {
                $pid = (int)$priceRow['PRODUCT_ID'];
                $priceVal = (float)$priceRow['PRICE'];
                if ($priceVal > 0 && !isset($prices[$pid]))
                    $prices[$pid] = $priceVal;
            }
        }

        $productRes = CCatalogProduct::GetList(
            [],
            ['ID' => $productIds],
            false,
            false,
            ['ID', 'QUANTITY']
        );

        while ($productRow = $productRes->Fetch())
            $quantities[(int)$productRow['ID']] = (float)$productRow['QUANTITY'];
    }

    $stockMap = metprofGetOffersStockBatch($productIds);

    foreach ($productIds as $id)
    {
        $priceVal = $prices[$id] ?? null;
        $qty = $stockMap[$id]['quantity'] ?? ($quantities[$id] ?? 0);
        $offerId = (int)($stockMap[$id]['offer_id'] ?? 0);

        if ($offerId > 0)
            $result[$id]['OFFER_ID'] = $offerId;

        if (($priceVal === null || $priceVal <= 0) && function_exists('price'))
        {
            $fallbackPrice = price($id);
            if ($fallbackPrice)
                $priceVal = (float)$fallbackPrice;
        }

        if ($priceVal !== null && $priceVal > 0)
        {
            $result[$id]['PRICE_SORT'] = $priceVal;
            $result[$id]['FORMAT_INT'] = CurrencyFormat($priceVal, 'RUB');
        }

        if ($qty > 0 && $priceVal !== null && $priceVal > 0)
        {
            $result[$id]['STOCK_STATUS'] = 'available';
            $result[$id]['CAN_BUY'] = true;
        }
        elseif ($priceVal !== null && $priceVal > 0)
        {
            $result[$id]['STOCK_STATUS'] = 'order';
            $result[$id]['CAN_BUY'] = false;
        }
        else
        {
            $result[$id]['STOCK_STATUS'] = 'unavailable';
            $result[$id]['CAN_BUY'] = false;
        }
    }

    return $result;
}

function polimerGetSearchProductSortPrice(array $productItem)
{
    if (array_key_exists('PRICE_SORT', $productItem) && $productItem['PRICE_SORT'] !== null)
        return (float)$productItem['PRICE_SORT'];

    $productId = (int)($productItem['ELEMENT_ID'] ?? $productItem['ITEM_ID'] ?? 0);
    if ($productId <= 0)
        return PHP_FLOAT_MAX;

    $productPrice = price($productId);

    return $productPrice ? (float)$productPrice : PHP_FLOAT_MAX;
}

function polimerSortSearchProductsByAvailabilityAndPrice(array $products, $query = '')
{
    $availableProducts = [];
    $orderProducts = [];
    $unavailableProducts = [];

    foreach ($products as $productItem)
    {
        $stockStatus = $productItem['STOCK_STATUS'] ?? 'unavailable';

        if ($stockStatus === 'available')
            $availableProducts[] = $productItem;
        elseif ($stockStatus === 'order')
            $orderProducts[] = $productItem;
        else
            $unavailableProducts[] = $productItem;
    }

    $query = trim((string)$query);
    $sortByRelevanceAndPrice = static function (array $left, array $right) use ($query): int {
        if ($query !== '')
        {
            $relCompare = polimerScoreSearchNameRelevance($left['NAME'] ?? '', $query)
                <=> polimerScoreSearchNameRelevance($right['NAME'] ?? '', $query);
            if ($relCompare !== 0)
                return $relCompare;
        }

        $priceCompare = polimerGetSearchProductSortPrice($left) <=> polimerGetSearchProductSortPrice($right);
        if ($priceCompare !== 0)
            return $priceCompare;

        return strcmp((string)($left['NAME'] ?? ''), (string)($right['NAME'] ?? ''));
    };

    usort($availableProducts, $sortByRelevanceAndPrice);
    usort($orderProducts, $sortByRelevanceAndPrice);
    usort($unavailableProducts, $sortByRelevanceAndPrice);

    return array_merge($availableProducts, $orderProducts, $unavailableProducts);
}

function polimerNormalizePropValue($value)
{
    if (is_array($value))
    {
        $value = array_filter($value, static function ($item) {
            return $item !== '' && $item !== null;
        });
        sort($value);

        return implode('|', $value);
    }

    return trim((string)$value);
}

function polimerGetProductPropMatchValue(array $prop)
{
    if (!empty($prop['VALUE_ENUM_ID']))
        return (string)$prop['VALUE_ENUM_ID'];

    return polimerNormalizePropValue($prop['VALUE'] ?? '');
}

function polimerGetSimilarSearchSectionId($sectionId)
{
    $sectionId = (int)$sectionId;
    if ($sectionId <= 0)
        return 0;

    $section = CIBlockSection::GetByID($sectionId)->Fetch();
    if (!$section)
        return $sectionId;

    if ((int)$section['IBLOCK_SECTION_ID'] > 0)
        return (int)$section['IBLOCK_SECTION_ID'];

    return $sectionId;
}

function polimerGetSectionSmartFilterCodes($iblockId, $sectionId, $limit = 3)
{
    if (!CModule::IncludeModule('iblock'))
        return [];

    $iblockId = (int)$iblockId;
    $sectionId = (int)$sectionId;
    $limit = max(1, (int)$limit);
    $checkedSections = [];

    while ($sectionId > 0 && count($checkedSections) < 5)
    {
        if (in_array($sectionId, $checkedSections, true))
            break;

        $checkedSections[] = $sectionId;

        $res = \Bitrix\Iblock\SectionPropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'SECTION_ID' => $sectionId,
                'SMART_FILTER' => 'Y',
            ],
            'select' => [
                'PROPERTY_ID',
                'PROPERTY_CODE' => 'PROPERTY.CODE',
                'PROPERTY_SORT' => 'PROPERTY.SORT',
            ],
            'order' => [
                'PROPERTY_SORT' => 'ASC',
                'PROPERTY_ID' => 'ASC',
            ],
            'limit' => $limit,
        ]);

        $codes = [];
        while ($row = $res->fetch())
        {
            if (!empty($row['PROPERTY_CODE']))
                $codes[] = $row['PROPERTY_CODE'];
        }

        if (!empty($codes))
            return array_values(array_unique($codes));

        $section = CIBlockSection::GetByID($sectionId)->Fetch();
        $sectionId = $section ? (int)$section['IBLOCK_SECTION_ID'] : 0;
    }

    return [];
}

function polimerFetchSectionProductsForSimilar($iblockId, $sectionId, $excludeId, array $propCodes, $limit = 150)
{
    $items = [];
    $propCodes = array_values(array_filter(array_unique($propCodes)));

    $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'SORT'];
    $arFilter = [
        'IBLOCK_ID' => (int)$iblockId,
        'SECTION_ID' => (int)$sectionId,
        'INCLUDE_SUBSECTIONS' => 'Y',
        'ACTIVE' => 'Y',
        'ACTIVE_DATE' => 'Y',
        '!ID' => (int)$excludeId,
    ];

    $res = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'NAME' => 'ASC'],
        $arFilter,
        false,
        ['nTopCount' => max(20, (int)$limit)],
        $arSelect
    );

    while ($ob = $res->GetNextElement())
    {
        $fields = $ob->GetFields();
        $props = $ob->GetProperties();

        $propValues = [];
        foreach ($propCodes as $code)
        {
            if (!empty($props[$code]))
                $propValues[$code] = polimerGetProductPropMatchValue($props[$code]);
        }

        $items[] = [
            'ID' => (int)$fields['ID'],
            'PROPS' => $propValues,
        ];
    }

    return $items;
}

function polimerGetSimilarProductIds(array $product, $limit = 10)
{
    $limit = max(1, (int)$limit);
    $productId = (int)($product['ID'] ?? 0);
    $iblockId = (int)($product['IBLOCK_ID'] ?? 0);
    $sectionId = (int)($product['IBLOCK_SECTION_ID'] ?? 0);

    if ($productId <= 0 || $iblockId <= 0 || $sectionId <= 0)
        return [];

    if (!CModule::IncludeModule('iblock'))
        return [];

    $cache = \Bitrix\Main\Data\Cache::createInstance();
    $cacheKey = 'similar_v1_' . $productId . '_' . $limit;
    $cacheDir = '/polimer/similar_products';

    if ($cache->initCache(3600, $cacheKey, $cacheDir))
        return $cache->getVars();

    $cacheStarted = $cache->startDataCache();

    $searchSectionId = polimerGetSimilarSearchSectionId($sectionId);
    $propCodes = polimerGetSectionSmartFilterCodes($iblockId, $sectionId, 3);

    $matchProps = [];
    foreach ($propCodes as $code)
    {
        if (empty($product['PROPERTIES'][$code]))
            continue;

        $value = polimerGetProductPropMatchValue($product['PROPERTIES'][$code]);
        if ($value !== '')
            $matchProps[$code] = $value;
    }

    $candidates = polimerFetchSectionProductsForSimilar(
        $iblockId,
        $searchSectionId,
        $productId,
        $propCodes,
        150
    );

    $exact = [];
    $partial = [];
    $rest = [];
    $matchCount = count($matchProps);

    foreach ($candidates as $candidate)
    {
        if ((int)$candidate['ID'] === $productId)
            continue;

        if ($matchCount === 0)
        {
            $rest[] = (int)$candidate['ID'];
            continue;
        }

        $score = 0;
        foreach ($matchProps as $code => $value)
        {
            if (($candidate['PROPS'][$code] ?? '') === $value)
                $score++;
        }

        if ($score === $matchCount)
            $exact[] = (int)$candidate['ID'];
        elseif ($score > 0)
            $partial[] = ['ID' => (int)$candidate['ID'], 'SCORE' => $score];
        else
            $rest[] = (int)$candidate['ID'];
    }

    usort($partial, static function ($a, $b) {
        return $b['SCORE'] <=> $a['SCORE'];
    });

    $partialIds = array_column($partial, 'ID');
    $result = array_values(array_unique(array_merge($exact, $partialIds, $rest)));
    $result = array_slice($result, 0, $limit);

    if ($cacheStarted)
        $cache->endDataCache($result);

    return $result;
}

function polimerFormatPropertyDisplayValue(array $prop)
{
    if (empty($prop))
        return '';

    $value = $prop['DISPLAY_VALUE'] ?? $prop['VALUE'] ?? '';
    if (is_array($value))
        $value = implode(', ', array_filter($value, static function ($item) {
            return $item !== '' && $item !== null;
        }));

    return trim(strip_tags((string)$value));
}

function polimerShortPropertyName($name)
{
    $name = trim((string)$name);
    if ($name === '')
        return '';

    $lower = mb_strtolower($name);
    $map = [
        'мощност' => 'Мощность',
        'контур' => 'Контурность',
        'объ' => 'Объём',
        'вес' => 'Вес',
        'напряж' => 'Напряжение',
        'диаметр' => 'Диаметр',
        'напор' => 'Напор',
        'подач' => 'Подача',
        'тип насос' => 'Тип',
        'вид насос' => 'Вид',
    ];

    foreach ($map as $needle => $label)
    {
        if (mb_strpos($lower, $needle) !== false)
            return $label;
    }

    return $name;
}

function polimerIsPowerInWatts($code, $nameLower)
{
    $code = mb_strtoupper((string)$code);

    if (preg_match('/_KVT|_KW($|_)/i', $code))
        return false;

    if (preg_match('/_VT($|_)|MOSHCHNOST_VT/i', $code))
        return true;

    if (mb_strpos($nameLower, 'мощност') !== false)
    {
        if (mb_strpos($nameLower, 'квт') !== false || mb_strpos($nameLower, 'kw') !== false)
            return false;

        if (mb_strpos($nameLower, 'вт') !== false || mb_strpos($nameLower, 'w') !== false)
            return true;
    }

    return false;
}

function polimerFormatPowerNumeric($value, $inWatts = false)
{
    $normalized = str_replace(',', '.', preg_replace('/[^\d,\.]/u', '', (string)$value));
    if ($normalized === '' || !is_numeric($normalized))
        return trim((string)$value) . ($inWatts ? ' Вт' : ' кВт');

    $num = (float)$normalized;

    if ($inWatts)
    {
        if ($num >= 1000)
        {
            $kwt = $num / 1000;
            $formatted = rtrim(rtrim(number_format($kwt, 1, ',', ''), '0'), ',');

            return $formatted . ' кВт';
        }

        $formatted = rtrim(rtrim(number_format($num, 0, ',', ''), '0'), ',');

        return $formatted . ' Вт';
    }

    $formatted = rtrim(rtrim(number_format($num, 2, ',', ''), '0'), ',');

    return $formatted . ' кВт';
}

function polimerFormatSearchSpecPart(array $prop)
{
    $value = polimerFormatPropertyDisplayValue($prop);
    if ($value === '')
        return '';

    $code = mb_strtoupper((string)($prop['CODE'] ?? ''));
    $name = trim((string)($prop['NAME'] ?? ''));
    $nameLower = mb_strtolower($name);

    if (preg_match('/\s(квт|kw|кг|kg|л|l|мм|mm|м³|м3|bar|бар|в|w)\b/ui', $value))
        return $value;

    if (preg_match('/MOSHCH|MOHNOST|_KVT|_VT|_KW$/i', $code) || mb_strpos($nameLower, 'мощност') !== false)
    {
        if (preg_match('/^[\d\s,\.]+$/u', $value))
            return polimerFormatPowerNumeric($value, polimerIsPowerInWatts($code, $nameLower));
    }

    if (preg_match('/NAPOR|PODACH|PROIZVODITEL/i', $code) || mb_strpos($nameLower, 'напор') !== false || mb_strpos($nameLower, 'подач') !== false)
    {
        if (preg_match('/^[\d\s,\.]+$/u', $value))
        {
            if (mb_strpos($nameLower, 'напор') !== false || preg_match('/NAPOR/i', $code))
                return rtrim(str_replace('.', ',', $value)) . ' м';

            if (mb_strpos($nameLower, 'подач') !== false || preg_match('/PODACH|PROIZVODITEL/i', $code))
                return rtrim(str_replace('.', ',', $value)) . ' л/мин';
        }
    }

    if (preg_match('/VES|MASS|WEIGHT/i', $code) || mb_strpos($nameLower, 'вес') !== false)
    {
        if (preg_match('/^[\d\s,\.]+$/u', $value))
            return 'Вес: ' . $value . ' кг';
    }

    if (preg_match('/OBEM|EMKOST|VOLUME/i', $code) || mb_strpos($nameLower, 'объ') !== false)
    {
        if (preg_match('/^[\d\s,\.]+$/u', $value))
            return $value . ' л';
    }

    if (!preg_match('/^[\d\s,\.]+$/u', $value))
        return $value;

    $shortName = polimerShortPropertyName($name);
    if ($shortName !== '')
        return $shortName . ': ' . $value;

    return $value;
}

function polimerGetSectionSearchableCodes($iblockId, $limit = 3)
{
    if (!CModule::IncludeModule('iblock'))
        return [];

    $codes = [];
    $res = \Bitrix\Iblock\PropertyTable::getList([
        'filter' => [
            'IBLOCK_ID' => (int)$iblockId,
            'ACTIVE' => 'Y',
            'SEARCHABLE' => 'Y',
        ],
        'select' => ['CODE'],
        'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
        'limit' => max(1, (int)$limit),
    ]);

    while ($row = $res->fetch())
    {
        if (!empty($row['CODE']))
            $codes[] = $row['CODE'];
    }

    return $codes;
}

function polimerGetSearchSpecFallbackCodes()
{
    return [
        'TIP_KOTLA',
        'KONTOURNOST',
        'KOLICHESTVO_KONTOUROV',
        'VOZMOZHNOE_PODKLYUCHENIE',
        'TIP_NASOSOV',
        'VID_NASOSOV',
        'NAPOR_M_VOD_ST_',
        'MOSHCHNOST_KVT',
        'MOSHCHNOST_VT',
    ];
}

function polimerIsExcludedSearchSpecCode($code)
{
    $code = mb_strtoupper((string)$code);

    if (preg_match('/^(VES|MASS|WEIGHT|SHIRINA|VYSOTA|GLUBINA|DLINA|RAZMER|DIAMETR|UDELNYY_VES|UDERZHIVAEMYY_VES)/', $code))
        return true;

    return false;
}

function polimerGetSearchSpecCandidateCodes($iblockId, $sectionId)
{
    $codes = polimerGetSectionSmartFilterCodes($iblockId, $sectionId, 20);
    $codes = array_merge($codes, polimerGetSearchSpecFallbackCodes());
    $codes = polimerPrioritizeSearchSpecCodes($codes);

    return array_values(array_filter(array_unique($codes), static function ($code) {
        return !polimerIsExcludedSearchSpecCode($code);
    }));
}

function polimerExtractSpecsFromName($name)
{
    $parts = [];
    $name = trim((string)$name);

    if ($name === '')
        return $parts;

    if (preg_match('/(\d+(?:[,\.]\d+)?)\s*(?:квт|kw|kwt)\b/ui', $name, $match))
        $parts[] = str_replace('.', ',', $match[1]) . ' кВт';

    if (preg_match('/(одноконтурн\w*|двухконтурн\w*|комбинированн\w*)/ui', $name, $match))
        $parts[] = mb_strtolower($match[1]);

    return $parts;
}

function polimerGetSearchSectionPropertyCodes($iblockId, $sectionId, $limit = 2)
{
    return array_slice(polimerGetSearchSpecCandidateCodes($iblockId, $sectionId), 0, max($limit, 8));
}

function polimerPrioritizeSearchSpecCodes(array $codes)
{
    $priority = [
        'TIP_KOTLA', 'KONTOURNOST', 'KOLICHESTVO_KONTOUROV', 'VOZMOZHNOE_PODKLYUCHENIE',
        'TIP_NASOSOV', 'VID_NASOSOV',
        'NAPOR_M_VOD_ST_', 'NAPOR', 'PODACHA', 'PROIZVODITELNOST',
        'MOSHCHNOST_KVT', 'MOSHCHNOST', 'MOSHCH', 'MOCHNOST', 'NOMINALNAYA_MOSHCHNOST',
        'MOSHCHNOST_VT',
        'TIP',
        'OBEM', 'EMKOST', 'OBEM_BAKA',
    ];
    $deprioritize = ['VES_KG', 'VES', 'MASS', 'WEIGHT', 'SHIRINA', 'VYSOTA', 'GLUBINA', 'DLINA'];
    usort($codes, static function ($a, $b) use ($priority, $deprioritize) {
        $aUpper = mb_strtoupper($a);
        $bUpper = mb_strtoupper($b);

        $aDep = in_array($aUpper, $deprioritize, true) ? 1 : 0;
        $bDep = in_array($bUpper, $deprioritize, true) ? 1 : 0;
        if ($aDep !== $bDep)
            return $aDep <=> $bDep;

        $aIndex = array_search($aUpper, $priority, true);
        $bIndex = array_search($bUpper, $priority, true);
        $aIndex = $aIndex === false ? 100 : $aIndex;
        $bIndex = $bIndex === false ? 100 : $bIndex;

        if ($aIndex === $bIndex)
            return strcmp($a, $b);

        return $aIndex <=> $bIndex;
    });

    return array_values(array_unique($codes));
}

function polimerFillSearchProductSpecs(array &$products, $iblockId = IBLOCK_CATALOG, $propsLimit = 2)
{
    if (empty($products) || !CModule::IncludeModule('iblock'))
        return;

    $iblockId = (int)$iblockId;
    $propsLimit = max(1, (int)$propsLimit);
    $sectionCodesCache = [];
    $productIds = [];
    $codesByProduct = [];

    foreach ($products as $index => $product)
    {
        $productId = (int)($product['ELEMENT_ID'] ?? $product['ITEM_ID'] ?? 0);
        $sectionId = (int)($product['SECTION_ID'] ?? 0);

        if ($productId <= 0)
            continue;

        $productIds[] = $productId;

        if ($sectionId > 0)
        {
            if (!isset($sectionCodesCache[$sectionId]))
                $sectionCodesCache[$sectionId] = polimerGetSearchSpecCandidateCodes($iblockId, $sectionId);

            $codesByProduct[$productId] = $sectionCodesCache[$sectionId];
        }
        else
        {
            $codesByProduct[$productId] = polimerGetSearchSpecFallbackCodes();
        }
    }

    $productIds = array_values(array_unique($productIds));
    if (empty($productIds))
        return;

    $allCodes = polimerGetSearchSpecFallbackCodes();
    foreach ($codesByProduct as $codes)
        $allCodes = array_merge($allCodes, $codes);

    $allCodes = array_values(array_unique(array_filter($allCodes, static function ($code) {
        return !polimerIsExcludedSearchSpecCode($code);
    })));
    $propsByProduct = [];

    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'ID' => $productIds, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'IBLOCK_ID']
    );

    while ($ob = $res->GetNextElement())
    {
        $fields = $ob->GetFields();
        $props = $ob->GetProperties();

        if (!empty($allCodes))
        {
            $filteredProps = [];
            foreach ($allCodes as $code)
            {
                if (isset($props[$code]))
                    $filteredProps[$code] = $props[$code];
            }
            $props = $filteredProps;
        }

        $propsByProduct[(int)$fields['ID']] = $props;
    }

    foreach ($products as &$product)
    {
        $productId = (int)($product['ELEMENT_ID'] ?? $product['ITEM_ID'] ?? 0);
        $specs = [];
        $codes = $codesByProduct[$productId] ?? [];

        $descriptive = [];
        $numeric = [];

        foreach ($codes as $code)
        {
            if (polimerIsExcludedSearchSpecCode($code))
                continue;

            $prop = $propsByProduct[$productId][$code] ?? [];
            $rawValue = polimerFormatPropertyDisplayValue($prop);
            if ($rawValue === '')
                continue;

            $part = polimerFormatSearchSpecPart($prop);
            if ($part === '')
                continue;

            if (preg_match('/^[\d\s,\.]+$/u', $rawValue))
                $numeric[] = $part;
            else
                $descriptive[] = $part;
        }

        foreach (array_merge($descriptive, $numeric) as $part)
        {
            if (!in_array($part, $specs, true))
                $specs[] = $part;

            if (count($specs) >= $propsLimit)
                break;
        }

        if (count($specs) < $propsLimit)
        {
            foreach (polimerExtractSpecsFromName($product['NAME'] ?? '') as $part)
            {
                if ($part !== '' && !in_array($part, $specs, true))
                    $specs[] = $part;

                if (count($specs) >= $propsLimit)
                    break;
            }
        }

        $product['SPECS'] = implode(' · ', $specs);
    }
    unset($product);
}

function polimerMapProductIdsToSectionIds(array $productIds, $iblockId = IBLOCK_CATALOG)
{
    $map = [];
    $productIds = array_values(array_filter(array_map('intval', $productIds)));
    if (empty($productIds) || !CModule::IncludeModule('iblock'))
        return $map;

    $res = CIBlockElement::GetList(
        [],
        ['ID' => $productIds, 'IBLOCK_ID' => (int)$iblockId],
        false,
        false,
        ['ID', 'IBLOCK_SECTION_ID']
    );

    while ($row = $res->Fetch())
    {
        $id = (int)($row['ID'] ?? 0);
        if ($id > 0)
            $map[$id] = (int)($row['IBLOCK_SECTION_ID'] ?? 0);
    }

    return $map;
}

function polimerParseSearchSectionFilterIds($raw = null)
{
    if ($raw === null)
        $raw = $_REQUEST['sid'] ?? null;

    if ($raw === null || $raw === '')
        return [];

    if (is_array($raw))
        $parts = $raw;
    else
        $parts = preg_split('/[,\s]+/', (string)$raw, -1, PREG_SPLIT_NO_EMPTY);

    $ids = [];
    foreach ($parts as $part)
    {
        $id = (int)$part;
        if ($id > 0)
            $ids[$id] = $id;
    }

    return array_values($ids);
}

function polimerBuildSearchSectionsFromProducts(array $products, $noPhoto = '/bitrix/templates/main/img/no_photo.png', $pictureSize = 48)
{
    if (empty($products) || !CModule::IncludeModule('iblock'))
        return [];

    $counts = [];
    foreach ($products as $product)
    {
        $sectionId = (int)($product['SECTION_ID'] ?? 0);
        if ($sectionId > 0)
            $counts[$sectionId] = ($counts[$sectionId] ?? 0) + 1;
    }

    if (empty($counts))
        return [];

    $pictureSize = max(24, (int)$pictureSize);
    $sections = [];
    $res = CIBlockSection::GetList(
        ['NAME' => 'ASC'],
        ['ID' => array_keys($counts)],
        false,
        ['ID', 'NAME', 'SECTION_PAGE_URL', 'PICTURE', 'ELEMENT_CNT']
    );

    while ($row = $res->GetNext())
    {
        $sectionId = (int)$row['ID'];
        $picture = $noPhoto;

        if (!empty($row['PICTURE']))
        {
            $resized = CFile::ResizeImageGet(
                $row['PICTURE'],
                ['width' => $pictureSize, 'height' => $pictureSize],
                BX_RESIZE_IMAGE_EXACT,
                true
            );
            if (!empty($resized['src']))
                $picture = $resized['src'];
        }

        $searchCount = (int)($counts[$sectionId] ?? 0);
        $sections[] = [
            'ID' => $sectionId,
            'NAME' => $row['NAME'],
            'URL' => $row['SECTION_PAGE_URL'],
            'PICTURE' => $picture,
            'COUNT' => $searchCount,
            'TOTAL' => (int)$row['ELEMENT_CNT'],
        ];
    }

    usort($sections, static function ($a, $b) {
        if ($a['COUNT'] !== $b['COUNT'])
            return $b['COUNT'] <=> $a['COUNT'];

        return strcmp($a['NAME'], $b['NAME']);
    });

    return $sections;
}

function polimerConvertMixedLayoutChars($text)
{
    static $map = [
        'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з',
        '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л',
        'l' => 'д', ';' => 'ж', '\'' => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т', 'm' => 'ь',
        ',' => 'б', '.' => 'ю', '/' => '.',
        'Q' => 'Й', 'W' => 'Ц', 'E' => 'У', 'R' => 'К', 'T' => 'Е', 'Y' => 'Н', 'U' => 'Г', 'I' => 'Ш', 'O' => 'Щ', 'P' => 'З',
        'A' => 'Ф', 'S' => 'Ы', 'D' => 'В', 'F' => 'А', 'G' => 'П', 'H' => 'Р', 'J' => 'О', 'K' => 'Л',
        'L' => 'Д', 'Z' => 'Я', 'X' => 'Ч', 'C' => 'С', 'V' => 'М', 'B' => 'И', 'N' => 'Т', 'M' => 'Ь',
    ];

    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $converted = [];

    foreach ($chars as $char)
        $converted[] = $map[$char] ?? $char;

    return implode('', $converted);
}

function polimerConvertKeyboardLayoutMixed($text)
{
    if (!CModule::IncludeModule('search'))
        return $text;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/tools/language.php';

    $variants = [
        CSearchLanguage::ConvertKeyboardLayout($text, 'en', 'ru'),
        CSearchLanguage::ConvertKeyboardLayout($text, 'ru', 'en'),
    ];

    foreach ($variants as $variant)
    {
        if ($variant && $variant !== $text)
            return $variant;
    }

    return $text;
}

function polimerGetCyrillicTypoSubstitutes($char)
{
    static $map = [
        'а' => 'аоея', 'б' => 'бп', 'в' => 'вм', 'г' => 'гн', 'д' => 'дл', 'е' => 'еиё', 'ё' => 'ёео',
        'ж' => 'жш', 'з' => 'зс', 'и' => 'иеы', 'й' => 'йи', 'к' => 'кул', 'л' => 'лдк', 'м' => 'мнв',
        'н' => 'нмг', 'о' => 'оаеу', 'п' => 'прб', 'р' => 'рл', 'с' => 'сз', 'т' => 'ть', 'у' => 'уко',
        'ф' => 'фа', 'х' => 'х', 'ц' => 'цс', 'ч' => 'чш', 'ш' => 'шщч', 'щ' => 'щш', 'ы' => 'ыи',
        'ь' => 'ьъ', 'ъ' => 'ъь', 'э' => 'эе', 'ю' => 'юу', 'я' => 'яа',
    ];

    $char = mb_strtolower((string)$char);

    return preg_split('//u', $map[$char] ?? $char, -1, PREG_SPLIT_NO_EMPTY);
}

function polimerGenerateTypoQueries($query, $maxVariants = 40)
{
    $query = mb_strtolower(trim((string)$query));
    $length = mb_strlen($query);

    if ($length < 3 || $length > 24)
        return [];

    if (!preg_match('/^[а-яё\-]+$/u', $query))
        return [];

    $variants = [];

    for ($i = 0; $i < $length; $i++)
    {
        $char = mb_substr($query, $i, 1);
        foreach (polimerGetCyrillicTypoSubstitutes($char) as $substitute)
        {
            if ($substitute === $char)
                continue;

            $candidate = mb_substr($query, 0, $i) . $substitute . mb_substr($query, $i + 1);
            if ($candidate !== $query)
                $variants[] = $candidate;
        }
    }

    $chars = preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY);
    for ($i = 0; $i < $length - 1; $i++)
    {
        $swapped = $chars;
        $tmp = $swapped[$i];
        $swapped[$i] = $swapped[$i + 1];
        $swapped[$i + 1] = $tmp;
        $variants[] = implode('', $swapped);
    }

    return array_slice(array_values(array_unique($variants)), 0, max(1, (int)$maxVariants));
}

function polimerBuildTokenTypoQueries($query, $maxVariants = 40, $maxVariantsPerToken = 12)
{
    $query = trim((string)$query);
    if ($query === '')
        return [];

    if (!preg_match('/[\s,]+/u', $query))
        return polimerGenerateTypoQueries($query, $maxVariants);

    $tokens = preg_split('/[\s,]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    if (count($tokens) < 2)
        return polimerGenerateTypoQueries($query, $maxVariants);

    $variants = [];
    foreach ($tokens as $index => $token)
    {
        if (mb_strlen($token) < 3)
            continue;

        foreach (polimerGenerateTypoQueries($token, $maxVariantsPerToken) as $variant)
        {
            if ($variant === mb_strtolower($token))
                continue;

            $rebuilt = $tokens;
            $rebuilt[$index] = $variant;
            $variants[] = implode(' ', $rebuilt);
        }
    }

    if (count($tokens) >= 2 && count($tokens) <= 4)
    {
        $tokenOptions = [];
        foreach ($tokens as $token)
        {
            $options = [mb_strtolower($token)];
            if (mb_strlen($token) >= 3)
            {
                foreach (polimerGenerateTypoQueries($token, $maxVariantsPerToken) as $variant)
                    $options[] = $variant;
            }
            $tokenOptions[] = array_values(array_unique($options));
        }

        $combined = [[]];
        foreach ($tokenOptions as $options)
        {
            $next = [];
            foreach ($combined as $prefix)
            {
                foreach ($options as $option)
                    $next[] = array_merge($prefix, [$option]);
            }
            $combined = $next;
        }

        foreach ($combined as $parts)
        {
            $candidate = implode(' ', $parts);
            if (mb_strtolower($candidate) !== mb_strtolower($query))
                $variants[] = $candidate;
        }
    }

    return array_slice(array_values(array_unique($variants)), 0, max(1, (int)$maxVariants));
}

function polimerCorrectSearchQueryByTokens($query, $iblockId = IBLOCK_CATALOG)
{
    $query = trim((string)$query);
    if ($query === '')
        return null;

    if (!empty(polimerSearchCatalogByTokens($query, $iblockId, 1)))
        return $query;

    foreach (polimerBuildSearchQueries($query, true) as $variant)
    {
        if (mb_strtolower($variant) === mb_strtolower($query))
            continue;

        if (!empty(polimerSearchCatalogByTokens($variant, $iblockId, 1)))
            return $variant;
    }

    $tokens = preg_split('/[\s,]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    if (count($tokens) < 2)
        return null;

    $corrected = $tokens;
    $changed = true;
    $passes = 0;

    while ($changed && $passes < count($tokens) * 2)
    {
        $changed = false;
        $passes++;

        foreach ($corrected as $index => $token)
        {
            if (mb_strlen($token) < 3)
                continue;

            foreach (polimerGenerateTypoQueries($token, 15) as $variant)
            {
                if ($variant === mb_strtolower($token))
                    continue;

                $candidateTokens = $corrected;
                $candidateTokens[$index] = $variant;
                $candidate = implode(' ', $candidateTokens);

                if (!empty(polimerSearchCatalogByTokens($candidate, $iblockId, 1)))
                {
                    $corrected = $candidateTokens;
                    $changed = true;
                    break 2;
                }
            }
        }
    }

    $result = implode(' ', $corrected);

    return !empty(polimerSearchCatalogByTokens($result, $iblockId, 1)) ? $result : null;
}

function polimerSuggestSearchCorrection($query, $iblockId = IBLOCK_CATALOG)
{
    $corrected = polimerCorrectSearchQueryByTokens($query, $iblockId);
    if (!$corrected || mb_strtolower($corrected) === mb_strtolower(trim((string)$query)))
        return null;

    return $corrected;
}

/**
 * Ослабленные варианты запроса, если точное совпадение пустое:
 * «счетчик горячей» → «счетчик», «горячей» и т.п.
 */
function polimerBuildRelaxedSearchQueries($query)
{
    $query = trim((string)$query);
    if ($query === '')
        return [];

    $tokens = preg_split('/[\s,]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    $tokens = array_values(array_filter($tokens, static function ($token) {
        return mb_strlen($token) >= 2;
    }));

    if (count($tokens) < 2)
        return [];

    $queries = [];

    // убираем слова с конца: «а б в» → «а б», «а»
    for ($len = count($tokens) - 1; $len >= 1; $len--)
        $queries[] = implode(' ', array_slice($tokens, 0, $len));

    // убираем слова с начала: «а б в» → «б в», «в»
    for ($start = 1; $start < count($tokens); $start++)
        $queries[] = implode(' ', array_slice($tokens, $start));

    // отдельные токены — сначала более длинные
    $byLength = $tokens;
    usort($byLength, static function ($left, $right) {
        return mb_strlen($right) <=> mb_strlen($left);
    });
    foreach ($byLength as $token)
        $queries[] = $token;

    $unique = [];
    $originalLower = mb_strtolower($query);
    foreach ($queries as $candidate)
    {
        $candidate = trim((string)$candidate);
        if ($candidate === '' || mb_strtolower($candidate) === $originalLower)
            continue;
        $unique[$candidate] = true;
    }

    return array_keys($unique);
}

function polimerBuildSearchQueries($query, $includeTypo = false)
{
    $query = trim((string)$query);
    if ($query === '')
        return [];

    $queries = [$query];

    if (CModule::IncludeModule('search'))
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/tools/language.php';

        $arLang = CSearchLanguage::GuessLanguage($query);
        if (is_array($arLang) && $arLang['from'] !== $arLang['to'])
        {
            $alt = CSearchLanguage::ConvertKeyboardLayout($query, $arLang['from'], $arLang['to']);
            if ($alt && $alt !== $query)
                $queries[] = $alt;
        }
    }

    $mixed = polimerConvertKeyboardLayoutMixed($query);
    if ($mixed !== $query)
        $queries[] = $mixed;

    $mixedChars = polimerConvertMixedLayoutChars($query);
    if ($mixedChars !== $query)
        $queries[] = $mixedChars;

    if ($includeTypo)
    {
        if (preg_match('/[\s,]+/u', $query))
            $queries = array_merge($queries, polimerBuildTokenTypoQueries($query));
        else
            $queries = array_merge($queries, polimerGenerateTypoQueries($query));
    }

    return array_values(array_unique(array_filter($queries)));
}

function polimerBuildCatalogTokenFilter(array $tokens, $iblockId = IBLOCK_CATALOG, $fullText = true)
{
    $filter = [
        'IBLOCK_ID' => (int)$iblockId,
        'ACTIVE' => 'Y',
        'ACTIVE_DATE' => 'Y',
    ];

    if (empty($tokens))
        return $filter;

    $tokenFilters = [];
    foreach ($tokens as $token)
    {
        if ($fullText)
        {
            $tokenFilters[] = [
                'LOGIC' => 'OR',
                ['?NAME' => $token],
                ['?PREVIEW_TEXT' => $token],
                ['?DETAIL_TEXT' => $token],
            ];
        }
        else
        {
            $tokenFilters[] = ['?NAME' => $token];
        }
    }

    if (count($tokenFilters) === 1)
        $filter[] = $tokenFilters[0];
    else
        $filter[] = array_merge(['LOGIC' => 'AND'], $tokenFilters);

    return $filter;
}

function polimerFetchCatalogElementIds(array $filter, $limit = 0)
{
    if (!CModule::IncludeModule('iblock'))
        return [];

    $navParams = ((int)$limit > 0) ? ['nTopCount' => (int)$limit] : false;
    $ids = [];
    $res = CIBlockElement::GetList(
        ['SHOW_COUNTER' => 'DESC', 'NAME' => 'ASC'],
        $filter,
        false,
        $navParams,
        ['ID']
    );

    while ($row = $res->GetNext())
    {
        $id = (int)($row['ID'] ?? 0);
        if ($id > 0)
            $ids[] = $id;
    }

    return $ids;
}

function polimerSearchCatalogByTokens($query, $iblockId = IBLOCK_CATALOG, $limit = 15, array $excludeIds = [], $fullText = false)
{
    if (!CModule::IncludeModule('iblock'))
        return [];

    $tokens = preg_split('/[\s,]+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY);
    $tokens = array_values(array_filter($tokens, static function ($token) {
        return mb_strlen($token) >= 2;
    }));

    if (empty($tokens))
        return [];

    $filter = polimerBuildCatalogTokenFilter($tokens, $iblockId, $fullText);

    if (!empty($excludeIds))
        $filter['!ID'] = $excludeIds;

    $items = [];
    $navParams = ((int)$limit > 0) ? ['nTopCount' => (int)$limit] : false;
    $res = CIBlockElement::GetList(
        ['SHOW_COUNTER' => 'DESC', 'NAME' => 'ASC'],
        $filter,
        false,
        $navParams,
        ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL']
    );

    while ($row = $res->GetNext())
    {
        $items[] = [
            'NAME' => $row['NAME'],
            'URL' => $row['DETAIL_PAGE_URL'],
            'MODULE_ID' => 'iblock',
            'PARAM1' => '1c_catalog',
            'PARAM2' => (int)$row['IBLOCK_ID'],
            'ITEM_ID' => (int)$row['ID'],
        ];
    }

    return $items;
}

/**
 * Релевантность названия к запросу (меньше = лучше).
 * 0 — точное совпадение, 1 — целое слово, 2 — начало слова,
 * 3 — подстрока в имени, 4 — в имени нет (попал из текста/поиска).
 */
function polimerScoreSearchNameRelevance($name, $query)
{
    $nameLower = mb_strtolower(trim((string)$name));
    $queryLower = mb_strtolower(trim((string)$query));
    if ($queryLower === '' || $nameLower === '')
        return 4;

    if ($nameLower === $queryLower)
        return 0;

    $tokens = preg_split('/[\s,]+/u', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
    $tokens = array_values(array_filter($tokens, static function ($token) {
        return mb_strlen($token) >= 2;
    }));

    if (empty($tokens))
        return 4;

    $worst = 0;
    $anyInName = false;

    foreach ($tokens as $token)
    {
        $tokenScore = 4;
        if (mb_strpos($nameLower, $token) === false)
        {
            $worst = max($worst, $tokenScore);
            continue;
        }

        $anyInName = true;
        $quoted = preg_quote($token, '/');

        if (preg_match('/(?:^|[^\p{L}\p{N}])' . $quoted . '(?:[^\p{L}\p{N}]|$)/u', $nameLower))
            $tokenScore = 1;
        elseif (preg_match('/(?:^|[^\p{L}\p{N}])' . $quoted . '/u', $nameLower))
            $tokenScore = 2;
        else
            $tokenScore = 3;

        $worst = max($worst, $tokenScore);
    }

    if (!$anyInName)
        return 4;

    return $worst;
}

function polimerRankCatalogIdsByQuery(array $ids, $query)
{
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (empty($ids) || !CModule::IncludeModule('iblock'))
        return $ids;

    $names = [];
    $res = CIBlockElement::GetList(
        [],
        ['ID' => $ids, 'IBLOCK_ID' => IBLOCK_CATALOG],
        false,
        false,
        ['ID', 'NAME']
    );
    while ($row = $res->Fetch())
        $names[(int)$row['ID']] = (string)$row['NAME'];

    $orderIndex = array_flip($ids);
    usort($ids, static function ($a, $b) use ($names, $query, $orderIndex) {
        $scoreA = polimerScoreSearchNameRelevance($names[$a] ?? '', $query);
        $scoreB = polimerScoreSearchNameRelevance($names[$b] ?? '', $query);
        if ($scoreA !== $scoreB)
            return $scoreA <=> $scoreB;

        return ($orderIndex[$a] ?? 0) <=> ($orderIndex[$b] ?? 0);
    });

    return $ids;
}

function polimerSearchCatalogAllIds($query, $iblockId = IBLOCK_CATALOG, $maxIds = 50000, $fullText = true)
{
    $query = trim((string)$query);
    if ($query === '')
        return [];

    $seen = [];
    $allIds = [];
    $queries = polimerBuildSearchQueries($query, true);

    foreach ($queries as $searchQuery)
    {
        if (count($allIds) >= $maxIds)
            break;

        $tokens = preg_split('/[\s,]+/u', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_filter($tokens, static function ($token) {
            return mb_strlen($token) >= 2;
        }));

        if (empty($tokens))
            continue;

        $filter = polimerBuildCatalogTokenFilter($tokens, $iblockId, $fullText);
        $remaining = $maxIds - count($allIds);
        if ($remaining <= 0)
            break;

        $ids = polimerFetchCatalogElementIds($filter, $remaining);

        foreach ($ids as $id)
        {
            if (isset($seen[$id]))
                continue;

            $seen[$id] = true;
            $allIds[] = $id;

            if (count($allIds) >= $maxIds)
                break 2;
        }
    }

    return $allIds;
}

function polimerSearchBitrixCatalogIds($query, array $arParams, $iblockId = IBLOCK_CATALOG, $maxIds = 50000)
{
    if (!CModule::IncludeModule('search'))
        return [];

    $query = trim((string)$query);
    if ($query === '')
        return [];

    $exFILTER = CSearchParameters::ConvertParamsToFilter($arParams, 'arrFILTER');

    $arFilter = [
        'QUERY' => $query,
        'SITE_ID' => SITE_ID,
    ];

    if (($arParams['CHECK_DATES'] ?? '') === 'Y')
        $arFilter['CHECK_DATES'] = 'Y';

    $obSearch = new CSearch();
    $obSearch->limit = max(500, (int)$maxIds);
    $obSearch->SetOptions([
        'ERROR_ON_EMPTY_STEM' => ($arParams['RESTART'] ?? '') !== 'Y',
        'NO_WORD_LOGIC' => isset($arParams['NO_WORD_LOGIC']) && $arParams['NO_WORD_LOGIC'] === 'Y',
    ]);
    $obSearch->Search($arFilter, ['CUSTOM_RANK' => 'DESC', 'RANK' => 'DESC', 'TITLE_RANK' => 'DESC'], $exFILTER);

    if ($obSearch->errorno !== 0)
        return [];

    $ids = [];
    while ($ar = $obSearch->GetNext())
    {
        if ((int)($ar['PARAM2'] ?? 0) !== (int)$iblockId)
            continue;

        $id = (int)($ar['ITEM_ID'] ?? 0);
        if ($id > 0 && !in_array($id, $ids, true))
            $ids[] = $id;

        if (count($ids) >= $maxIds)
            break;
    }

    return $ids;
}

function polimerEnhanceSearchPageResult(array &$arResult, array $arParams)
{
    $query = trim((string)($arResult['REQUEST']['~QUERY'] ?? $arResult['REQUEST']['QUERY'] ?? ''));
    if ($query === '')
        return;

    $iblockId = IBLOCK_CATALOG;
    // Сначала совпадения по названию, потом по тексту, потом модуль поиска Bitrix
    $nameIds = polimerSearchCatalogAllIds($query, $iblockId, 5000, false);
    $textIds = polimerSearchCatalogAllIds($query, $iblockId, 5000, true);
    $bitrixIds = polimerSearchBitrixCatalogIds($query, $arParams, $iblockId, 5000);

    $seen = [];
    $allIds = [];
    foreach ([$nameIds, $bitrixIds, $textIds] as $idList)
    {
        foreach ($idList as $id)
        {
            $id = (int)$id;
            if ($id <= 0 || isset($seen[$id]))
                continue;

            $seen[$id] = true;
            $allIds[] = $id;
        }
    }

    if (empty($allIds))
        return;

    $allIds = polimerRankCatalogIdsByQuery($allIds, $query);

    $arResult['POLIMER_PRODUCT_IDS'] = $allIds;
    $arResult['ROWS_COUNT'] = count($allIds);
    $arResult['SEARCH'] = array_map(static function ($id) use ($iblockId) {
        return [
            'ITEM_ID' => $id,
            'ID' => $id,
            'PARAM2' => $iblockId,
        ];
    }, $allIds);
}

function polimerSearchCatalogSections($query, $iblockId = IBLOCK_CATALOG, $maxSections = 500)
{
    $query = trim((string)$query);
    if ($query === '' || !CModule::IncludeModule('iblock'))
        return [];

    $maxSections = max(1, (int)$maxSections);
    $origTokens = preg_split('/[\s,]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    $origTokens = array_values(array_filter($origTokens, static function ($token) {
        return mb_strlen($token) >= 2;
    }));

    if (empty($origTokens))
        return [];

    $queries = polimerBuildSearchQueries($query, false);
    $found = [];
    $seen = [];

    foreach ($queries as $searchQuery)
    {
        if (count($found) >= $maxSections)
            break;

        $tokens = preg_split('/[\s,]+/u', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_filter($tokens, static function ($token) {
            return mb_strlen($token) >= 2;
        }));

        if (empty($tokens))
            continue;

        $filter = [
            'IBLOCK_ID' => (int)$iblockId,
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
        ];

        $tokenFilters = [];
        foreach ($tokens as $token)
            $tokenFilters[] = ['?NAME' => $token];

        if (count($tokenFilters) === 1)
            $filter[] = $tokenFilters[0];
        else
            $filter[] = array_merge(['LOGIC' => 'AND'], $tokenFilters);

        $res = CIBlockSection::GetList(
            ['NAME' => 'ASC'],
            $filter,
            false,
            ['ID', 'NAME', 'SECTION_PAGE_URL', 'PICTURE', 'ELEMENT_CNT']
        );

        while ($row = $res->GetNext())
        {
            $id = (int)$row['ID'];
            if (isset($seen[$id]))
                continue;

            $nameLower = mb_strtolower((string)$row['NAME']);
            $matchesOriginal = false;
            foreach ($origTokens as $token)
            {
                if (mb_strpos($nameLower, mb_strtolower($token)) !== false)
                {
                    $matchesOriginal = true;
                    break;
                }
            }

            if (!$matchesOriginal)
                continue;

            $seen[$id] = true;
            $found[] = $row;

            if (count($found) >= $maxSections)
                break;
        }
    }

    if (empty($found))
        return [];

    $queryLower = mb_strtolower($query);
    usort($found, static function ($a, $b) use ($queryLower) {
        $score = static function ($name) use ($queryLower) {
            $nameLower = mb_strtolower((string)$name);
            if ($nameLower === $queryLower)
                return 0;
            if (mb_strpos($nameLower, $queryLower) === 0)
                return 1;
            if (mb_strpos($nameLower, $queryLower) !== false)
                return 2;

            return 3;
        };

        $scoreA = $score($a['NAME']);
        $scoreB = $score($b['NAME']);
        if ($scoreA !== $scoreB)
            return $scoreA <=> $scoreB;

        return strcmp((string)$a['NAME'], (string)$b['NAME']);
    });

    return $found;
}

function polimerDisableCompositeForDynamicPages()
{
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    if (
        strpos($uri, '/search/') !== false
        || (($_REQUEST['ajax_call'] ?? '') === 'y')
    )
    {
        if (!defined('BX_COMPOSITE_DISABLE'))
            define('BX_COMPOSITE_DISABLE', true);

        if (class_exists('\Bitrix\Main\Composite\Helper'))
            \Bitrix\Main\Composite\Helper::setEnabled(false);

        polimerDisableSeometaBufferHandlerForSearch();
    }
}

function polimerDisableSeometaBufferHandlerForSearch()
{
    if (!function_exists('GetModuleEvents') || !function_exists('RemoveEventHandler'))
        return;

    foreach (GetModuleEvents('main', 'OnEndBufferContent', true) as $key => $event)
    {
        if (($event['TO_MODULE_ID'] ?? '') !== 'sotbit.seometa')
            continue;

        if (($event['TO_METHOD'] ?? '') === 'ChangeContent')
            RemoveEventHandler('main', 'OnEndBufferContent', $key);
    }
}

function polimerBuildSearchPageNav($currentPage, $pageSize, $totalCount, $queryString)
{
    $pageSize = max(1, (int)$pageSize);
    $totalCount = max(0, (int)$totalCount);
    $pageCount = max(1, (int)ceil($totalCount / $pageSize));
    $currentPage = max(1, min((int)$currentPage, $pageCount));

    if ($pageCount <= 1)
        return '';

    $queryString = trim((string)$queryString);
    $baseUrl = '/search/' . ($queryString !== '' ? '?' . $queryString : '');

    $buildUrl = static function ($page) use ($baseUrl, $queryString) {
        if ($page <= 1)
            return $baseUrl;

        $params = [];
        if ($queryString !== '')
            parse_str($queryString, $params);

        $params['PAGEN_1'] = $page;

        return '/search/?' . http_build_query($params);
    };

    $window = 5;
    $startPage = max(1, $currentPage - (int)floor($window / 2));
    $endPage = min($pageCount, $startPage + $window - 1);
    $startPage = max(1, $endPage - $window + 1);

    $somePage = [];
    for ($page = $startPage; $page <= $endPage; $page++)
        $somePage[$page] = $buildUrl($page);

    $html = '<div class="ns__paginator cl">';
    $html .= '<div class="name">Страницы:</div>';

    if ($currentPage === 1)
        $html .= '<a href="#" class="arrow left"><span></span><span></span></a>';
    else
        $html .= '<a href="' . htmlspecialcharsbx($buildUrl($currentPage - 1)) . '" class="arrow left aractive"><span></span><span></span></a>';

    $html .= '<div class="pages cl">';
    for ($page = $startPage; $page <= $endPage; $page++)
    {
        if ($page === $currentPage)
            $html .= '<a href="" class="page active">' . $page . '</a>';
        else
            $html .= '<a href="' . htmlspecialcharsbx($somePage[$page]) . '" class="page">' . $page . '</a>';
    }
    $html .= '</div>';

    if ($currentPage === $pageCount)
        $html .= '<a href="#" class="arrow right"><span></span><span></span></a>';
    else
        $html .= '<a href="' . htmlspecialcharsbx($buildUrl($currentPage + 1)) . '" class="arrow right aractive"><span></span><span></span></a>';

    $html .= '</div>';

    return $html;
}

function polimerEnhanceTitleSearchResult(array &$arResult, array $arParams)
{
    if (empty($arResult['query']) || !CModule::IncludeModule('search'))
        return;

    $existingIds = [];
    $productCount = 0;

    foreach ($arResult['CATEGORIES'] as &$category)
    {
        if (empty($category['ITEMS']) || !is_array($category['ITEMS']))
            continue;

        foreach ($category['ITEMS'] as $item)
        {
            if (!empty($item['TYPE']) && $item['TYPE'] === 'all')
                continue;

            if (!empty($item['ITEM_ID']) && substr((string)$item['ITEM_ID'], 0, 1) !== 'S')
            {
                $existingIds[] = (int)$item['ITEM_ID'];
                $productCount++;
            }
        }
    }
    unset($category);

    $topCount = (int)($arParams['TOP_COUNT'] ?? 15);
    if ($topCount <= 0)
        $topCount = 15;

    if ($productCount >= $topCount)
        return;

    $categoryIndex = 0;
    if (empty($arResult['CATEGORIES']))
    {
        $categoryTitle = trim($arParams['CATEGORY_0_TITLE'] ?? '');
        if ($categoryTitle === '' && !empty($arParams['CATEGORY_0']))
            $categoryTitle = is_array($arParams['CATEGORY_0']) ? implode(', ', $arParams['CATEGORY_0']) : $arParams['CATEGORY_0'];

        $arResult['CATEGORIES'][$categoryIndex] = [
            'TITLE' => htmlspecialcharsbx($categoryTitle),
            'ITEMS' => [],
        ];
    }
    else
    {
        $categoryKeys = array_keys($arResult['CATEGORIES']);
        $categoryIndex = (int)$categoryKeys[0];
    }

    $originalQuery = trim((string)$arResult['query']);
    // Сначала без опечаток — быстрее; typo — только если всё ещё пусто
    $queries = polimerBuildSearchQueries($originalQuery, false);

    foreach ($queries as $searchQuery)
    {
        if ($productCount >= $topCount)
            break;

        if (!isset($arResult['CATEGORIES'][$categoryIndex]))
            break;

        $beforeCount = $productCount;

        $exFILTER = [
            0 => CSearchParameters::ConvertParamsToFilter($arParams, 'CATEGORY_' . $categoryIndex),
        ];
        $exFILTER[0]['LOGIC'] = 'OR';

        if (($arParams['CHECK_DATES'] ?? '') === 'Y')
            $exFILTER['CHECK_DATES'] = 'Y';

        $obTitle = new CSearchTitle;
        $obTitle->setMinWordLength($_REQUEST['l'] ?? 2);

        if (!$obTitle->Search($searchQuery, $topCount, $exFILTER, false, $arParams['ORDER'] ?? 'rank'))
            continue;

        while ($ar = $obTitle->Fetch())
        {
            $itemId = (int)$ar['ITEM_ID'];
            if ($itemId <= 0 || in_array($itemId, $existingIds, true))
                continue;

            $arResult['CATEGORIES'][$categoryIndex]['ITEMS'][] = [
                'NAME' => $ar['NAME'],
                'URL' => htmlspecialcharsbx($ar['URL']),
                'MODULE_ID' => $ar['MODULE_ID'],
                'PARAM1' => $ar['PARAM1'],
                'PARAM2' => $ar['PARAM2'],
                'ITEM_ID' => $ar['ITEM_ID'],
            ];

            $existingIds[] = $itemId;
            $productCount++;

            if ($productCount >= $topCount)
                break 2;
        }

        if ($beforeCount === 0 && $productCount > 0 && mb_strtolower($searchQuery) !== mb_strtolower($originalQuery))
            $arResult['SEARCH_QUERY_CORRECTED'] = $searchQuery;
    }

    if ($productCount >= $topCount)
        return;

    foreach ($queries as $searchQuery)
    {
        if ($productCount >= $topCount)
            break;

        $beforeCount = $productCount;

        $fallbackItems = polimerSearchCatalogByTokens(
            $searchQuery,
            IBLOCK_CATALOG,
            $topCount - $productCount,
            $existingIds
        );

        foreach ($fallbackItems as $item)
        {
            $itemId = (int)$item['ITEM_ID'];
            if ($itemId <= 0 || in_array($itemId, $existingIds, true))
                continue;

            $arResult['CATEGORIES'][$categoryIndex]['ITEMS'][] = $item;
            $existingIds[] = $itemId;
            $productCount++;

            if ($productCount >= $topCount)
                break 2;
        }

        if ($beforeCount === 0 && $productCount > 0 && mb_strtolower($searchQuery) !== mb_strtolower($originalQuery))
            $arResult['SEARCH_QUERY_CORRECTED'] = $searchQuery;
    }

    // Если точных совпадений нет — ближайшие по укороченному запросу
    if ($productCount > 0)
        return;

    foreach (polimerBuildRelaxedSearchQueries($originalQuery) as $relaxedQuery)
    {
        if ($productCount >= $topCount)
            break;

        $beforeCount = $productCount;

        $exFILTER = [
            0 => CSearchParameters::ConvertParamsToFilter($arParams, 'CATEGORY_' . $categoryIndex),
        ];
        $exFILTER[0]['LOGIC'] = 'OR';
        if (($arParams['CHECK_DATES'] ?? '') === 'Y')
            $exFILTER['CHECK_DATES'] = 'Y';

        $obTitle = new CSearchTitle;
        $obTitle->setMinWordLength($_REQUEST['l'] ?? 2);

        if ($obTitle->Search($relaxedQuery, $topCount, $exFILTER, false, $arParams['ORDER'] ?? 'rank'))
        {
            while ($ar = $obTitle->Fetch())
            {
                $itemId = (int)$ar['ITEM_ID'];
                if ($itemId <= 0 || in_array($itemId, $existingIds, true))
                    continue;

                $arResult['CATEGORIES'][$categoryIndex]['ITEMS'][] = [
                    'NAME' => $ar['NAME'],
                    'URL' => htmlspecialcharsbx($ar['URL']),
                    'MODULE_ID' => $ar['MODULE_ID'],
                    'PARAM1' => $ar['PARAM1'],
                    'PARAM2' => $ar['PARAM2'],
                    'ITEM_ID' => $ar['ITEM_ID'],
                ];

                $existingIds[] = $itemId;
                $productCount++;

                if ($productCount >= $topCount)
                    break;
            }
        }

        if ($productCount < $topCount)
        {
            $fallbackItems = polimerSearchCatalogByTokens(
                $relaxedQuery,
                IBLOCK_CATALOG,
                $topCount - $productCount,
                $existingIds
            );

            foreach ($fallbackItems as $item)
            {
                $itemId = (int)$item['ITEM_ID'];
                if ($itemId <= 0 || in_array($itemId, $existingIds, true))
                    continue;

                $arResult['CATEGORIES'][$categoryIndex]['ITEMS'][] = $item;
                $existingIds[] = $itemId;
                $productCount++;

                if ($productCount >= $topCount)
                    break;
            }
        }

        if ($beforeCount === 0 && $productCount > 0)
        {
            $arResult['SEARCH_QUERY_CORRECTED'] = $relaxedQuery;
            $arResult['SEARCH_QUERY_RELAXED'] = true;
            break;
        }
    }

    // Опечатки — только если ближайшие тоже не нашлись
    if ($productCount > 0)
        return;

    $typoQueries = polimerBuildSearchQueries($originalQuery, true);
    foreach ($typoQueries as $searchQuery)
    {
        if ($productCount >= $topCount)
            break;

        if (mb_strtolower($searchQuery) === mb_strtolower($originalQuery))
            continue;

        $beforeCount = $productCount;

        $fallbackItems = polimerSearchCatalogByTokens(
            $searchQuery,
            IBLOCK_CATALOG,
            $topCount - $productCount,
            $existingIds
        );

        foreach ($fallbackItems as $item)
        {
            $itemId = (int)$item['ITEM_ID'];
            if ($itemId <= 0 || in_array($itemId, $existingIds, true))
                continue;

            $arResult['CATEGORIES'][$categoryIndex]['ITEMS'][] = $item;
            $existingIds[] = $itemId;
            $productCount++;

            if ($productCount >= $topCount)
                break 2;
        }

        if ($beforeCount === 0 && $productCount > 0)
        {
            $arResult['SEARCH_QUERY_CORRECTED'] = $searchQuery;
            break;
        }
    }
}
