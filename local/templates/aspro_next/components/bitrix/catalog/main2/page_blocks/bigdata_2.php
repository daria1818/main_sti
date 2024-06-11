<?$APPLICATION->IncludeComponent("bitrix:catalog.bigdata.products", "right_block", array(
	"USE_REGION" => ($arRegion ? "Y" : "N"),
	"STORES" => $arParams['STORES'],
	"LINE_ELEMENT_COUNT" => 5,
	"TEMPLATE_THEME" => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
	"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
	"BASKET_URL" => $arParams["BASKET_URL"],
	"ACTION_VARIABLE" => (!empty($arParams["ACTION_VARIABLE"]) ? $arParams["ACTION_VARIABLE"] : "action")."_cbdp",
	"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
	"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
	"SHOW_MEASURE_WITH_RATIO" => $arParams["SHOW_MEASURE_WITH_RATIO"],
	"ADD_PROPERTIES_TO_BASKET" => "N",
	"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
	"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
	"SHOW_OLD_PRICE" => "N",
	"SHOW_DISCOUNT_PERCENT" => "N",
	"PRICE_CODE" => $arParams['PRICE_CODE'],
	"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
	"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
	"PRODUCT_SUBSCRIPTION" => $arParams['PRODUCT_SUBSCRIPTION'],
	"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
	"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
	"TITLE_SLIDER" => $arParams['TITLE_SLIDER'],
	"SHOW_NAME" => "Y",
	"SHOW_IMAGE" => "Y",
	"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"MESS_BTN_BUY" => $arParams['MESS_BTN_BUY'],
	"MESS_BTN_DETAIL" => $arParams['MESS_BTN_DETAIL'],
	"MESS_BTN_SUBSCRIBE" => $arParams['MESS_BTN_SUBSCRIBE'],
	"MESS_NOT_AVAILABLE" => $arParams['MESS_NOT_AVAILABLE'],
	"PAGE_ELEMENT_COUNT" => ($arParams['RECOMEND_COUNT'] ? $arParams['RECOMEND_COUNT'] : 5),
	"SHOW_FROM_SECTION" => "N",
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SALE_STIKER" => $arParams["SALE_STIKER"],
	"STIKERS_PROP" => $arParams["STIKERS_PROP"],
	"DEPTH" => "2",
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
	"SHOW_PRODUCTS_".$arParams["IBLOCK_ID"] => "Y",
	"ADDITIONAL_PICT_PROP_".$arParams["IBLOCK_ID"] => $arParams['ADD_PICT_PROP'],
	"LABEL_PROP_".$arParams["IBLOCK_ID"] => "-",
	"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
	'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
	"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
	"CURRENCY_ID" => $arParams["CURRENCY_ID"],
	"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
	"SECTION_ELEMENT_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"SECTION_ELEMENT_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
	"ID" => $arResult["ID"],
	"PROPERTY_CODE_".$arParams["IBLOCK_ID"] => $arParams["LIST_PROPERTY_CODE"],
	"CART_PROPERTIES_".$arParams["IBLOCK_ID"] => $arParams["PRODUCT_PROPERTIES"],
	"RCM_TYPE" => (isset($arParams['BIG_DATA_RCM_TYPE']) ? $arParams['BIG_DATA_RCM_TYPE'] : ''),
	"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
	"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
	"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],
	),
	false,
	array("HIDE_ICONS" => "Y", "ACTIVE_COMPONENT" => "Y")
);
?>