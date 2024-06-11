<?
require __DIR__ . '/config.php';

$kupilegko_payment_default_option = array(
		'BANK_NAME' => $ALFABANK_CONFIG['BANK_NAME'],
		'MODULE_ID' => $ALFABANK_CONFIG['MODULE_ID'],
		'RBS_PROD_URL_AB' => $ALFABANK_CONFIG['RBS_PROD_URL_AB'],
		'RBS_TEST_URL_AB' => $ALFABANK_CONFIG['RBS_TEST_URL_AB'],
		'MODULE_VERSION' => $ALFABANK_CONFIG['MODULE_VERSION'],
		'ISO' => serialize($ALFABANK_CONFIG['ISO']),
		'RESULT_ORDER_STATUS' => 'FALSE',
		'OPTION_PHONE' => 'PHONE',
		'OPTION_EMAIL' => 'EMAIL',
		'OPTION_FIO' => 'FIO',
		'TAX_DEFAULT' => 0,
		'CALLBACK_ENABLED' => serialize(array()),
		'AUTO_REDIRECT_EXCEPTIONS' => serialize(array(
			'/personal/orders/'
		))
    );

?>