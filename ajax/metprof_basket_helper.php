<?php
if (!defined('B_PROLOG_INCLUDED')) {
    die();
}

/**
 * Считает корзину через CSaleBasket::GetList (ORDER_ID => false),
 * т.к. BasketComponentHelper (D7) не видит строки с ORDER_ID = 0.
 *
 * @param int|null $fuserId
 * @return array{count: int, price_raw: float, price: string}
 */
function metprofGetBasketTotals($fuserId = null)
{
    if (!CModule::IncludeModule('sale')) {
        return ['count' => 0, 'price_raw' => 0.0, 'price' => '0 руб.'];
    }

    if ($fuserId === null) {
        $fuserId = (int)CSaleBasket::GetBasketUserID(true);
    }

    if ($fuserId <= 0) {
        return ['count' => 0, 'price_raw' => 0.0, 'price' => '0 руб.'];
    }

    $count = 0;
    $priceRaw = 0.0;

    $res = CSaleBasket::GetList(
        ['ID' => 'ASC'],
        [
            'FUSER_ID' => $fuserId,
            'LID' => SITE_ID,
            'ORDER_ID' => false,
            'CAN_BUY' => 'Y',
            'DELAY' => 'N',
        ],
        false,
        false,
        ['ID', 'QUANTITY', 'PRICE']
    );

    while ($row = $res->Fetch()) {
        $count++;
        $priceRaw += (float)$row['PRICE'] * (float)$row['QUANTITY'];
    }

    $price = '0 руб.';
    if (CModule::IncludeModule('currency')) {
        $price = CCurrencyLang::CurrencyFormat(
            $priceRaw,
            \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency(SITE_ID),
            true
        );
    }

    if (class_exists('\Bitrix\Sale\BasketComponentHelper')) {
        \Bitrix\Sale\BasketComponentHelper::setFUserBasketQuantity($fuserId, $count, SITE_ID);
        \Bitrix\Sale\BasketComponentHelper::setFUserBasketPrice($fuserId, $priceRaw, SITE_ID);
    }

    return [
        'count' => $count,
        'price_raw' => $priceRaw,
        'price' => $price,
    ];
}
