#!/usr/bin/php
<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$filePatch = "/home/bitrix/www/log/tst.log";
if($file = fopen($filePatch, 'a'))
{
	$data = "crontab test log check";
	fwrite($file, $data);
	fclose($file);

}
echo "crontab test log check\n";		
?>