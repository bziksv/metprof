<?php
$arUrlRewrite=array (
  0 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => '',
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/stssync/calendar/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/calendar/index.php',
    'SORT' => 100,
  ),
  22 => 
  array (
    'CONDITION' => '#^/personal/order/#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.order',
    'PATH' => '/personal/order/index.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/personal/#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.section',
    'PATH' => '/personal/index.php',
    'SORT' => 100,
  ),
  14 => 
  array (
    'CONDITION' => '#^/articles/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/articles/index.php',
    'SORT' => 100,
  ),
  25 => 
  array (
    'CONDITION' => '#^/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  24 => 
  array (
    'CONDITION' => '#^/store/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store',
    'PATH' => '/store/index.php',
    'SORT' => 100,
  ),
  12 => 
  array (
    'CONDITION' => '#^/sale/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/sale/index.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
  17 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  23 => 
  array (
    'CONDITION' => '#^/ral/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/ral/index.php',
    'SORT' => 100,
  ),
);
