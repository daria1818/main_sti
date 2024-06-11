<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$componentParameters = $arResult;


if ($_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
	    'bitrix:ui.sidepanel.wrapper',
	    '',
	    [
	        'POPUP_COMPONENT_NAME' => 'rtop:kpi.event.view',
	        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
	        'POPUP_COMPONENT_PARAMS' => $componentParameters,
	        'USE_UI_TOOLBAR' => 'Y',
			'USE_PADDING' => false,
			'PLAIN_VIEW' => false,
			'PAGE_MODE' => false,
			'PAGE_MODE_OFF_BACK_URL' => "/shop/settings/kpi/",
	    ]
	);
}else{
	$APPLICATION->IncludeComponent(
		"rtop:kpi.event.view",
		"",
		$componentParameters
	);
}