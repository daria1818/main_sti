<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
global $isShowSale, $isShowCatalogSections, $isShowCatalogElements, $isShowMiddleAdvBottomBanner, $isShowBlog, $isShowReviews;
?>
<div class="grey_block">
	<div class="maxwidth-theme">
		<?$APPLICATION->IncludeComponent(
			"aspro:com.banners.next", 
			"top_big_banners", 
			array(
				"IBLOCK_TYPE" => "aspro_next_adv",
				"IBLOCK_ID" => "3",
				"TYPE_BANNERS_IBLOCK_ID" => "1",
				"SET_BANNER_TYPE_FROM_THEME" => "N",
				"NEWS_COUNT" => "10",
				"NEWS_COUNT2" => "4",
				"SORT_BY1" => "SORT",
				"SORT_ORDER1" => "ASC",
				"SORT_BY2" => "ID",
				"SORT_ORDER2" => "DESC",
				"PROPERTY_CODE" => array(
					0 => "TEXT_POSITION",
					1 => "TARGETS",
					2 => "TEXTCOLOR",
					3 => "URL_STRING",
					4 => "BUTTON1TEXT",
					5 => "BUTTON1LINK",
					6 => "BUTTON2TEXT",
					7 => "BUTTON2LINK",
					8 => "",
				),
				"CHECK_DATES" => "Y",
				"CACHE_GROUPS" => "N",
				"CACHE_TYPE" => "A",
				"CACHE_TIME" => "36000000",
				"BANNER_TYPE_THEME" => "TOP",
				"BANNER_TYPE_THEME_CHILD" => "TOP_SMALL_BANNER",
			),
			false
		);?>
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_tizers.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>
	</div>
	<hr>
</div>
<?

// if (CModule::IncludeModule("iblock")) {
//     $saleIds = [];
//     $propertyRes = CIBlockElement::GetProperty(101, 26214327, "sort", "asc", ["CODE" => "SALE_ID"]);
//     while ($propertyValue = $propertyRes->Fetch()) {
//         $saleIds[] = $propertyValue['VALUE'];
//     }
// }

if (CModule::IncludeModule("iblock")) {
    $saleIds = [];
    $arSaleTypes = [];
    
    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_SALE_ID", "PROPERTY_SALE_TYPE");
    $arFilter = Array("IBLOCK_ID"=>101, "ID"=>26214327, "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
    
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
    }
     if (!empty($arProps['SALE_ID']['VALUE'])) {
        foreach ($arProps['SALE_ID']['VALUE'] as $value) {
            $saleIds[] = $value;
        }
    }
    if (!empty($arProps['SALE_TYPE']['VALUE_ENUM'])) {
        // $arSaleTypes = $arProps['SALE_TYPE']['VALUE_ENUM_ID']; // ID значения
        // Или для получения текстового значения
        // $arSaleTypes = $arProps['SALE_TYPE']['VALUE_ENUM']; // Текстовое значение
	    if ($arProps['SALE_TYPE']['VALUE_ENUM'] == "offers") {
	    	$saleTypeID = 81;
	    	$updPicture = true;
	    }else{
	    	$saleTypeID = 30;
	    	$updPicture = false;
	    }
    }
}



//get Sale Product IDs
if(CModule::IncludeModule('catalog')){
	$productIDs = [];
	foreach ($saleIds as $saleID) {
	    // Получаем список товаров, на которые распространяется скидка
	    $dbDiscountProducts = CCatalogDiscount::GetDiscountProductsList([], ['DISCOUNT_ID' => $saleID], false, false, ['PRODUCT_ID']);
	    while ($discountProduct = $dbDiscountProducts->Fetch()) {
	        $productIDs["ID"][] = $discountProduct['PRODUCT_ID'];
	    }
	}

}
global $arrFilterProducts;
$arrFilterProducts = $productIDs;



