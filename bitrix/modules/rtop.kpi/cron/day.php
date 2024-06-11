<?php
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
use Bitrix\Calendar\Internals\EventTable;
use Rtop\KPI\HandlersTable;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\Main as RtopMain;
use Rtop\KPI\Logger as Log;

if(!Loader::IncludeModule("rtop.kpi") || !Loader::IncludeModule("crm") || !Loader::includeModule("calendar"))
	return;

$handlersJs = HandlersTable::getList([
	'filter' => ['PERIOD' => 'day', 'AUTO' => 'Y'],
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

/*Исходящая почта , Входящая почта*/
if(isset($handlersList['OnBeforeMailSendHandler']) && isset($handlersList['onMailMessageModifiedHandler']))
{
	$timelineList = TimelineTable::getList([
		'filter' => [
			'ASSOCIATED_ENTITY_CLASS_NAME' => 'CRM_EMAIL', 
			'AUTHOR_ID' => $keyUsers,
			'>CREATED' => $date['FROM'],
			'<CREATED' => $date['TO'],
			'BINDINGS_ENTITY_TYPE_ID' => 14
		],
		'select' => ['ID', 'AUTHOR_ID', 'ASSOCIATED_ENTITY_ID', 'BINDINGS_' => 'BINDINGS', 'CONTACT_COMPANY_' => 'CONTACT_COMPANY', 'COMPANY_' => 'COMPANY', 'CONTACT_' => 'CONTACT', 'ACCESS_' => 'ACCESS', 'MESSAGE_' => 'MESSAGE'],
		'runtime' => [
	        'CONTACT_COMPANY' => [
	            'data_type' => '\Bitrix\Crm\Binding\OrderContactCompanyTable',
	            'reference' => [
	                '=this.BINDINGS_ENTITY_ID' => 'ref.ORDER_ID',
	            ],
	        ],
	        new Entity\ReferenceField(
	            'COMPANY', 
	            \Bitrix\Crm\CompanyTable::class,
	            Query\Join::on('this.CONTACT_COMPANY_ENTITY_ID', 'ref.ID')->where('this.CONTACT_COMPANY_ENTITY_TYPE_ID', \CCrmOwnerType::Company)
	        ),
	        new Entity\ReferenceField(
	            'CONTACT', 
	            \Bitrix\Crm\ContactTable::class,
	            Query\Join::on('this.CONTACT_COMPANY_ENTITY_ID', 'ref.ID')->where('this.CONTACT_COMPANY_ENTITY_TYPE_ID', \CCrmOwnerType::Contact)
	        ),
	        'ACCESS' => [
	        	'data_type' => '\Bitrix\Mail\Internals\MessageAccessTable',
	            'reference' => [
	                '=this.ASSOCIATED_ENTITY_ID' => 'ref.ENTITY_ID',
	            ],
	        ],
	        'MESSAGE' => [
	        	'data_type' => '\Bitrix\Mail\MailMessageTable',
	            'reference' => [
	                '=this.ACCESS_MESSAGE_ID' => 'ref.ID',
	            ],
	        ]
		]
	])->fetchAll();


	$result = [];
	foreach($timelineList ?:[] as $field)
	{
		if(isset($result[$field['AUTHOR_ID']][$type][$field['ID']]))
			continue;

		$type = ($field['MESSAGE_FIELD_TO'] == "info@stionline.ru" ? 'incoming' : 'outgoing');
		if(!empty($field['COMPANY_ID'])){
			$client = ['ID' => $field['COMPANY_ID'], 'TYPE' => 'Company'];
		}else{
			$client = ['ID' => $field['CONTACT_ID'], 'TYPE' => 'Contact'];
		}
		
		$result[$field['AUTHOR_ID']][$type][$field['ID']] = $client;
	}

	foreach($result ?: [] as $userid => $field)
	{
		if(isset($field['outgoing']) 
			&& in_array($userList[$userid]['DEPARTMENT'], $handlersList['OnBeforeMailSendHandler']['EVENT_DEPARTMENT']) 
			&& in_array($userList[$userid]['ROLE'], $handlersList['OnBeforeMailSendHandler']['EVENT_ROLE']))
		{
			foreach($field['outgoing'] as $value)
			{
				RtopMain::addSimpleBonus(
					$userid, 
					$userList[$userid], 
					['FUNCTION' => 'OnBeforeMailSendHandler', 'VALUE' => $handlersList['OnBeforeMailSendHandler']['EVENT_VALUE'], 'NAME' => $handlersList['OnBeforeMailSendHandler']['NAME']],
					$value
				);
			}
			
		}

		if(isset($field['incoming']) 
			&& in_array($userList[$userid]['DEPARTMENT'], $handlersList['onMailMessageModifiedHandler']['EVENT_DEPARTMENT']) 
			&& in_array($userList[$userid]['ROLE'], $handlersList['onMailMessageModifiedHandler']['EVENT_ROLE']))
		{
			foreach($field['incoming'] as $value)
			{
				RtopMain::addSimpleBonus(
					$userid, 
					$userList[$userid], 
					['FUNCTION' => 'onMailMessageModifiedHandler', 'VALUE' => $handlersList['onMailMessageModifiedHandler']['EVENT_VALUE'], 'NAME' => $handlersList['onMailMessageModifiedHandler']['NAME']],
					$value
				);
			}
			
		}
	}
}

if(isset($handlersList['OnImConnectorMessageAddHandler']))
{
	if(!Loader::IncludeModule("im") || !Loader::IncludeModule("imopenlines"))
		return;

	$sessions = \Bitrix\ImOpenLines\Model\SessionTable::getList([
		'filter' => [
			'OPERATOR_ID' => $keyUsers,
			'>KPI_MESSAGES_TIME_ANSWER' => $date['FROM'],
			'<KPI_MESSAGES_TIME_ANSWER' => $date['TO'],
		],
		'select' => ['ID', 'OPERATOR_ID', 'DATE_CLOSE', 'CRM_ACTIVITY_ID', 'KPI_MESSAGES_' => 'KPI_MESSAGES']
	])->fetchAll();

	$result = [];

	foreach($sessions as $session)
	{
		if($session['DATE_CLOSE'] == $session['KPI_MESSAGES_TIME_ANSWER'])
			continue;

		$client = [];

		$crmEntitiesManager = \Bitrix\ImOpenLines\Crm\Common::getActivityBindings($session['CRM_ACTIVITY_ID']);
		if($crmEntitiesManager->isSuccess())
		{
			$data = $crmEntitiesManager->getData();
			
			if(!empty($data['COMPANY']))
				$client = ['ID' => $data['COMPANY'], 'TYPE' => 'Company'];
			if(!empty($data['CONTACT']))
				$client = ['ID' => $data['CONTACT'], 'TYPE' => 'Contact'];
		}		

		$result[$session['OPERATOR_ID']][$session['KPI_MESSAGES_ID']] = $client;
	}

	foreach($result ?: [] as $userid => $field)
	{
		if(in_array($userList[$userid]['DEPARTMENT'], $handlersList['OnImConnectorMessageAddHandler']['EVENT_DEPARTMENT']) 
			&& in_array($userList[$userid]['ROLE'], $handlersList['OnImConnectorMessageAddHandler']['EVENT_ROLE']))
		{
			foreach($field as $value)
			{
				RtopMain::addSimpleBonus(
					$userid, 
					$userList[$userid], 
					['FUNCTION' => 'OnImConnectorMessageAddHandler', 'VALUE' => $handlersList['OnImConnectorMessageAddHandler']['EVENT_VALUE'], 'NAME' => $handlersList['OnImConnectorMessageAddHandler']['NAME']],
					$value
				);
			}
		}
	}
}

/*Входящий звонок , Исходящий звонок*/
if(isset($handlersList['IncomingСall']) && isset($handlersList['OutgoingСall']))
{
	if(!Loader::IncludeModule("voximplant"))
		return;

	$calls = \Bitrix\Voximplant\StatisticTable::getList([
		'filter' => [
			'PORTAL_USER_ID' => $keyUsers,
			'>CALL_START_DATE' => $date['FROM'],
			'<CALL_START_DATE' => $date['TO'],
			'=CALL_FAILED_REASON' => 'Success call'
		],
		'select' => ['PORTAL_USER_ID', 'INCOMING', 'CRM_ENTITY_TYPE', 'CRM_ENTITY_ID']
	])->fetchAll();

	foreach($calls ?: [] as $call)
	{
		$userid = $call['PORTAL_USER_ID'];
		$client = [];
		if($call['CRM_ENTITY_TYPE'] == 'COMPANY' || $call['CRM_ENTITY_TYPE'] == 'CONTACT')
			$client = ['ID' => $call['CRM_ENTITY_ID'], 'TYPE' => ucfirst(strtolower($call['CRM_ENTITY_TYPE']))];

		if(in_array($userList[$userid]['DEPARTMENT'], $handlersList['IncomingСall']['EVENT_DEPARTMENT']) 
			&& in_array($userList[$userid]['ROLE'], $handlersList['IncomingСall']['EVENT_ROLE']) 
			&& in_array($call['INCOMING'], [2,3]))
		{
			RtopMain::addSimpleBonus(
				$userid, 
				$userList[$userid], 
				['FUNCTION' => 'IncomingСall', 'VALUE' => $handlersList['IncomingСall']['EVENT_VALUE'], 'NAME' => $handlersList['IncomingСall']['NAME']],
				$client
			);
		}

		if(in_array($userList[$userid]['DEPARTMENT'], $handlersList['OutgoingСall']['EVENT_DEPARTMENT']) 
			&& in_array($userList[$userid]['ROLE'], $handlersList['OutgoingСall']['EVENT_ROLE']) 
			&& in_array($call['INCOMING'], [1,4]))
		{
			RtopMain::addSimpleBonus(
				$userid, 
				$userList[$userid], 
				['FUNCTION' => 'OutgoingСall', 'VALUE' => $handlersList['OutgoingСall']['EVENT_VALUE'], 'NAME' => $handlersList['OutgoingСall']['NAME']],
				$client
			);
		}
	}
}

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
		$holidays = explode(",", COption::GetOptionString('calendar', 'year_holidays', '1.01,2.01,7.01,23.02,8.03,1.05,9.05,12.06,4.11'));
		$dm = date('d.m');
		$jm = date('j.m');
		$continue = false;
		foreach($holidays ?: [] as $day)
		{
			if($day == $dm || $day == $jm)
			{
				$continue = true;
				break;
			}
		}

		if($continue)
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