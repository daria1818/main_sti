<?php
if(date('d.m.Y') != date('d.m.Y', strtotime("last day of this month this year")))
	exit();
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4) . '';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Mail\MailMessageUidTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Entity;
use Rtop\KPI\HandlersTable;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\EventManager;
use Rtop\KPI\Main as RtopMain;
use Rtop\KPI\Logger as Log;

if(!Loader::IncludeModule("rtop.kpi") || !Loader::IncludeModule("crm"))
	return;

$handlersCron = HandlersTable::getList([
	'filter' => ['PERIOD' => 'month', 'AUTO' => 'Y'],
	'select' => ['ID', 'CODE', 'NAME', 'EVENT_' => 'EVENT'],
	'runtime' => [
		'EVENT' => [
			'data_type' => '\Rtop\KPI\EventsTable',
			'reference' => [
				'=this.ID' => 'ref.HANDLER'
			]
		]
	]
])->fetchAll();

if(empty($handlersCron))
	return;

$departList = [];
$roleList = [];
$funcList = [];

foreach ($handlersCron as $handler)
{
	$funcList[$handler['ID']] = [
		'FUNCTION' => $handler['CODE'],
		'HANDLER_ID' => $handler['EVENT_HANDLER'],
		'VALUE' => $handler['EVENT_VALUE'],
		'MIN_COST' => $handler['EVENT_MIN_COST'],
		'MAX_COST' => $handler['EVENT_MAX_COST'],
		'ROLE' => $handler['EVENT_ROLE'],
		'DEPARTMENT' => $handler['EVENT_DEPARTMENT'],
		'NAME' => $handler['NAME'],
	];

	$departList = array_merge($departList, $handler['EVENT_DEPARTMENT']);
	$roleList = array_merge($roleList, $handler['EVENT_ROLE']);
}

$departList = array_unique($departList);
$roleList = array_unique($roleList);

$arFilterUser = [
	'ROLE' => $roleList
];
if(!empty($departList))
	$arFilterUser['DEPARTMENT'] = $departList;

$users = BalanceTable::getList(['filter' => $arFilterUser, 'select' => ['ID', 'USERID', 'DEPARTMENT', 'ROLE', 'BALANCE']])->fetchAll();
if(empty($users))
	return;

$userList = [];
foreach($users as $user){
	$item = [
		'ID' => $user['ID'],
		'BALANCE' => $user['BALANCE'],
		'DEPARTMENT' => $user['DEPARTMENT'],
		'ROLE' => $user['ROLE'],
	];
	$userList[$user['USERID']] = $item;
}

$dates = [
	'FROM' => new DateTime(date('d.m.Y', strtotime("first day of this month this year")) . "00:00:00"),
	'TO' => new DateTime(date('d.m.Y', strtotime("last day of this month this year")) . "23:59:59"),
];

// $dates = [
// 	'FROM' => "01.11.2022 00:00:00",
// 	'TO' => "30.11.2022 23:59:59",
// ];

$userList = RtopMain::getOrderList($dates, $userList);

foreach($funcList as $func)
{
	$filter = [];
	foreach($userList as $id => $user){
		if(((!empty($func['DEPARTMENT']) && in_array($user['DEPARTMENT'], $func['DEPARTMENT'])) || empty($func['DEPARTMENT'])) && in_array($user['ROLE'], $func['ROLE']))
			$filter[$id] = $user;
	}
	$functionName = $func['FUNCTION'];
	EventManager::${"functionName"}($filter, $func);
}

/*Обнуляем баланс*/
foreach($userList as $id => $user){
	BalanceTable::update($user['ID'], ['BALANCE' => 0]);
}
/*Обновляем план на след. месяц*/
$plans = \Rtop\KPI\PlaneTable::getList(['select' => ['ID', 'HISTORY']])->fetchAll();
$next_month = date('m')+1;
$next_month = $next_month > 12 ? '1' : $next_month;
$year = date('m') == 12 ? date('Y')+1 : date('Y');
foreach ($plans as &$plan) {
    $value = end($plan['HISTORY'][date('Y')]);
    $plan['HISTORY'][$year][$next_month] = $value;
    \Rtop\KPI\PlaneTable::update($plan['ID'], ['HISTORY' => $plan['HISTORY']]);
}