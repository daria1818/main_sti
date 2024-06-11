<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
global $arSectionFilter;

$arSectionFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
CNext::makeSectionFilterInRegion($arSectionFilter);

// region filter for to count elements
if(
	$GLOBALS['arRegion'] &&
	$GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' &&
	$GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y'
){
	// unrem this for hide empty sections without region`s products
	//$arSectionFilter['PROPERTY'] = array('LINK_REGION' => $GLOBALS['arRegion']['ID']);

	$arSectionFilter['PROPERTY_LINK_REGION'] = $GLOBALS['arRegion']['ID'];
}
?>
<?@include_once('page_blocks/'.$arParams["SECTIONS_TYPE_VIEW"].'.php');?>
