<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	"map",
	Array(
		"API_KEY" => "",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CONTROLS" => array("ZOOM"),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.82339393397561;s:10:\"yandex_lon\";d:37.49492335502398;s:12:\"yandex_scale\";i:13;s:10:\"PLACEMARKS\";a:2:{i:0;a:3:{s:3:\"LON\";d:37.483629194199;s:3:\"LAT\";d:55.809134469145;s:4:\"TEXT\";s:89:\"Главный офис stionline.ru###RN###ул. Щукинская 2, 10 подъезд\";}i:1;a:3:{s:3:\"LON\";d:37.51260361705496;s:3:\"LAT\";d:55.833030129984195;s:4:\"TEXT\";s:78:\"Склад stionline.ru###RN###Проезд Черепановых д. 6 к. 1\";}}}",
		"MAP_HEIGHT" => "500",
		"MAP_ID" => "",
		"MAP_WIDTH" => "100%",
		"OPTIONS" => array("ENABLE_DBLCLICK_ZOOM","ENABLE_DRAGGING"),
		"USE_REGION_DATA" => "Y"
	)
);?>