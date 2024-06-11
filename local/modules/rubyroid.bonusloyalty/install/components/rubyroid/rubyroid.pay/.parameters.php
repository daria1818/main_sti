<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("RB_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"SET_MESSAGE" => Array(
		  	"NAME" => GetMessage("RB_SET_MESSAGE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);
?>