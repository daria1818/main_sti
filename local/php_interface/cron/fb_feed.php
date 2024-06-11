<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 3) . '';
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Loader;

set_time_limit(0);
error_reporting(E_ERROR | E_STRICT);

define('R_HOST', 'https://stionline.ru');
define('R_PATH_FEED', $_SERVER["DOCUMENT_ROOT"] . '/fb_feed.xml');
define('G_NAMESPACE', 'http://base.google.com/ns/1.0');
define('R_IBLOCK_ID', 30);
define('P_IBLOCK_ID', 81);
define('SROKGODNOSTI1S', 28);

if (!Loader::includeModule('iblock') 
		|| !Loader::includeModule('sale') 
		|| !Loader::includeModule('catalog') 
		|| !Loader::includeModule('highloadblock'))
{
	die("Didn't included module iblock or sale or catalog.");	
}

$arProp = [];

$hlblock = HL\HighloadBlockTable::getById(SROKGODNOSTI1S)->fetch();
$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data_class = $entity->getDataClass(); 
$res = $entity_data_class::getList();
while($ob = $res->fetch())
{
	$arProp[$ob['UF_XML_ID']] = $ob['UF_NAME'];
}

function fd($array){
	echo "<pre>"; print_r($array); echo "</pre>";
}

$listOffers = CIBlockElement::GetList(
	array(),
	array('IBLOCK_ID' => P_IBLOCK_ID, 'ACTIVE' => 'Y'),
	false,
	false,
	array(
		'ID', 
		'NAME',
		'CATALOG_GROUP_11',
		'PROPERTY_CML2_LINK',
		'PROPERTY_CML2_ARTICLE',
		'PROPERTY_SROK_GODNOSTI_1S'
	)
);
$arOffers = array();
while ($res = $listOffers->GetNextElement())
{
	$arProduct = $res->GetFields();

	$arProduct['PROPERTY_SROK_GODNOSTI_1S_VALUE'] = $arProp[$arProduct['PROPERTY_SROK_GODNOSTI_1S_VALUE']];

	$arOffers[$arProduct['PROPERTY_CML2_LINK_VALUE']][$arProduct['ID']] = [
		'ID' => $arProduct['ID'],
		'NAME' => (!empty($arProduct['PROPERTY_SROK_GODNOSTI_1S_VALUE']) ? '. Срок годности - ' . $arProduct['PROPERTY_SROK_GODNOSTI_1S_VALUE'] : $arProduct['NAME']),
		'CUSTOM_NAME' => (!empty($arProduct['PROPERTY_SROK_GODNOSTI_1S_VALUE']) ? "Y" : "N"),
		'CATALOG_PRICE_11' => $arProduct['CATALOG_PRICE_11'],
		'CATALOG_CURRENCY_11' => $arProduct['CATALOG_CURRENCY_11'],
		'PROPERTY_CML2_ARTICLE_VALUE' => $arProduct['PROPERTY_CML2_ARTICLE_VALUE']
	];
}

$listElement = CIBlockElement::GetList(
	array(),
	array('IBLOCK_ID' => R_IBLOCK_ID, 'ACTIVE' => 'Y'),
	false,
	false,
	array(
		'ID', 
		'NAME',
		'PREVIEW_TEXT',
		'DETAIL_PAGE_URL',
		'IBLOCK_SECTION_ID',
		'CATALOG_GROUP_11',
		'PREVIEW_PICTURE',
		'DETAIL_PICTURE',
		'PROPERTY_CML2_ARTICLE',
	) 
);

$arResult = [];

while ($res = $listElement->GetNextElement())
{
	$arProduct = $res->GetFields();

	if(empty($arProduct['PREVIEW_PICTURE']) && empty($arProduct['DETAIL_PICTURE']))
		continue;

	$arProduct['MORE_PHOTO'] = "";

	$db_props = CIBlockElement::GetProperty(R_IBLOCK_ID, $arProduct['ID'], array("sort" => "asc"), Array("CODE" => "MORE_PHOTO", "EMPTY" => "N"));
	while($ar_props = $db_props->Fetch())
		$arProduct['MORE_PHOTO'] .= R_HOST . CFile::GetPath($ar_props['VALUE']) . ",";
	
	$skubool = CCatalogSKU::getExistOffers(array($arProduct['ID']), R_IBLOCK_ID);

	$arProduct['PREVIEW_PICTURE'] = R_HOST . CFile::GetPath((!empty($arProduct['PREVIEW_PICTURE']) ? $arProduct['PREVIEW_PICTURE'] : $arProduct['DETAIL_PICTURE']));

	$res_breadcrumb = CIBlockSection::GetNavChain(R_IBLOCK_ID, $arProduct['IBLOCK_SECTION_ID']);
	while ($ar_breadcrumb = $res_breadcrumb->GetNext()) {
    	$arProduct['PATH'][] = array('NAME' => $ar_breadcrumb['NAME'], 'IBLOCK_SECTION_ID' => $ar_breadcrumb['IBLOCK_SECTION_ID']);		
	}

	$path_breadcrumb = '';

	foreach ($arProduct['PATH'] as $key => $value) {
		if($key == 0){
			$arProduct['custom_label_0'] = $value['NAME'];
		}
		if($key == 1){
			$arProduct['custom_label_1'] = $value['NAME'];
		}
		if(empty($value['IBLOCK_SECTION_ID'])){
			$path_breadcrumb .= 'Главная &gt; ';
		}
		$path_breadcrumb .= $value['NAME'] . ' &gt; ';
	}

	$arProduct['PATH'] = substr($path_breadcrumb, 0, -6);

	if(!!$skubool[$arProduct['ID']]){	
		foreach($arOffers[$arProduct['ID']] ?: [] as $id => &$offer){
			if($offer["CUSTOM_NAME"] == "Y")
				$offer['NAME'] = $arProduct['NAME'] . $offer['NAME'];
			$price = feedGetDiscountProduct($id, $offer['CATALOG_PRICE_11']);
			if(!!$price)
				$offer['PRICE_SALE'] = $price;
			$offer['PREVIEW_TEXT'] = $arProduct['PREVIEW_TEXT'];
			$offer['PREVIEW_PICTURE'] = $arProduct['PREVIEW_PICTURE'];
			$offer['PATH'] = $arProduct['PATH'];
			$offer['DETAIL_PAGE_URL'] = $arProduct['DETAIL_PAGE_URL'];
			$offer['MORE_PHOTO'] = $arProduct['MORE_PHOTO'];
			if(empty($offer['PROPERTY_CML2_ARTICLE_VALUE']))
				$offer['PROPERTY_CML2_ARTICLE_VALUE'] = $arProduct['PROPERTY_CML2_ARTICLE_VALUE'];
			$offer['PAREND_ID'] = $arProduct['ID'];
			$offer['custom_label_0'] = $arProduct['custom_label_0'];
			$offer['custom_label_1'] = $arProduct['custom_label_1'];
			$arResult = array_merge($arResult, [$id => $offer]);
		}
	}else{
		$price = feedGetDiscountProduct($id, $arProduct['CATALOG_PRICE_11']);
		if(!!$price)
			$arProduct['PRICE_SALE'] = $price;
		
		$arResult = array_merge($arResult, [$arProduct['ID'] => $arProduct]);
	}

}


