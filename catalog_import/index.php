<?
define('BX_SESSION_ID_CHANGE', false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(""); ?><?php // catalog-3d8ff14e-05f3-4441-aef7-36e6fbc73a3e
?>
<?$APPLICATION->IncludeComponent(
	"bit24:catalog.import.1c", 
	"", 
	array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"DETAIL_HEIGHT" => "300",
		"DETAIL_RESIZE" => "N",
		"DETAIL_WIDTH" => "300",
		"ELEMENT_ACTION" => "N",
		"FILE_SIZE_LIMIT" => "204800",
		"FORCE_OFFERS" => "N",
		"GENERATE_PREVIEW" => "N",
		"GROUP_PERMISSIONS" => array(
			0 => "1",
			1 => "10",
		),
		"IBLOCK_TYPE" => "aspro_next_catalog",
		"INTERVAL" => "30",
		"PREVIEW_HEIGHT" => "100",
		"PREVIEW_WIDTH" => "100",
		"SECTION_ACTION" => "N",
		"SITE_LIST" => array(
			0 => "s1",
		),
		"SKIP_ROOT_SECTION" => "Y",
		"TRANSLIT_ON_ADD" => "Y",
		"TRANSLIT_ON_UPDATE" => "N",
		"USE_CRC" => "Y",
		"USE_IBLOCK_PICTURE_SETTINGS" => "N",
		"USE_IBLOCK_TYPE_ID" => "Y",
		"USE_OFFERS" => "N",
		"USE_ZIP" => "Y",
		"TRANSLIT_MAX_LEN" => "100",
		"TRANSLIT_CHANGE_CASE" => "L",
		"TRANSLIT_REPLACE_SPACE" => "-",
		"TRANSLIT_REPLACE_OTHER" => "-",
		"TRANSLIT_DELETE_REPEAT_REPLACE" => "Y"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>