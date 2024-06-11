<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("dev");
?><?$APPLICATION->IncludeComponent(
	"bitrix:search.title", 
	"fixed", 
	array(
		"CATEGORY_0" => array(
			0 => "no",
		),
		"CATEGORY_0_TITLE" => "ALL",
		"CATEGORY_0_iblock_aspro_next_catalog" => array(
			0 => "all",
		),
		"CATEGORY_0_iblock_aspro_next_content" => array(
			0 => "all",
		),
		"CATEGORY_OTHERS_TITLE" => "OTHER",
		"CHECK_DATES" => "Y",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CONTAINER_ID" => "title-search",
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"INPUT_ID" => "title-search-input",
		"NUM_CATEGORIES" => "1",
		"ORDER" => "date",
		"PAGE" => "/search/",
		"PREVIEW_HEIGHT" => "25",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PREVIEW_WIDTH" => "25",
		"PRICE_CODE" => array(
			0 => "BASE",
			1 => "OPT",
		),
		"SHOW_INPUT" => "Y",
		"SHOW_INPUT_FIXED" => "N",
		"SHOW_OTHERS" => "Y",
		"SHOW_PREVIEW" => "Y",
		"TOP_COUNT" => "10",
		"USE_LANGUAGE_GUESS" => "Y",
		"COMPONENT_TEMPLATE" => "fixed"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>