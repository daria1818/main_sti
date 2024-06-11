<?//$APPLICATION->ShowHeadScripts();?>
<?$APPLICATION->ShowAjaxHead();?>
<script data-skip-moving="true">
	window['FAST_VIEW_OID'] = null;
</script>
<?if ($_SERVER['QUERY_STRING']) {
	$arQuery = explode('&', $_SERVER['QUERY_STRING']);
	$offerID = 0;
	if ($arQuery) {
		foreach ($arQuery as $key => $arQueryTmp) {
			$tmp = explode('=', $arQueryTmp);
			if ($tmp && count($tmp) > 1) {
				if ($arParams["SKU_DETAIL_ID"] && $tmp[0] == $arParams["SKU_DETAIL_ID"]) {
					$offerID = $tmp[1];
				}
			}
		}
	}
	if ($offerID) {
		global $OFFER_ID;
		$OFFER_ID = $offerID;
		?>
		<script data-skip-moving="true">
			window['FAST_VIEW_OID'] = <?=$OFFER_ID?>;
		</script>
		<?
	}
}?>
<div class="catalog_detail" itemscope itemtype="http://schema.org/Product">
	<?@include_once('page_blocks/'.$arTheme["USE_FAST_VIEW_PAGE_DETAIL"]["VALUE"].'.php');?>
</div>
<?if($arRegion)
{
	$arTagSeoMarks = array();
	foreach($arRegion as $key => $value)
	{
		if(strpos($key, 'PROPERTY_REGION_TAG') !== false && strpos($key, '_VALUE_ID') === false)
		{
			$tag_name = str_replace(array('PROPERTY_', '_VALUE'), '', $key);
			$arTagSeoMarks['#'.$tag_name.'#'] = $key;
		}
	}
	if($arTagSeoMarks)
		CNextRegionality::addSeoMarks($arTagSeoMarks);
}?>