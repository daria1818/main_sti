<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("Title", "Список производителей, товары которых представлены на сайте  Stionline");
$APPLICATION->SetPageProperty("description", "Интернет-магазин Stionline является официальным дилером представленных заграничных торговых марок. Доставка по Краснодару и РФ. Тел.: +8 800 555-46-07");
$APPLICATION->SetTitle("Партнеры");
?><?$APPLICATION->IncludeComponent(
	"bitrix:news", 
	"partners", 
	array(
		"ADD_ELEMENT_CHAIN" => "Y",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"ALT_TITLE_GET" => "SEO",
		"BROWSER_TITLE" => "BANNER_TITLE",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"CACHE_TIME" => "100000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"COMPONENT_TEMPLATE" => "partners",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CONVERT_CURRENCY" => "N",
		"COUNT_IN_LINE" => "3",
		"DEFAULT_LIST_TEMPLATE" => "block",
		"DEPTH_LEVEL_BRAND" => "2",
		"DETAIL_ACTIVE_DATE_FORMAT" => "",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_FIELD_CODE" => array(
			0 => "ID",
			1 => "NAME",
			2 => "DETAIL_TEXT",
			3 => "",
		),
		"DETAIL_PAGER_SHOW_ALL" => "Y",
		"DETAIL_PAGER_TEMPLATE" => "",
		"DETAIL_PAGER_TITLE" => "Страница",
		"DETAIL_PROPERTY_CODE" => array(
			0 => "",
			1 => "DOCUMENTS",
			2 => "PHOTOS",
			3 => "",
		),
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_COMPARE" => "N",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_WISH_BUTTONS" => "Y",
		"ELEMENT_TYPE_VIEW" => "FROM_MODULE",
		"FILE_404" => "",
		"GALLERY_PRODUCTS_PROPERTY" => "BNR_TOP_IMG",
		"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
		"HIDE_NOT_AVAILABLE" => "Y",
		"IBLOCK_CATALOG_ID" => "30",
		"IBLOCK_CATALOG_TYPE" => "aspro_next_catalog",
		"IBLOCK_ID" => "26",
		"IBLOCK_TYPE" => "aspro_next_content",
		"IMAGE_POSITION" => "left",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"LINKED_ELEMENST_PAGE_COUNT" => "20",
		"LINKED_PRODUCTS_PROPERTY" => "BRAND",
		"LIST_ACTIVE_DATE_FORMAT" => "",
		"LIST_FIELD_CODE" => array(
			0 => "PREVIEW_PICTURE",
			1 => "",
		),
		"LIST_OFFERS_FIELD_CODE" => array(
			0 => "ID",
			1 => "NAME",
			2 => "",
		),
		"LIST_OFFERS_LIMIT" => "10",
		"LIST_OFFERS_PROPERTY_CODE" => array(
			0 => "CML2_MANUFACTURER",
			1 => "AVTOTSENY",
			2 => "SIZES",
			3 => "COLOR_REF",
			4 => "ARTICLE",
			5 => "SIZES2",
			6 => "",
		),
		"LIST_PROPERTY_CATALOG_CODE" => array(
			0 => "",
			1 => "",
		),
		"LIST_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"LIST_VIEW" => "block",
		"MESSAGE_404" => "",
		"META_DESCRIPTION" => "BANNER_DESCRIPTION",
		"META_KEYWORDS" => "-",
		"NEWS_COUNT" => "20",
		"OFFERS_CART_PROPERTIES" => array(
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
		"OFFER_HIDE_NAME_PROPS" => "N",
		"OFFER_SHOW_PREVIEW_PICTURE_PROPS" => array(
		),
		"OFFER_TREE_PROPS" => array(
			0 => "CML2_MANUFACTURER",
		),
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "arrows_adm",
		"PAGER_TITLE" => "Новости",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PRICE_CODE" => array(
			0 => "AUTO",
			1 => "",
		),
		"PRODUCT_PROPERTIES" => array(
		),
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"SALE_STIKER" => "-",
		"SECTIONS_DETAIL_COUNT" => "10",
		"SECTION_ELEMENTS_TYPE_VIEW" => "FROM_MODULE",
		"SEF_FOLDER" => "/info/brands/",
		"SEF_MODE" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"SET_STATUS_404" => "Y",
		"SET_TITLE" => "Y",
		"SHOW_404" => "Y",
		"SHOW_ARTICLE_SKU" => "Y",
		"SHOW_DETAIL_LINK" => "Y",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
		"SHOW_DISCOUNT_TIME" => "Y",
		"SHOW_DISCOUNT_TIME_EACH_SKU" => "N",
		"SHOW_GALLERY" => "Y",
		"SHOW_LINKED_PRODUCTS" => "Y",
		"SHOW_MEASURE" => "N",
		"SHOW_MEASURE_WITH_RATIO" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"SHOW_RATING" => "N",
		"SHOW_SECTION_PREVIEW_DESCRIPTION" => "Y",
		"SHOW_UNABLE_SKU_PROPS" => "N",
		"SORT_BUTTONS" => array(
			0 => "NAME",
			1 => "PRICE",
		),
		"SORT_BY1" => "ID",
		"SORT_BY2" => "ID",
		"SORT_ORDER1" => "ASC",
		"SORT_ORDER2" => "ASC",
		"SORT_PRICES" => "AUTO",
		"SORT_REGION_PRICE" => "BASE",
		"STIKERS_PROP" => "-",
		"STORES" => array(
			0 => "1",
			1 => "",
		),
		"STRICT_SECTION_CHECK" => "N",
		"T_DOCS" => "",
		"T_GALLERY" => "Галерея",
		"T_GOODS" => "",
		"T_GOODS_SECTION" => "",
		"T_PROJECTS" => "",
		"T_REVIEWS" => "",
		"USE_CATEGORIES" => "N",
		"USE_FILTER" => "N",
		"USE_PERMISSIONS" => "N",
		"USE_PRICE_COUNT" => "N",
		"USE_RATING" => "N",
		"USE_REVIEW" => "N",
		"USE_RSS" => "N",
		"USE_SEARCH" => "Y",
		"VIEW_TYPE" => "table",
		"FILTER_NAME" => "",
		"FILTER_FIELD_CODE" => array(
			0 => "ID",
			1 => "NAME",
			2 => "",
		),
		"FILTER_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"S_ASK_QUESTION" => "",
		"S_ORDER_SERVISE" => "",
		"FORM_ID_ORDER_SERVISE" => "",
		"T_NEXT_LINK" => "",
		"T_PREV_LINK" => "",
		"SHOW_FILTER_DATE" => "Y",
		"LINE_ELEMENT_COUNT_LIST" => "3",
		"SHOW_NEXT_ELEMENT" => "N",
		"USE_SHARE" => "N",
		"SEF_URL_TEMPLATES" => array(
			"news" => "",
			"section" => "#SECTION_CODE_PATH#/",
			"detail" => "#ELEMENT_CODE#/",
			"search" => "search/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>