<?php

include dirname(__FILE__) . "/install/version.php";

$ALFABANK_CONFIG = array(
	'MODULE_ID' => 'kupilegko.payment',
	'BANK_NAME' => 'Alfabank',
	'RBS_PROD_URL_AB' => 'https://pay.alfabank.ru/payment/rest/',
	'RBS_TEST_URL_AB' => 'https://alfa.rbsuat.com/payment/rest/',

	'RBS_PROD_URL_ALT' => 'https://payment.alfabank.ru/payment/rest/',
	'RBS_PROD_URL_ALT_PREFIX' => 'r-',

	'ISO' => array(
		'USD' => 840,
		'EUR' => 978,
		'RUB' => 643,
		'BYN' => 933
	),
	'MODULE_VERSION' => $arModuleVersion['VERSION'],
	'CALLBACK_BROADCAST' => false,
	'DISCOUNT_HELPER' => false,
	'IGNORE_PRODUCT_TAX' => false,
	"MEASUREMENT_NAME" => 'шт', //FFD v.1.05
	"MEASUREMENT_CODE" => 0, //FFD v.1.2
	"RBS_ENABLE_CALLBACK" => false,
	'CANCEL_ORDER_BY_TIMEOUT' => false,
);
