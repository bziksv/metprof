<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$quantity = (float)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);
$type = (int)($_POST['type'] ?? $_GET['type'] ?? 0);

if (!$id) {
    die('Ошибка добавления товара в корзину');
}

if ($quantity <= 0 || !is_finite($quantity)) {
    $quantity = 1;
}

CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');
require __DIR__ . '/metprof_basket_helper.php';

/**
 * @return array{count: int, price: string}
 */
function metprofGetBasketHeaderData()
{
    $cart = metprofGetBasketTotals();

    return [
        'count' => $cart['count'],
        'price' => $cart['price'],
    ];
}

/**
 * @param string $message
 */
function metprofBasketResponse($message)
{
    $cart = metprofGetBasketHeaderData();
    header('X-Metprof-Cart-Count: ' . $cart['count']);
    header('X-Metprof-Cart-Price: ' . rawurlencode($cart['price']));
    echo $message;
    echo '<!--METPROF_CART:' . json_encode($cart, JSON_UNESCAPED_UNICODE) . '-->';
}

/**
 * @param int $productId
 * @return float
 */
function metprofGetBasketProductQuantity($productId)
{
    $fuserId = CSaleBasket::GetBasketUserID(true);
    if (!$fuserId) {
        return 0;
    }

    $quantity = 0;
    $res = CSaleBasket::GetList(
        [],
        [
            'FUSER_ID' => $fuserId,
            'PRODUCT_ID' => $productId,
            'LID' => SITE_ID,
            'ORDER_ID' => false,
            'CAN_BUY' => 'Y',
            'DELAY' => 'N',
        ],
        false,
        false,
        ['QUANTITY']
    );

    while ($row = $res->Fetch()) {
        $quantity += (float)$row['QUANTITY'];
    }

    return $quantity;
}

if (!empty($_POST['props']) && is_array($_POST['props'])) {
    $propsQuantity = $_POST['props'][1]['VALUE'] ?? $quantity;
    if (Add2BasketByProductID($id, $propsQuantity, [], $_POST['props'])) {
        metprofBasketResponse('Товар успешно добавлен в корзину');
    } else {
        metprofBasketResponse('Ошибка!');
    }
    return;
}

// Листовые товары (м²) — минимум 20, штучные (type=5) и расчётные (type=6) без ограничения.
if ($type !== 5 && $type !== 6 && $quantity < 20) {
    die('Минимальное кол-во 20');
}

$catalogProduct = CCatalogProduct::GetByID($id);
$stockQuantity = $catalogProduct ? (float)$catalogProduct['QUANTITY'] : 0;
$basketQuantity = metprofGetBasketProductQuantity($id);
$availableQuantity = max(0, $stockQuantity - $basketQuantity);

if ($quantity > $availableQuantity) {
    if ($basketQuantity > 0 && $availableQuantity <= 0) {
        metprofBasketResponse('Товар уже в корзине. На складе: ' . (int)$stockQuantity . ' шт.');
    } elseif ($basketQuantity > 0) {
        metprofBasketResponse('В корзине уже ' . (int)$basketQuantity . ' шт. Доступно ещё: ' . (int)$availableQuantity . ' (на складе: ' . (int)$stockQuantity . ')');
    } elseif ($catalogProduct) {
        metprofBasketResponse('Запрашиваемое кол-во превышает остаток. На складе: ' . (int)$stockQuantity);
    } else {
        metprofBasketResponse('Товара нет в наличии');
    }
    return;
}

if (Add2BasketByProductID($id, $quantity)) {
    metprofBasketResponse('Товар успешно добавлен в корзину');
} elseif ($catalogProduct) {
    $basketQuantity = metprofGetBasketProductQuantity($id);
    if ($basketQuantity > 0) {
        metprofBasketResponse('Товар уже в корзине. На складе: ' . (int)$stockQuantity . ' шт.');
    } else {
        metprofBasketResponse('Запрашиваемое кол-во превышает остаток. На складе: ' . (int)$stockQuantity);
    }
} else {
    metprofBasketResponse('Товара нет в наличии');
}
