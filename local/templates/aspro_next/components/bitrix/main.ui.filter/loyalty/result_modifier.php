<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['DEFAULT_PRESETS'] as &$item){
	$item['FIELDS'] = $arResult['FIELDS'];
}

foreach ($arResult['PRESETS'] as &$item){
	if(empty($item['FIELDS']))
		$item['FIELDS'] = $arResult['FIELDS'];
}