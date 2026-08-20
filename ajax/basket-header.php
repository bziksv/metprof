<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require __DIR__ . '/metprof_basket_helper.php';

if (class_exists('\Bitrix\Main\Composite\Helper')) {
    \Bitrix\Main\Composite\Helper::setEnabled(false);
}

$cart = metprofGetBasketTotals();

header('Content-Type: text/html; charset=' . LANG_CHARSET);
header('X-Metprof-Cart-Count: ' . $cart['count']);
header('X-Metprof-Cart-Price: ' . rawurlencode($cart['price']));
?>
<a href="<?= SITE_DIR ?>personal/cart/" class="header__cart cart">
    <span class="cart__number"><?= (int)$cart['count'] ?></span>
    <span class="cart__sum"><span class="cart__sum--numbers"><?= $cart['price'] ?></span> </span>
</a>
