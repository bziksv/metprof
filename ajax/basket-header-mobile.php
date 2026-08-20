<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require __DIR__ . '/metprof_basket_helper.php';

if (class_exists('\Bitrix\Main\Composite\Helper')) {
    \Bitrix\Main\Composite\Helper::setEnabled(false);
}

$cart = metprofGetBasketTotals();

header('Content-Type: text/html; charset=' . LANG_CHARSET);
header('X-Metprof-Cart-Count: ' . $cart['count']);
?>
<a href="<?= SITE_DIR ?>personal/cart/" class="hmobile__cart cart">
    <span class="cart__number"><?= (int)$cart['count'] ?></span>
</a>