if(count($arrFilterProducts) > 0){
	?>
<div class="grey_block">
	<div class="maxwidth-theme">
		<?
		$APPLICATION->IncludeComponent(
		"bitrix:catalog.section",
		"catalog_block2",
		array(
			"updPict" => $updPicture,
			"FILTER_IDS" => "arrFilterProducts",
			"TITLE" => "Акции месяца",
			"IBLOCK_TYPE" => "aspro_next_catalog",
			"IBLOCK_ID" => $saleTypeID,
			"HIDE_NOT_AVAILABLE" => "L",
			"BASKET_URL" => "/basket/",
			"ACTION_VARIABLE" => "action",
			"PRODUCT_ID_VARIABLE" => "id",
			"SECTION_ID_VARIABLE" => "SECTION_ID",
			"PRODUCT_QUANTITY_VARIABLE" => "quantity",
			"PRODUCT_PROPS_VARIABLE" => "prop",
			"SEF_MODE" => "N",
			"SEF_FOLDER" => "/catalog/",
			"AJAX_MODE" => "N",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "Y",
			"AJAX_OPTION_HISTORY" => "Y",
			"CACHE_TYPE" => "A",
			"CACHE_TIME" => "3600",
			"CACHE_FILTER" => "Y",
			"CACHE_GROUPS" => "N",
			"SET_TITLE" => "Y",
			"SET_STATUS_404" => "N",
			"USE_ELEMENT_COUNTER" => "Y",
			"USE_FILTER" => "Y",
			"FILTER_NAME" => "arrFilterProducts",
			"FILTER_FIELD_CODE" => array(
				0 => "",
				1 => "TSVET",
				2 => "",
			),
			"FILTER_PROPERTY_CODE" => array(
				0 => "CML2_ARTICLE",
				1 => "TSVET",
				2 => "BRAND",
				3 => "IN_STOCK",
				4 => "BREND",
				5 => "",
			),
			"FILTER_PRICE_CODE" => array(
				0 => "AUTO",
			),
			"FILTER_OFFERS_FIELD_CODE" => array(
				0 => "NAME",
				1 => "",
			),
			"FILTER_OFFERS_PROPERTY_CODE" => array(
				0 => "BREND",
				1 => "EXP",
				2 => "LEVEL",
				3 => "COLOR",
				4 => "CML2_LINK",
				5 => "TSVET",
				6 => "",
			),
			"USE_REVIEW" => "N",
			"MESSAGES_PER_PAGE" => "10",
			"USE_CAPTCHA" => "Y",
			"REVIEW_AJAX_POST" => "Y",
			"PATH_TO_SMILE" => "/bitrix/images/forum/smile/",
			"FORUM_ID" => "1",
			"URL_TEMPLATES_READ" => "",
			"SHOW_LINK_TO_FORUM" => "Y",
			"POST_FIRST_MESSAGE" => "N",
			"USE_COMPARE" => "N",
			"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
			"COMPARE_FIELD_CODE" => array(
				0 => "NAME",
				1 => "TAGS",
				2 => "SORT",
				3 => "PREVIEW_PICTURE",
				4 => "",
			),
			"COMPARE_PROPERTY_CODE" => array(
				0 => "CML2_ARTICLE",
				1 => "CML2_BASE_UNIT",
				2 => "BRAND",
				3 => "PROP_2033",
				4 => "COLOR_REF2",
				5 => "PROP_2052",
				6 => "CML2_MANUFACTURER",
				7 => "PROP_159",
				8 => "PROP_2049",
				9 => "PROP_162",
				10 => "PROP_2017",
				11 => "PROP_2027",
				12 => "PROP_2053",
				13 => "PROP_2083",
				14 => "PROP_2026",
				15 => "PROP_2044",
				16 => "PROP_2065",
				17 => "PROP_2054",
				18 => "PROP_2055",
				19 => "PROP_2069",
				20 => "PROP_2062",
				21 => "PROP_2061",
				22 => "",
			),
			"COMPARE_OFFERS_FIELD_CODE" => array(
				0 => "NAME",
				1 => "PREVIEW_PICTURE",
				2 => "",
			),
			"COMPARE_OFFERS_PROPERTY_CODE" => array(
				0 => "SIZES",
				1 => "COLOR_REF",
				2 => "ARTICLE",
				3 => "VOLUME",
				4 => "",
			),
			"COMPARE_ELEMENT_SORT_FIELD" => "sort",
			"COMPARE_ELEMENT_SORT_ORDER" => "asc",
			"DISPLAY_ELEMENT_SELECT_BOX" => "N",
			"PRICE_CODE" => array(
				0 => "AUTO",
			),
			"USE_PRICE_COUNT" => "N",
			"SHOW_PRICE_COUNT" => "1",
			"PRICE_VAT_INCLUDE" => "Y",
			"PRICE_VAT_SHOW_VALUE" => "N",
			"PRODUCT_PROPERTIES" => array(
			),
			"USE_PRODUCT_QUANTITY" => "Y",
			"CONVERT_CURRENCY" => "Y",
			"CURRENCY_ID" => "RUB",
			"OFFERS_CART_PROPERTIES" => array(
			),
			"SHOW_TOP_ELEMENTS" => "Y",
			"SECTION_COUNT_ELEMENTS" => "Y",
			"SECTION_TOP_DEPTH" => "2",
			"SECTIONS_LIST_PREVIEW_PROPERTY" => "DESCRIPTION",
			"SHOW_SECTION_LIST_PICTURES" => "Y",
			"PAGE_ELEMENT_COUNT" => "20",
			"LINE_ELEMENT_COUNT" => "4",
			"ELEMENT_SORT_FIELD" => "sort",
			"ELEMENT_SORT_ORDER" => "asc",
			"ELEMENT_SORT_FIELD2" => "sort",
			"ELEMENT_SORT_ORDER2" => "asc",
			"LIST_PROPERTY_CODE" => array(
				0 => "",
				1 => "PROP_2052",
				2 => "",
			),
			"INCLUDE_SUBSECTIONS" => "Y",
			"LIST_META_KEYWORDS" => "-",
			"LIST_META_DESCRIPTION" => "-",
			"LIST_BROWSER_TITLE" => "-",
			"LIST_OFFERS_FIELD_CODE" => array(
				0 => "NAME",
				1 => "CML2_LINK",
				2 => "DETAIL_PAGE_URL",
				3 => "",
			),
			"LIST_OFFERS_PROPERTY_CODE" => array(
				0 => "SROK_GODNOSTI_1S",
				1 => "SEX",
				2 => "SIZECHOICE",
				3 => "EXP",
				4 => "ARTICLE",
				5 => "VOLUME",
				6 => "",
			),
			"LIST_OFFERS_LIMIT" => "0",
			"SORT_BUTTONS" => array(
				0 => "POPULARITY",
				1 => "NAME",
				2 => "PRICE",
			),
			"SORT_PRICES" => "AUTO",
			"DEFAULT_LIST_TEMPLATE" => "block",
			"SECTION_DISPLAY_PROPERTY" => "UF_ELEMENT_DETAIL",
			"LIST_DISPLAY_POPUP_IMAGE" => "Y",
			"SECTION_PREVIEW_PROPERTY" => "DESCRIPTION",
			"SHOW_SECTION_PICTURES" => "N",
			"SHOW_SECTION_SIBLINGS" => "Y",
			"USE_DETAIL_PREDICTION" => "N",
			"DETAIL_PROPERTY_CODE" => array(
				0 => "CML2_ARTICLE",
				1 => "VIDEO",
				2 => "VIDEO_YOUTUBE",
				3 => "ASSOCIATED",
				4 => "BRAND",
				5 => "PROP_2033",
				6 => "COLOR_REF2",
				7 => "CML2_ATTRIBUTES",
				8 => "RECOMMEND",
				9 => "NEW",
				10 => "STOCK",
				11 => "INSTRUCTIONS",
				12 => "FILES",
				13 => "",
			),
			"DETAIL_META_KEYWORDS" => "-",
			"DETAIL_META_DESCRIPTION" => "-",
			"DETAIL_BROWSER_TITLE" => "-",
			"DETAIL_OFFERS_FIELD_CODE" => array(
				0 => "NAME",
				1 => "PREVIEW_PICTURE",
				2 => "DETAIL_TEXT",
				3 => "DETAIL_PICTURE",
				4 => "DETAIL_PAGE_URL",
				5 => "",
			),
			"DETAIL_OFFERS_PROPERTY_CODE" => array(
				0 => "SROK_GODNOSTI_1S",
				1 => "EXP",
				2 => "COLOR_REF",
				3 => "EXPIRE",
				4 => "",
			),
			"PROPERTIES_DISPLAY_LOCATION" => "DESCRIPTION",
			"SHOW_BRAND_PICTURE" => "Y",
			"SHOW_ASK_BLOCK" => "Y",
			"ASK_FORM_ID" => "2",
			"SHOW_ADDITIONAL_TAB" => "N",
			"PROPERTIES_DISPLAY_TYPE" => "TABLE",
			"SHOW_KIT_PARTS" => "Y",
			"SHOW_KIT_PARTS_PRICES" => "Y",
			"LINK_IBLOCK_TYPE" => "aspro_next_content",
			"LINK_IBLOCK_ID" => "",
			"LINK_PROPERTY_SID" => "",
			"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
			"USE_ALSO_BUY" => "N",
			"ALSO_BUY_ELEMENT_COUNT" => "5",
			"ALSO_BUY_MIN_BUYES" => "2",
			"USE_STORE" => "N",
			"USE_STORE_PHONE" => "Y",
			"USE_STORE_SCHEDULE" => "Y",
			"USE_MIN_AMOUNT" => "Y",
			"MIN_AMOUNT" => "10",
			"STORE_PATH" => "/contacts/stores/#store_id#/",
			"MAIN_TITLE" => "Наличие на складах",
			"MAX_AMOUNT" => "20",
			"USE_ONLY_MAX_AMOUNT" => "Y",
			"OFFERS_SORT_FIELD" => "sort",
			"OFFERS_SORT_ORDER" => "asc",
			"OFFERS_SORT_FIELD2" => "sort",
			"OFFERS_SORT_ORDER2" => "asc",
			"PAGER_TEMPLATE" => "main2",
			"DISPLAY_TOP_PAGER" => "N",
			"DISPLAY_BOTTOM_PAGER" => "Y",
			"PAGER_TITLE" => "Товары",
			"PAGER_SHOW_ALWAYS" => "N",
			"PAGER_DESC_NUMBERING" => "N",
			"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
			"PAGER_SHOW_ALL" => "N",
			"IBLOCK_STOCK_ID" => "19",
			"SHOW_QUANTITY" => "Y",
			"SHOW_MEASURE" => "Y",
			"SHOW_QUANTITY_COUNT" => "Y",
			"USE_RATING" => "N",
			"DISPLAY_WISH_BUTTONS" => "Y",
			"DEFAULT_COUNT" => "1",
			"SHOW_HINTS" => "Y",
			"AJAX_OPTION_ADDITIONAL" => "",
			"ADD_SECTIONS_CHAIN" => "Y",
			"ADD_ELEMENT_CHAIN" => "Y",
			"ADD_PROPERTIES_TO_BASKET" => "Y",
			"PARTIAL_PRODUCT_PROPERTIES" => "Y",
			"DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
			"STORES" => array(
				0 => "",
				1 => "",
			),
			"USER_FIELDS" => array(
				0 => "",
				1 => "",
			),
			"FIELDS" => array(
				0 => "",
				1 => "",
			),
			"SHOW_EMPTY_STORE" => "Y",
			"SHOW_GENERAL_STORE_INFORMATION" => "N",
			"TOP_ELEMENT_COUNT" => "8",
			"TOP_LINE_ELEMENT_COUNT" => "4",
			"TOP_ELEMENT_SORT_FIELD" => "shows",
			"TOP_ELEMENT_SORT_ORDER" => "desc",
			"TOP_ELEMENT_SORT_FIELD2" => "SCALED_PRICE_1",
			"TOP_ELEMENT_SORT_ORDER2" => "asc",
			"TOP_PROPERTY_CODE" => array(
				0 => "",
				1 => "",
			),
			"COMPONENT_TEMPLATE" => "main",
			"DETAIL_SET_CANONICAL_URL" => "N",
			"SHOW_DEACTIVATED" => "N",
			"TOP_OFFERS_FIELD_CODE" => array(
				0 => "ID",
				1 => "",
			),
			"TOP_OFFERS_PROPERTY_CODE" => array(
				0 => "",
				1 => "",
			),
			"TOP_OFFERS_LIMIT" => "10",
			"SECTION_TOP_BLOCK_TITLE" => "Лучшие предложения",
			"OFFER_TREE_PROPS" => array(
				0 => "SROK_GODNOSTI_1S",
			),
			"USE_BIG_DATA" => "N",
			"BIG_DATA_RCM_TYPE" => "similar",
			"SHOW_DISCOUNT_PERCENT" => "Y",
			"SHOW_OLD_PRICE" => "Y",
			"VIEWED_ELEMENT_COUNT" => "20",
			"VIEWED_BLOCK_TITLE" => "Ранее вы смотрели",
			"ELEMENT_SORT_FIELD_BOX" => "name",
			"ELEMENT_SORT_ORDER_BOX" => "asc",
			"ELEMENT_SORT_FIELD_BOX2" => "id",
			"ELEMENT_SORT_ORDER_BOX2" => "desc",
			"ADD_PICT_PROP" => "-",
			"OFFER_ADD_PICT_PROP" => "-",
			"DETAIL_ADD_DETAIL_TO_SLIDER" => "Y",
			"SKU_DETAIL_ID" => "oid",
			"USE_MAIN_ELEMENT_SECTION" => "Y",
			"SET_LAST_MODIFIED" => "N",
			"PAGER_BASE_LINK_ENABLE" => "N",
			"SHOW_404" => "Y",
			"MESSAGE_404" => "",
			"AJAX_FILTER_CATALOG" => "Y",
			"SECTION_BACKGROUND_IMAGE" => "-",
			"DETAIL_BACKGROUND_IMAGE" => "-",
			"DISPLAY_ELEMENT_SLIDER" => "10",
			"SHOW_ONE_CLICK_BUY" => "N",
			"USE_GIFTS_DETAIL" => "Y",
			"USE_GIFTS_SECTION" => "Y",
			"USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
			"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "8",
			"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
			"GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
			"GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
			"GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT" => "8",
			"GIFTS_SECTION_LIST_HIDE_BLOCK_TITLE" => "N",
			"GIFTS_SECTION_LIST_BLOCK_TITLE" => "Подарки к товарам этого раздела",
			"GIFTS_SECTION_LIST_TEXT_LABEL_GIFT" => "Подарок",
			"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
			"GIFTS_SHOW_OLD_PRICE" => "Y",
			"GIFTS_SHOW_NAME" => "Y",
			"GIFTS_SHOW_IMAGE" => "Y",
			"GIFTS_MESS_BTN_BUY" => "Выбрать",
			"GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "8",
			"GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
			"GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
			"OFFER_HIDE_NAME_PROPS" => "N",
			"DISABLE_INIT_JS_IN_COMPONENT" => "N",
			"DETAIL_SET_VIEWED_IN_COMPONENT" => "N",
			"SECTION_PREVIEW_DESCRIPTION" => "Y",
			"SECTIONS_LIST_PREVIEW_DESCRIPTION" => "N",
			"SALE_STIKER" => "SALE_TEXT",
			"SHOW_DISCOUNT_TIME" => "N",
			"SHOW_RATING" => "N",
			"COMPOSITE_FRAME_MODE" => "A",
			"COMPOSITE_FRAME_TYPE" => "AUTO",
			"DETAIL_OFFERS_LIMIT" => "0",
			"DETAIL_EXPANDABLES_TITLE" => "Рекомендуемые дополнения",
			"DETAIL_ASSOCIATED_TITLE" => "Товары из этой линейки",
			"DETAIL_PICTURE_MODE" => "MAGNIFIER",
			"SHOW_UNABLE_SKU_PROPS" => "Y",
			"HIDE_NOT_AVAILABLE_OFFERS" => "Y",
			"DETAIL_STRICT_SECTION_CHECK" => "Y",
			"COMPATIBLE_MODE" => "Y",
			"TEMPLATE_THEME" => "blue",
			"LABEL_PROP" => "",
			"PRODUCT_DISPLAY_MODE" => "Y",
			"COMMON_SHOW_CLOSE_POPUP" => "N",
			"PRODUCT_SUBSCRIPTION" => "Y",
			"SHOW_MAX_QUANTITY" => "N",
			"MESS_BTN_BUY" => "Купить",
			"MESS_BTN_ADD_TO_BASKET" => "В корзину",
			"MESS_BTN_COMPARE" => "Сравнение",
			"MESS_BTN_DETAIL" => "Подробнее",
			"MESS_NOT_AVAILABLE" => "Нет в наличии",
			"MESS_BTN_SUBSCRIBE" => "Уведомить о поступлении",
			"SIDEBAR_SECTION_SHOW" => "Y",
			"SIDEBAR_DETAIL_SHOW" => "N",
			"SIDEBAR_PATH" => "",
			"USE_SALE_BESTSELLERS" => "Y",
			"FILTER_VIEW_MODE" => "VERTICAL",
			"FILTER_HIDE_ON_MOBILE" => "N",
			"INSTANT_RELOAD" => "N",
			"COMPARE_POSITION_FIXED" => "Y",
			"COMPARE_POSITION" => "top left",
			"USE_RATIO_IN_RANGES" => "Y",
			"USE_COMMON_SETTINGS_BASKET_POPUP" => "N",
			"COMMON_ADD_TO_BASKET_ACTION" => "ADD",
			"TOP_ADD_TO_BASKET_ACTION" => "ADD",
			"SECTION_ADD_TO_BASKET_ACTION" => "ADD",
			"DETAIL_ADD_TO_BASKET_ACTION" => array(
				0 => "BUY",
			),
			"DETAIL_ADD_TO_BASKET_ACTION_PRIMARY" => array(
				0 => "BUY",
			),
			"TOP_PROPERTY_CODE_MOBILE" => "",
			"TOP_VIEW_MODE" => "SECTION",
			"TOP_PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons,compare",
			"TOP_PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
			"TOP_ENLARGE_PRODUCT" => "STRICT",
			"TOP_SHOW_SLIDER" => "Y",
			"TOP_SLIDER_INTERVAL" => "3000",
			"TOP_SLIDER_PROGRESS" => "N",
			"SECTIONS_VIEW_MODE" => "LIST",
			"SECTIONS_SHOW_PARENT_NAME" => "Y",
			"LIST_PROPERTY_CODE_MOBILE" => "",
			"LIST_PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons,compare",
			"LIST_PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
			"LIST_ENLARGE_PRODUCT" => "STRICT",
			"LIST_SHOW_SLIDER" => "Y",
			"LIST_SLIDER_INTERVAL" => "3000",
			"LIST_SLIDER_PROGRESS" => "N",
			"DETAIL_MAIN_BLOCK_PROPERTY_CODE" => "",
			"DETAIL_MAIN_BLOCK_OFFERS_PROPERTY_CODE" => "",
			"DETAIL_USE_VOTE_RATING" => "N",
			"DETAIL_USE_COMMENTS" => "N",
			"DETAIL_BRAND_USE" => "N",
			"DETAIL_DISPLAY_NAME" => "Y",
			"DETAIL_IMAGE_RESOLUTION" => "16by9",
			"DETAIL_PRODUCT_INFO_BLOCK_ORDER" => "sku,props",
			"DETAIL_PRODUCT_PAY_BLOCK_ORDER" => "rating,price,priceRanges,quantityLimit,quantity,buttons",
			"DETAIL_SHOW_SLIDER" => "N",
			"DETAIL_DETAIL_PICTURE_MODE" => array(
				0 => "POPUP",
				1 => "MAGNIFIER",
			),
			"DETAIL_DISPLAY_PREVIEW_TEXT_MODE" => "E",
			"MESS_PRICE_RANGES_TITLE" => "Цены",
			"MESS_DESCRIPTION_TAB" => "Описание",
			"MESS_PROPERTIES_TAB" => "Характеристики",
			"MESS_COMMENTS_TAB" => "Комментарии",
			"LAZY_LOAD" => "N",
			"LOAD_ON_SCROLL" => "N",
			"USE_ENHANCED_ECOMMERCE" => "N",
			"DETAIL_DOCS_PROP" => "-",
			"STIKERS_PROP" => "HIT",
			"USE_SHARE" => "N",
			"TAB_OFFERS_NAME" => "",
			"TAB_DESCR_NAME" => "",
			"TAB_CHAR_NAME" => "",
			"TAB_VIDEO_NAME" => "",
			"TAB_REVIEW_NAME" => "",
			"TAB_FAQ_NAME" => "",
			"TAB_STOCK_NAME" => "",
			"TAB_DOPS_NAME" => "",
			"BLOCK_SERVICES_NAME" => "",
			"BLOCK_DOCS_NAME" => "Дополнительная информация",
			"CHEAPER_FORM_NAME" => "",
			"DIR_PARAMS" => CNext::GetDirMenuParametrs(__DIR__),
			"SHOW_CHEAPER_FORM" => "N",
			"SHOW_LANDINGS" => "N",
			"LANDING_TITLE" => "Привет буфет",
			"LANDING_SECTION_COUNT" => "4",
			"SHOW_LANDINGS_SEARCH" => "Y",
			"LANDING_SEARCH_TITLE" => "Похожие запросы",
			"LANDING_SEARCH_COUNT" => "7",
			"SECTIONS_TYPE_VIEW" => "sections_1",
			"SECTION_ELEMENTS_TYPE_VIEW" => "list_elements_1",
			"ELEMENT_TYPE_VIEW" => "element_1",
			"SHOW_ARTICLE_SKU" => "Y",
			"SORT_REGION_PRICE" => "BASE",
			"LANDING_TYPE_VIEW" => "landing_1",
			"BIGDATA_NORMAL" => "bigdata_1",
			"BIGDATA_EXT" => "bigdata_2",
			"SHOW_MEASURE_WITH_RATIO" => "N",
			"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
			"ALT_TITLE_GET" => "NORMAL",
			"SHOW_COUNTER_LIST" => "N",
			"SHOW_DISCOUNT_TIME_EACH_SKU" => "N",
			"USER_CONSENT" => "N",
			"USER_CONSENT_ID" => "0",
			"USER_CONSENT_IS_CHECKED" => "Y",
			"USER_CONSENT_IS_LOADED" => "N",
			"SHOW_HOW_BUY" => "N",
			"TITLE_HOW_BUY" => "Как купить",
			"SHOW_DELIVERY" => "N",
			"TITLE_DELIVERY" => "Доставка",
			"SHOW_PAYMENT" => "N",
			"TITLE_PAYMENT" => "Оплата",
			"SHOW_GARANTY" => "N",
			"TITLE_GARANTY" => "Условия гарантии",
			"USE_FILTER_PRICE" => "Y",
			"DISPLAY_ELEMENT_COUNT" => "Y",
			"RESTART" => "Y",
			"USE_LANGUAGE_GUESS" => "N",
			"NO_WORD_LOGIC" => "Y",
			"SHOW_SECTION_DESC" => "Y",
			"LANDING_POSITION" => "AFTER_PRODUCTS",
			"TITLE_SLIDER" => "Рекомендуем",
			"VIEW_BLOCK_TYPE" => "N",
			"SHOW_SEND_GIFT" => "N",
			"SEND_GIFT_FORM_NAME" => "",
			"USE_ADDITIONAL_GALLERY" => "N",
			"BLOCK_LANDINGS_NAME" => "",
			"BLOG_IBLOCK_ID" => "",
			"BLOCK_BLOG_NAME" => "",
			"RECOMEND_COUNT" => "5",
			"VISIBLE_PROP_COUNT" => "4",
			"BUNDLE_ITEMS_COUNT" => "3",
			"STORES_FILTER" => "TITLE",
			"STORES_FILTER_ORDER" => "SORT_ASC",
			"OFFER_SHOW_PREVIEW_PICTURE_PROPS" => array(
			),
			"FILE_404" => "404.php",
			"FILL_COMPACT_FILTER" => "Y",
			"SECTIONS_SEARCH_COUNT" => "10",
			"DETAIL_BLOCKS_ORDER" => "tizers,complect,nabor,tabs,stores,char,galery,exp_goods,services,gifts,goods,podborki,blog,recomend_goods,assoc_goods",
			"DETAIL_BLOCKS_TAB_ORDER" => "offers,desc,char,buy,payment,delivery,video,reviews,ask,stores,custom_tab",
			"DETAIL_BLOCKS_ALL_ORDER" => "tizers,complect,nabor,offers,desc,char,galery,video,reviews,gifts,ask,stores,services,docs,custom_tab,goods,recomend_goods,exp_goods,podborki,blog,assoc_goods",
			"BLOG_URL" => "catalog_comments",
			"MAX_IMAGE_SIZE" => "0.5",
			"DETAIL_BLOG_EMAIL_NOTIFY" => "Y",
			"SHOW_SKU_DESCRIPTION" => "N",
			"SEF_URL_TEMPLATES" => array(
				"sections" => "",
				"section" => "#SECTION_CODE_PATH#/",
				"element" => "#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
				"compare" => "compare.php?action=#ACTION_CODE#",
				"smart_filter" => "#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/",
			),
			"VARIABLE_ALIASES" => array(
				"compare" => array(
					"ACTION_CODE" => "action",
				),
			)
		),
	);?>
	</div>
</div>
<?}?>
<?if($isShowSale && false):?>
	<div class="grey_block">
		<div class="maxwidth-theme">
			<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"COMPONENT_TEMPLATE" => ".default",
		"PATH" => SITE_DIR."include/mainpage/comp_news_akc.php",
		"AREA_FILE_SHOW" => "file",
		"AREA_FILE_SUFFIX" => "",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "standard.php"
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "Y"
	)
);?>
		</div>
	</div>
