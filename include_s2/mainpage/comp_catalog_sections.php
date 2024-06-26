<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?global $arTheme, $isShowCatalogSections;?>
<?if($isShowCatalogSections):?>
	<?$APPLICATION->IncludeComponent(
	"aspro:catalog.section.list.next", 
	"front_sections_theme", 
	array(
		"ADD_SECTIONS_CHAIN" => "N",
		"ALL_URL" => "catalog/",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"COUNT_ELEMENTS" => "Y",
		"DISPLAY_PANEL" => "N",
		"FILTER_NAME" => "arrPopularSections",
		"HIDE_SECTION_NAME" => "N",
		"IBLOCK_ID" => "30",
		"IBLOCK_TYPE" => "aspro_next_catalog",
		"SECTIONS_LIST_PREVIEW_DESCRIPTION" => "N",
		"SECTIONS_LIST_PREVIEW_PROPERTY" => "N",
		"SECTION_CODE" => "",
		"SECTION_FIELDS" => array(
			0 => "PICTURE",
			1 => "",
		),
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_URL" => "",
		"SECTION_USER_FIELDS" => array(
			0 => "UF_POPULAR",
			1 => "UF_BACKGROUND_IMAGE",
			2 => "",
		),
		"SHOW_PARENT_NAME" => "N",
		"SHOW_SECTIONS_LIST_PREVIEW" => "N",
		"SHOW_SECTION_LIST_PICTURES" => "N",
		"COMPACT_VIEW_MOBILE" => $arTheme["MOBILE_CATALOG_LIST_SECTIONS_COMPACT"]["VALUE"],
		"TEMPLATE" => $arTheme["FRONT_PAGE_SECTIONS"]["VALUE"],
		"TITLE_BLOCK" => "Популярные разделы",
		"TITLE_BLOCK_ALL" => "Весь каталог",
		"TOP_DEPTH" => "8",
		"VIEW_MODE" => "",
		"COMPONENT_TEMPLATE" => "front_sections_theme"
	),
	false
);?>
<?endif;?>