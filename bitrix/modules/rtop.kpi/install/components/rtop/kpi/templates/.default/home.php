<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Loader::includeModule('rtop.kpi');

$APPLICATION->IncludeComponent(
	"rtop:kpi.history",
	"",
	Array()
);