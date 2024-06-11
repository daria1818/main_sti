<?php

/** @noinspection PhpIncludeInspection */
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
        'STEP' => 3,
    )
);

if(is_object($USER)) {
    if($USER->IsAuthorized()) {
        $APPLICATION->IncludeComponent(
            'xguard:sale.order.ajax',
            '',
            Array(
                'COMPONENT_TEMPLATE'          => '.default',
                'PAY_FROM_ACCOUNT'            => 'Y',
                'ONLY_FULL_PAY_FROM_ACCOUNT'  => 'N',
                'COUNT_DELIVERY_TAX'          => 'Y',
                'ALLOW_AUTO_REGISTER'         => 'N',
                'SEND_NEW_USER_NOTIFY'        => 'Y',
                'DELIVERY_NO_AJAX'            => 'Y',
                'DELIVERY_NO_SESSION'         => 'Y',
                'TEMPLATE_LOCATION'           => '.default',
                'DELIVERY_TO_PAYSYSTEM'       => 'd2p',
                'USE_PREPAYMENT'              => 'N',
                'ALLOW_NEW_PROFILE'           => 'Y',
                'SHOW_PAYMENT_SERVICES_NAMES' => 'Y',
                'SHOW_STORES_IMAGES'          => 'N',
                'PATH_TO_BASKET'              => '/personal/basket/',
                'PATH_TO_FINAL_BASKET'        => '/personal/basket/step4/',
                'PATH_TO_PERSONAL'            => '/personal/',
                'PATH_TO_PAYMENT'             => '/personal/payment/',
                'PATH_TO_AUTH'                => '/auth/',
                'SET_TITLE'                   => 'N',
                'DISABLE_BASKET_REDIRECT'     => 'Y',
                'PRODUCT_COLUMNS'             => array()
            )
        );
    } else {
        LocalRedirect(URL_BASKET_STEP2_PATH);

        die;
    }
}

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

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';