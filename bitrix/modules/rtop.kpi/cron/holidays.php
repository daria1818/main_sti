<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4) . '';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity;
use Bitrix\Calendar\Internals\EventTable;
use Rtop\KPI\HandlersTable;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\Main as RtopMain;
use Rtop\KPI\Logger as Log;

if(!Loader::IncludeModule("rtop.kpi") || !Loader::IncludeModule("crm") || !Loader::includeModule("calendar"))
	return;

$handlersJs = HandlersTable::getList([
	'filter' => ['PERIOD' => 'day', 'AUTO' => 'Y', 'FUNCTION' => ['OnCalendarEntryExhibition', 'OnCalendarEntryOffsite', 'OnTimeManWeekend']],
	'select' => ['ID', 'FUNCTION', 'NAME', 'EVENT_' => 'EVENT'],
	'runtime' => [
		'EVENT' => [
			'data_type' => '\Rtop\KPI\EventsTable',
			'reference' => [
				'=this.ID' => 'ref.HANDLER'
			]
		]
	]
])->fetchAll();

$handlersList = [];
$roleList = [];
$departList = [];
foreach ($handlersJs ?: [] as $field) {
	$handlersList[$field['FUNCTION']] = $field;
	if(!empty($field['EVENT_DEPARTMENT']))
		$departList = array_merge($departList, $field['EVENT_DEPARTMENT']);
	if(!empty($field['EVENT_ROLE']))
		$roleList = array_merge($roleList, $field['EVENT_ROLE']);
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
foreach($users as $user)
{
	$userList[$user['USERID']] = $user;
}

$date = ['FROM' => new DateTime(date('d.m.Y') . "00:00:00"), 'TO' => new DateTime(date('d.m.Y') . "23:59:59")];
$keyUsers = array_keys($userList);

if(isset($handlersList['OnCalendarEntryExhibition']) || isset($handlersList['OnCalendarEntryOffsite']) || isset($handlersList['OnTimeManWeekend']))
{
	$arFilterEvent = [
		'ACTIVE' => 'Y',
		'DELETED' => 'N',
		//'CAL_TYPE' => 'user',
		'!CUSTOM_TYPE_ID' => false,
		'CREATED_BY' => $keyUsers //?OWNER_ID
	];
	$arFilterEvent[] = [
		'LOGIC' => 'OR',
		['=CUSTOM_DATE_FROM' => date('j.n.Y')],
		['=CUSTOM_DATE_TO' => date('j.n.Y')],
		['<=DATE_FROM' => $date['FROM'], '>=DATE_TO' => $date['FROM']]
	];

	$events = EventTable::getList([
		'filter' => $arFilterEvent,
		'select' => ['PARENT_ID', 'DATE_FROM', 'DATE_TO', 'CUSTOM_DATE_FROM', 'CUSTOM_DATE_TO', 'CUSTOM_TYPE_' => 'CUSTOM_TYPE', 'CREATED_BY'],
		'runtime' => [
			'CUSTOM_TYPE' => [
				'data_type' => '\RtopTypeEventTable',
				'reference' => [
					'=this.PARENT_ID' => 'ref.EVENT_ID'
				]
			],
			new Entity\ExpressionField('CUSTOM_DATE_FROM', 'CONCAT(DAY(DATE_FROM), ".", MONTH(DATE_FROM), ".", YEAR(DATE_FROM))'),
			new Entity\ExpressionField('CUSTOM_DATE_TO', 'CONCAT(DAY(DATE_TO), ".", MONTH(DATE_TO), ".", YEAR(DATE_TO))'),
		]
	])->fetchAll();

	$result = [];

	foreach($events ?: [] as $event)
	{
		if(in_array($event['CREATED_BY'], $keyUsers))
			$result[$event['CUSTOM_TYPE_TYPE']][] = $event['CREATED_BY'];
	}

	if(empty($result))
		exit();

	if(isset($handlersList['OnTimeManWeekend']))
	{
		$reduce = array_reduce($result, "array_merge", []);
		foreach($reduce as $id)
		{
			if(in_array($userList[$id]['ROLE'], $handlersList['OnTimeManWeekend']['EVENT_ROLE']) 
				&& in_array($userList[$id]['DEPARTMENT'], $handlersList['OnTimeManWeekend']['EVENT_DEPARTMENT']))
			{
				RtopMain::addSimpleBonus(
					$id, 
					$userList[$id], 
					['FUNCTION' => 'OnTimeManWeekend', 'VALUE' => $handlersList['OnTimeManWeekend']['EVENT_VALUE']],
					[]
				);
			}
		}
	}

	if(isset($result['EXHIBITION']) && isset($handlersList['OnCalendarEntryExhibition']))
	{
		foreach($result['EXHIBITION'] as $id)
		{
			if(in_array($userList[$id]['ROLE'], $handlersList['OnCalendarEntryExhibition']['EVENT_ROLE']) 
				&& in_array($userList[$id]['DEPARTMENT'], $handlersList['OnCalendarEntryExhibition']['EVENT_DEPARTMENT']))
			{
				RtopMain::addSimpleBonus(
					$id, 
					$userList[$id], 
					['FUNCTION' => 'OnCalendarEntryExhibition', 'VALUE' => $handlersList['OnCalendarEntryExhibition']['EVENT_VALUE']],
					[]
				);
			}
		}

		unset($result['EXHIBITION']);
	}

	if(!empty($result) && isset($handlersList['OnCalendarEntryOffsite']))
	{
		foreach($result as $type)
		{
			foreach($type as $id)
			{
				if(in_array($userList[$id]['ROLE'], $handlersList['OnCalendarEntryOffsite']['EVENT_ROLE']) 
					&& in_array($userList[$id]['DEPARTMENT'], $handlersList['OnCalendarEntryOffsite']['EVENT_DEPARTMENT']))
				{
					RtopMain::addSimpleBonus(
						$id, 
						$userList[$id], 
						['FUNCTION' => 'OnCalendarEntryOffsite', 'VALUE' => $handlersList['OnCalendarEntryOffsite']['EVENT_VALUE']],
						[]
					);
				}
			}
		}
	}
}