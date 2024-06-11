<?php

include dirname(__FILE__) . "/install/version.php";

$ALFABANK_CONFIG = array(
	'MODULE_ID' => 'kupilegko.payment',
	'BANK_NAME' => 'Alfabank',
	'RBS_PROD_URL_AB' => 'https://pay.alfabank.ru/payment/rest/',
	'RBS_TEST_URL_AB' => 'https://web.rbsuat.com/ab/rest/',
	'ISO' => array(
	    'USD' => 840,
	    'EUR' => 978,
	    'RUB' => 810,
	    'RUR' => 810,
	    'BYN' => 933
	),
	'MODULE_VERSION' => $arModuleVersion['VERSION'],
	'CALLBACK_BROADCAST' => false
);

