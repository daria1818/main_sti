<?php
if(date('d.m.Y') != date('d.m.Y', strtotime("last day of this month this year")))
	exit();
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4) . '';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Rtop\KPI\BalanceTable;

if(!Loader::IncludeModule("rtop.kpi") || !Loader::IncludeModule("crm"))
	return;

$users = BalanceTable::getList(['select' => ['ID']])->fetchAll();

foreach($users ?:[] as $user){
	BalanceTable::update($user['ID'], ['BALANCE' => 0]);
}