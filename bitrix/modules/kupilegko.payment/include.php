<?php
use Bitrix\Main\Loader;

require dirname(__FILE__) ."/config.php";

Loader::registerAutoLoadClasses(
	$ALFABANK_CONFIG['MODULE_ID'],
	array(
        '\Alfabank\Payments\Gateway' => 'lib/rbs/Gateway.php',
        '\Alfabank\Payments\Discount' => 'lib/rbs/Discount.php',
	)
);
?>