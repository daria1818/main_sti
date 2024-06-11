<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
$APPLICATION->SetTitle('Оформление заказа: корзина');

$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    Array(
        'COMPONENT_TEMPLATE' => '.default',
        'AREA_FILE_SHOW' => 'file',
        'AREA_FILE_SUFFIX' => 'inc',
        'EDIT_TEMPLATE' => 'standard.php',
        'PATH' => '/include/basket-header.php',
        'STEP' => 1,
    )
);

$APPLICATION->IncludeComponent(
    'bitrix:sale.basket.basket',
    '',
    Array(
        'COMPONENT_TEMPLATE'            => '.default',
        'COLUMNS_LIST'                  => array(
            'PROPERTY_CML2_ARTICLE',
            'PREVIEW_PICTURE',
            'NAME',
            'PRICE',
            'QUANTITY',
            'SUM',
            'DISCOUNT',
            'DELETE',
            'TYPE',
            'PROPERTY_SPETSTOVAR'
        ),
        'PATH_TO_ORDER'                 => '/personal/basket/step1/',
        'HIDE_COUPON'                   => 'N',
        'PRICE_VAT_SHOW_VALUE'          => 'Y',
        'COUNT_DISCOUNT_4_ALL_QUANTITY' => 'Y',
        'USE_PREPAYMENT'                => 'N',
        'QUANTITY_FLOAT'                => 'N',
        'SET_TITLE'                     => 'N',
        'ACTION_VARIABLE'               => 'action',
        'OFFERS_PROPS'                  => array('PROPERTY_'.ELEMENT_PROP_LINK)
    )
);

$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    Array(
        'COMPONENT_TEMPLATE' => '.default',
        'AREA_FILE_SHOW' => 'file',
        'AREA_FILE_SUFFIX' => 'inc',
        'EDIT_TEMPLATE' => 'standard.php',
        'PATH' => '/include/basket-footer.php'
    )
);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';