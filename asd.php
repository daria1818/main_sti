<?php 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.location.selector.steps",
	"",
	Array(
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CODE" => "",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"DISABLE_KEYBOARD_INPUT" => "N",
		"FILTER_BY_SITE" => "N",
		"ID" => "",
		"INITIALIZE_BY_GLOBAL_EVENT" => "",
		"INPUT_NAME" => "LOCATION",
		"JS_CALLBACK" => "",
		"JS_CONTROL_GLOBAL_ID" => "",
		"PRECACHE_LAST_LEVEL" => "N",
		"PRESELECT_TREE_TRUNK" => "N",
		"PROVIDE_LINK_BY" => "id",
		"SHOW_DEFAULT_LOCATIONS" => "N",
		"SUPPRESS_ERRORS" => "N"
	)
);?><br>
 <?$APPLICATION->IncludeComponent(
	"bitrix:sale.location.selector.search",
	"",
	Array(
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CODE" => "",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"FILTER_BY_SITE" => "N",
		"ID" => "",
		"INITIALIZE_BY_GLOBAL_EVENT" => "",
		"INPUT_NAME" => "LOCATION",
		"JS_CALLBACK" => "",
		"JS_CONTROL_GLOBAL_ID" => "",
		"PROVIDE_LINK_BY" => "id",
		"SHOW_DEFAULT_LOCATIONS" => "N",
		"SUPPRESS_ERRORS" => "N"
	)
);?><br>