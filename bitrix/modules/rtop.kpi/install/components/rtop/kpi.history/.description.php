<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("KPIHISTORY_COMP_NAME"),
	"DESCRIPTION" => GetMessage("KPIHISTORY_COMP_DESCR"),
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "kpi",
		"NAME" => GetMessage("KPI_GROUP"),
		"SORT" => 1
	),
);
?>