if(count($arResult) > 0){
	$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:g="' . G_NAMESPACE . '"/>');
	$channel = $xml->addChild('channel');
	$channel->addChild('title', 'fb_feed_stionline');
	$channel->addChild('link', R_HOST);
	$channel->addChild('description', 'Фид данных для FB.');
	foreach($arResult as $arProduct){
		$item = $channel->addChild('item');
		$item->addChild('g:id', $arProduct['ID'], G_NAMESPACE);
		$title = htmlspecialchars($arProduct['NAME']);
		$description = (!empty($arProduct['PREVIEW_TEXT']) ? htmlspecialchars($arProduct['PREVIEW_TEXT']) : $title);
		$item->addChild('g:title', $title, G_NAMESPACE);
		$item->addChild('g:description', $description, G_NAMESPACE);
		$url = R_HOST . $arProduct['DETAIL_PAGE_URL'];
		$item->addChild('g:link', $url, G_NAMESPACE);		
		$item->addChild('g:brand', $arProduct['PROPERTY_CML2_ARTICLE_VALUE'], G_NAMESPACE);
		if($arProduct['PREVIEW_PICTURE'] != R_HOST)
			$item->addChild('g:image_link', $arProduct['PREVIEW_PICTURE'], G_NAMESPACE);
		$item->addChild('g:price', (float)$arProduct['CATALOG_PRICE_11'] . ' ' . $arProduct["CATALOG_CURRENCY_11"], G_NAMESPACE);
		if(!empty($arProduct['PRICE_SALE']) && $arProduct['PRICE_SALE'] != 0) $item->addChild('g:sale_price',(float)$arProduct['PRICE_SALE'] . ' ' . $arProduct["CATALOG_CURRENCY_11"], G_NAMESPACE);
	
		$item->addChild('g:condition', 'new', G_NAMESPACE);
		$item->addChild('g:visibility', 'published', G_NAMESPACE);
		$item->addChild('g:availability', $arFields['CATALOG_AVAILABLE'] == 'N' ? 'out of stock' : 'in stock', G_NAMESPACE);

		if(!empty($arProduct['PAREND_ID']))
			$item->addChild('g:item_group_id', $arProduct['PAREND_ID'], G_NAMESPACE);
		if(!empty($arProduct['MORE_PHOTO']))
			$item->addChild('g:additional_image_link', substr($arProduct['MORE_PHOTO'], 0 , -1) , G_NAMESPACE);

		$item->addChild('g:custom_label_0', $arProduct['custom_label_0'], G_NAMESPACE);
		$item->addChild('g:custom_label_1', $arProduct['custom_label_1'], G_NAMESPACE);
		$item->addChild('g:google_product_category', $arProduct['PATH'], G_NAMESPACE);
	}
	$output = $xml->asXML();
	$file = fopen(R_PATH_FEED, 'wa');
	if ($file && $output)
	{
		if (fwrite($file, $output)) 
			echo '<h3>SUCCESSFUL</h3>';
		else 
			echo '<h3>ERROR</h3>';
		fclose($file);
	}
}


function feedGetDiscountProduct($id, $price){
	$res_discount = CCatalogDiscount::GetDiscountByProduct($id, array(), "N", array(), SITE_ID);
	$res_discount = $res_discount[0];
	if($res_discount['VALUE_TYPE'] == 'P'){
		$res_discount['VALUE'] = intval($res_discount['VALUE'])/100;
		return $price-$price*$res_discount['VALUE'];
	}
	if($res_discount['VALUE_TYPE'] == 'F'){
		return $price-$res_discount['VALUE'];
	}

	return false;
}
?>