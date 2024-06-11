<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Loader;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\Main as RtopMain;

if(!Loader::includeModule("rtop.kpi"))
	return;

global $USER;

$user = BalanceTable::getList(['filter' => ['USERID' => $USER->GetId()], 'select' => ['BALANCE']])->fetch();
if(empty($user))
	return;


$arResult['BALANCE'] = $user['BALANCE'] . " " . RtopMain::NumberWordEndings($user['BALANCE']);

$this->includeComponentTemplate();