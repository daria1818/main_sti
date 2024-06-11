<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?global $arRegion;
if($arRegion)
{
	if($arRegion['LIST_PRICES'])
	{
		if(reset($arRegion['LIST_PRICES']) != 'component')
			$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
	}
	if($arRegion['LIST_STORES'])
	{
		if(reset($arRegion['LIST_STORES']) != 'component')
			$arParams['STORES'] = $arRegion['LIST_STORES'];
	}

	$GLOBALS['arrProductsFilter']['IBLOCK_ID'] = \Bitrix\Main\Config\Option::get("aspro.next", "CATALOG_IBLOCK_ID", "17");
	CNext::makeElementFilterInRegion($GLOBALS['arrProductsFilter']);
}
?>
<?$GLOBALS['arrProductsFilter']['INCLUDE_SUBSECTIONS'] = 'Y';?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.top",
	"products_slider",
	Array(
		"ACTION_VARIABLE" => "action",
		"ADD_DETAIL_TO_GALLERY_IN_LIST" => $GLOBALS["arTheme"]["GALLERY_ITEM_SHOW"]["DEPENDENT_PARAMS"]["ADD_DETAIL_TO_GALLERY_IN_LIST"]["VALUE"],
		"ADD_PICT_PROP" => ($arParams["ADD_PICT_PROP"]?$arParams["ADD_PICT_PROP"]:'MORE_PHOTO'),
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"ADD_TO_BASKET_ACTION" => "ADD",
		"BASKET_URL" => SITE_DIR."basket/",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"CACHE_TIME" => "3600000",
		"CACHE_TYPE" => "A",
		"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
		"COMPARE_PATH" => "",
		"COMPATIBLE_MODE" => "Y",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CONVERT_CURRENCY" => "N",
		"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
		"DETAIL_URL" => "",
		"DISCOUNT_PERCENT_POSITION" => "bottom-right",
		"DISPLAY_COMPARE" => "N",
		"ELEMENT_COUNT" => "9",
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_FIELD2" => "sort",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_ORDER2" => "desc",
		"ENLARGE_PRODUCT" => "STRICT",
		"FILTER_NAME" => "arrProductsFilter",
		"GALLERY_ITEM_SHOW" => $GLOBALS["arTheme"]["GALLERY_ITEM_SHOW"]["VALUE"],
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
		"IBLOCK_ID" => "30",
		"IBLOCK_TYPE" => "aspro_next_catalog",
		"INIT_SLIDER" => "Y",
		"LINE_ELEMENT_COUNT" => "",
		"MAX_GALLERY_ITEMS" => $GLOBALS["arTheme"]["GALLERY_ITEM_SHOW"]["DEPENDENT_PARAMS"]["MAX_GALLERY_ITEMS"]["VALUE"],
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_COMPARE" => "Сравнить",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"OFFERS_CART_PROPERTIES" => array(),
		"OFFERS_FIELD_CODE" => array("ID","NAME",""),
		"OFFERS_LIMIT" => "10",
		"OFFERS_PROPERTY_CODE" => array("",""),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFER_ADD_PICT_PROP" => ($arParams["OFFER_ADD_PICT_PROP"]?$arParams["OFFER_ADD_PICT_PROP"]:'MORE_PHOTO'),
		"PARTIAL_PRODUCT_PROPERTIES" => "Y",
		"PRICE_CODE" => array("AUTO"),
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_PROPERTIES" => array(),
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false}]",
		"PRODUCT_SUBSCRIPTION" => "Y",
		"PROPERTY_CODE" => array("",""),
		"REVIEWS_VIEW" => $GLOBALS["arTheme"]["REVIEWS_VIEW"]["VALUE"]=='EXTENDED',
		"SALE_STIKER" => $arParams["SALE_STIKER"],
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"SECTION_URL" => "",
		"SEF_MODE" => "N",
		"SHOW_BUY_BUTTONS" => $arParams["SHOW_BUY_BUTTONS"],
		"SHOW_CLOSE_POPUP" => "N",
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
		"SHOW_MAX_QUANTITY" => "N",
		"SHOW_MEASURE" => "Y",
		"SHOW_MEASURE_WITH_RATIO" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"SHOW_RATING" => "Y",
		"SHOW_SLIDER" => "Y",
		"SLIDER_INTERVAL" => "3000",
		"SLIDER_PROGRESS" => "N",
		"STIKERS_PROP" => $arParams["STIKERS_PROP"],
		"STORES" => $arParams["STORES"],
		"TEMPLATE_THEME" => "blue",
		"TITLE" => $arParams["TITLE"],
		"USE_ENHANCED_ECOMMERCE" => "N",
		"USE_PRICE_COUNT" => "Y",
		"USE_PRODUCT_QUANTITY" => "Y",
		"USE_REGION" => ($arRegion?"Y":"N"),
		"VIEW_MODE" => "SECTION"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>