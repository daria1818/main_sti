<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("RB_COMP_NAME"),
	"DESCRIPTION" => GetMessage("RB_COMP_DESCR"),
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "rubyroid",
		"CHILD" => array(
			"ID" => "bberry",
			"NAME" => GetMessage("RB_GROUP"),
			"SORT" => 1,
			"CHILD" => array(
				"ID" => "rubyroid.bonusloyalty",
			),
		),
	),
);
?>