<?endif;?>
<?if($isShowCatalogSections || $isShowCatalogElements || $isShowMiddleAdvBottomBanner):?>
	<div class="maxwidth-theme">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_catalog_hit.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_adv_middle.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>	
	</div>
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_catalog_sections.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>
<?endif;?>


<!-- inner us -->
<?if($isShowBlog):?>
	<div class="maxwidth-theme">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_blog.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>	
	</div>
<?endif;?>

<?if($isShowReviews):?>
	<div class="maxwidth-theme">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"PATH" => SITE_DIR."include/mainpage/comp_reviews.php",
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => "standard.php"
			),
			false
		);?>	
	</div>
<?endif;?>

<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"COMPONENT_TEMPLATE" => ".default",
		"PATH" => SITE_DIR."include/mainpage/comp_bottom_banners.php",
		"AREA_FILE_SHOW" => "file",
		"AREA_FILE_SUFFIX" => "",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "standard.php"
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "N"
	)
);?>

<div class="maxwidth-theme">
	<?global $arRegion, $isShowCompany;?>
	<?if($isShowCompany):?>
		<div class="company_bottom_block">			
			<div class="row wrap_md">
				<div class="col-md-3 col-sm-3 hidden-xs img">
					<?$APPLICATION->IncludeFile(SITE_DIR."include/mainpage/company/front_img.php", Array(), Array( "MODE" => "html", "NAME" => GetMessage("FRONT_IMG") )); ?>
				</div>
				<div class="col-md-9 col-sm-9 big">
					<?if($arRegion):?>
						<?$frame = new \Bitrix\Main\Page\FrameHelper('text-regionality-block');?>
						<?$frame->begin();?>
							<?=$arRegion['DETAIL_TEXT'];?>
						<?$frame->end();?>
					<?else:?>
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "front", array(
	"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/mainpage/company/front_info.php",
		"EDIT_TEMPLATE" => ""
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "N"
	)
);?>
					<?endif;?>
				</div>
			</div>			
		</div>
	<?endif;?>	
	<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
		array(
			"COMPONENT_TEMPLATE" => ".default",
			"PATH" => SITE_DIR."include/mainpage/comp_brands.php",
			"AREA_FILE_SHOW" => "file",
			"AREA_FILE_SUFFIX" => "",
			"AREA_FILE_RECURSIVE" => "Y",
			"EDIT_TEMPLATE" => "standard.php"
		),
		false
	);?>
</div>

<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"COMPONENT_TEMPLATE" => ".default",
		"PATH" => SITE_DIR."include/mainpage/comp_instagramm.php",
		"AREA_FILE_SHOW" => "file",
		"AREA_FILE_SUFFIX" => "",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "standard.php"
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "N"
	)
);?>

<?global $isShowMap;?>
<?if($isShowMap):?>
	<div class="maxwidth-theme js-load-block front_map_wrapper">
		<?$APPLICATION->IncludeComponent(
	"bitrix:main.include", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"PATH" => SITE_DIR."include/mainpage/company/about.php",
		"AREA_FILE_SHOW" => "file",
		"AREA_FILE_SUFFIX" => "",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "include_area.php",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>	
	</div>
<?endif;?>