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
<article itemscope itemtype="http://schema.org/Review">
	<span style="display:none;" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">

		<meta itemprop="worstRating" content = "0">

		<meta itemprop="bestRating" content = "5">

		<span itemprop="ratingValue">4</span>

	</span>
<div class="catalog_detail" itemprop="itemReviewed" itemscope itemtype="http://schema.org/Product">
	<?@include_once('page_blocks/'.$arTheme["USE_FAST_VIEW_PAGE_DETAIL"]["VALUE"].'.php');?>
</div>
</article>
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