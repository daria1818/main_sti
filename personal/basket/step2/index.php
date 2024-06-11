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
        'STEP' => 2,
    )
);

if(is_object($USER)) {
    if(!$USER->IsAuthorized()) {
        $APPLICATION->IncludeComponent(
            'bitrix:system.auth.form',
            '',
            Array(
                'COMPONENT_TEMPLATE'  => '.default',
                'REGISTER_URL'        => URL_REGISTRATION_PATH,
                'FORGOT_PASSWORD_URL' => URL_FORGOT_PASSWORD_PATH,
                'PROFILE_URL'         => URL_PROFILE_PATH,
                'SHOW_ERRORS'         => 'Y'
            )
        );
    } else {
        LocalRedirect(URL_BASKET_STEP3_PATH);

        die;
        ?>
        <div class="col-xs-12 col-sm-12">
            <form action="<?php echo URL_BASKET_STEP3_PATH; ?>" method="get" id="process-account_form<?php echo $arResult['RND']?>" class="box">
                <h3 class="page-subheading" style="text-align: center;">Вы вошли как <?php echo  $USER->GetFullName(); ?></h3>
                <div class="form_content clearfix">
                    <div class="form-group">
                        <button type="submit" class="account_input form-control">Оформить заказ</button>
                    </div>
                </div>
            </form>
            <form action="<?=$arResult['AUTH_URL']?>" method="get" id="exit-account_form<?php echo $arResult['RND']?>" class="box">
                <div class="form_content clearfix">
                    <h5 class="page-subheading" style="text-align: center;">Выйти и зайти под другой учетной записью</h5 class="page-subheading">
                    <div class="form-group">
                        <?foreach ($arResult['GET'] as $key => $value):?>
                            <input type="hidden" name="<?php echo $key?>" value="<?=$value?>" />
                        <?endforeach?>
                        <input type="hidden" name="logout" value="yes" />
                        <input type="hidden" name="logout_butt" value="exit" />
                        <button type="submit" class="account_input form-control">Выйти</button>
                    </div>
                </div>
            </form>
        </div>
        <?php
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