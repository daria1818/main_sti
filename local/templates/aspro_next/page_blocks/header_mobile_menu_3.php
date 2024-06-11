<?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"top_mobile_v2",
	Array(
		"COMPONENT_TEMPLATE" => "top_mobile_v2",
		"MENU_CACHE_TIME" => "3600000",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_USE_GROUPS" => "N",
		"MENU_CACHE_GET_VARS" => array(
		),
		"DELAY" => "N",
		"MAX_LEVEL" => \Bitrix\Main\Config\Option::get("aspro.next", "MAX_DEPTH_MENU", 2),
		"ALLOW_MULTI_SELECT" => "Y",
		"ROOT_MENU_TYPE" => "top_content_multilevel",
		"CHILD_MENU_TYPE" => "left",
		"CACHE_SELECTED_ITEMS" => "N",
		"ALLOW_MULTI_SELECT" => "Y",
		"USE_EXT" => "Y"
	)
);?>
<div class="clio-sidenav__info">
	<?$APPLICATION->IncludeFile(
		SITE_DIR."include/top_page/site-address_v2_mobile.php", 
		array(), 
		array(
			"MODE" => "html",
			"NAME" => "Address",
			"TEMPLATE" => "include_area.php",
		)
	);?>
</div>