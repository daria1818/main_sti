<?$bAjaxMode = (isset($_POST["AJAX_POST"]) && $_POST["AJAX_POST"] == "Y");
if($bAjaxMode)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $APPLICATION;
	if(\Bitrix\Main\Loader::includeModule("aspro.next"))
	{
		$arRegion = CNextRegionality::getCurrentRegion();
	}
}?>
<?if((isset($arParams["IBLOCK_ID"]) && $arParams["IBLOCK_ID"]) || $bAjaxMode):?>
	<?
	$arIncludeParams = ($bAjaxMode ? $_POST["AJAX_PARAMS"] : $arParamsTmp);
	$arGlobalFilter = ($bAjaxMode ? unserialize(urldecode($_POST["GLOBAL_FILTER"])) : array());
	$arComponentParams = unserialize(urldecode($arIncludeParams));
	$arComponentParams['TYPE_SKU'] = \Bitrix\Main\Config\Option::get('aspro.next', 'TYPE_SKU', 'TYPE_1', SITE_ID);
	?>
<?
// array(90587, 91273, 90984, 91366, 26208370)
$res_date = CCatalogDiscount::GetByID(188);
preg_match_all('/\==\s(\d+)/', $res_date["UNPACK"], $matches);

preg_match_all('/in_array\((\d+)/', $res_date["UNPACK"], $mat);
 
$arGlobalFilter = array( array(
       "LOGIC" => "OR", "ID" => $matches["1"], "SECTION_ID" => $mat["1"]));

$GLOBALS[$arComponentParams["FILTER_NAME"]] = $arGlobalFilter;
?>
	<?
	if($bAjaxMode && (is_array($arGlobalFilter) && $arGlobalFilter))
		$GLOBALS[$arComponentParams["FILTER_NAME"]] = $arGlobalFilter;

	if($bAjaxMode && $_POST["FILTER_HIT_PROP"])
		$arComponentParams["FILTER_HIT_PROP"] = $_POST["FILTER_HIT_PROP"];

	/* hide compare link from module options */
	if (CNext::GetFrontParametrValue('CATALOG_COMPARE') == 'N') {
		$arComponentParams["DISPLAY_COMPARE"] = 'N';
	}
	/**/

	if ($_POST["ajax_get"] && $_POST["ajax_get"] === 'Y') {
		$arComponentParams["AJAX_REQUEST"] = 'Y';
	}
$arComponentParams["PROPERTY_AFP_DISCOUNT_LIST"] = '188';
	?>

	<?$APPLICATION->IncludeComponent(
		"bitrix:catalog.section",
		"catalog_block_front",
		$arComponentParams,
		false, array("HIDE_ICONS"=>"Y")
	);?>

<?endif;?>
