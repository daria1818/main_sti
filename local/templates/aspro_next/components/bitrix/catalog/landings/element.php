<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?
use Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager;

Loader::includeModule("iblock");
Loader::includeModule("highloadblock");

global $arTheme, $arRegion;
$arElement = array();
$arSelect = array("ID", "IBLOCK_ID", "NAME", "PROPERTY_FILTER_URL", "PROPERTY_SECTION", "ElementValues", "PROPERTY_I_ELEMENT_PAGE_TITLE", "PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_ALT", "PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE", "PROPERTY_I_SKU_PAGE_TITLE", "PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_ALT", "PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_TITLE");
if($arResult["VARIABLES"]["ELEMENT_ID"] > 0){
	$arElementFilter = array("IBLOCK_ID" => CNextCache::$arIBlocks[SITE_ID]["aspro_next_catalog"]["aspro_next_landing"][0], "ID" => $arResult["VARIABLES"]["ELEMENT_ID"]);
}
elseif(strlen(trim($arResult["VARIABLES"]["ELEMENT_CODE"])) > 0){
	$arElementFilter = array("IBLOCK_ID" => CNextCache::$arIBlocks[SITE_ID]["aspro_next_catalog"]["aspro_next_landing"][0], "=CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"]);
}

if($arParams['SHOW_DEACTIVATED'] !== 'Y'){
	$arElementFilter['ACTIVE'] = 'Y';
}

if($GLOBALS[$arParams['FILTER_NAME']]){
	$arElementFilter = array_merge($arElementFilter, $GLOBALS[$arParams['FILTER_NAME']]);
}

$arElement = CNextCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" =>"N", "TAG" => CNextCache::GetIBlockCacheTag(CNextCache::$arIBlocks[SITE_ID]["aspro_next_catalog"]["aspro_next_landing"][0]))), $arElementFilter, false, false, $arSelect);

if($arElement){
	$arIBInheritTemplates = array(
		"ELEMENT_PAGE_TITLE" => $arElement["PROPERTY_I_ELEMENT_PAGE_TITLE_VALUE"],
		"ELEMENT_PREVIEW_PICTURE_FILE_ALT" => $arElement["PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_ALT_VALUE"],
		"ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => $arElement["PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE_VALUE"],
		"SKU_PAGE_TITLE" => $arElement["PROPERTY_I_SKU_PAGE_TITLE_VALUE"],
		"SKU_PREVIEW_PICTURE_FILE_ALT" => $arElement["PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_ALT_VALUE"],
		"SKU_PREVIEW_PICTURE_FILE_TITLE" => $arElement["PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_TITLE_VALUE"],
	);
}

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
}

if($arParams['LIST_PRICES'])
{
	foreach($arParams['LIST_PRICES'] as $key => $price)
	{
		if(!$price)
			unset($arParams['LIST_PRICES'][$key]);
	}
}

if($arParams['STORES'])
{
	foreach($arParams['STORES'] as $key => $store)
	{
		if(!$store)
			unset($arParams['STORES'][$key]);
	}
}

if(!$arParams["FILTER_NAME"]){
	$arParams["FILTER_NAME"] = 'arrProductsFilter';
}

$GLOBALS[$arParams["FILTER_NAME"]]['SECTION_ID'] = $arElement['PROPERTY_SECTION_VALUE'];
$GLOBALS[$arParams["FILTER_NAME"]]['INCLUDE_SUBSECTIONS'] = 'Y';

if($arRegion)
{
	if($arRegion["LIST_STORES"] && $arParams["HIDE_NOT_AVAILABLE"] == "Y")
	{
		if(CNext::checkVersionModule('18.6.200', 'iblock')){
			$arStoresFilter = array(
				'STORE_NUMBER' => $arParams['STORES'],
				'>STORE_AMOUNT' => 0,
			);
		}
		else{
			if(count($arParams['STORES']) > 1){
				$arStoresFilter = array('LOGIC' => 'OR');
				foreach($arParams['STORES'] as $storeID)
				{
					$arStoresFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
				}
			}
			else{
				foreach($arParams['STORES'] as $storeID)
				{
					$arStoresFilter = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
				}
			}
		}

		$arTmpFilter = array('!TYPE' => '2');
		if($arStoresFilter){
			if(!CNext::checkVersionModule('18.6.200', 'iblock') && count($arStoresFilter) > 1){
				$arTmpFilter[] = $arStoresFilter;
			}
			else{
				$arTmpFilter = array_merge($arTmpFilter, $arStoresFilter);
			}

			$GLOBALS[$arParams["FILTER_NAME"]][] = array(
				'LOGIC' => 'OR',
				array('TYPE' => '2'),
				$arTmpFilter,
			);
		}
	}
	$arParams["USE_REGION"] = "Y";

	CNext::makeElementFilterInRegion($GLOBALS[$arParams['FILTER_NAME']], false, true);
}
?>
<?if(!$arElement && $arParams['SET_STATUS_404'] !== 'Y'):?>
	<div class="alert alert-warning"><?=GetMessage("ELEMENT_NOTFOUND")?></div>
<?elseif(!$arElement && $arParams['SET_STATUS_404'] === 'Y'):?>
	<?CNext::goto404Page();?>
<?else:?>
	<?CNext::AddMeta(
		array(
			'og:description' => $arElement['PREVIEW_TEXT'],
			'og:image' => (($arElement['PREVIEW_PICTURE'] || $arElement['DETAIL_PICTURE']) ? CFile::GetPath(($arElement['PREVIEW_PICTURE'] ? $arElement['PREVIEW_PICTURE'] : $arElement['DETAIL_PICTURE'])) : false),
		)
	);?>

	<?
	/* hide compare link from module options */
	if(CNext::GetFrontParametrValue('CATALOG_COMPARE') == 'N')
		$arParams["USE_COMPARE"] = 'N';
	/**/

	if(CNext::GetFrontParametrValue('SHOW_DELAY_BUTTON') == 'N')
		$arParams["DISPLAY_WISH_BUTTONS"] = 'N';
	?>

	<?if($arParams["USE_SHARE"] == "Y" && $arElement):?>
		<?$this->SetViewTarget('product_share');?>
		<div class="catalog_detail share top <?=($arParams['USE_RSS'] == 'Y' ? 'rss-block' : '');?>">
			<?$APPLICATION->IncludeFile(SITE_DIR."include/share_buttons.php", Array(), Array("MODE" => "html", "NAME" => GetMessage('CT_BCE_CATALOG_SOC_BUTTON')));?>
		</div>
		<?$this->EndViewTarget();?>
	<?endif;?>
	<?$isWideBlock = (isset($arParams["DIR_PARAMS"]["HIDE_LEFT_BLOCK"]) ? $arParams["DIR_PARAMS"]["HIDE_LEFT_BLOCK"] : "");?>
	<div class="landing_detail <?=($isWideBlock == "Y" ? "fixed_wrapper" : "");?>" itemscope itemtype="http://schema.org/NewsArticle">
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="<?=($APPLICATION->IsHTTPS() ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$APPLICATION->GetCurPage()?>" />
		<?@include_once('page_blocks/'.$arParams["ELEMENT_TYPE_VIEW"].'.php');?>
	</div>
	<?CNext::checkBreadcrumbsChain($arParams, $arSection, $arElement);?>

	<?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.history.js');?>
<?endif;?>