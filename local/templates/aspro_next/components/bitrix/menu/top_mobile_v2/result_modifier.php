<?
$arResult = CNext::getChilds($arResult);
global $arRegion, $arTheme;

if(isset($arTheme['HEADER_MOBILE_MENU_CATALOG_EXPANDED']['VALUE']) && $arTheme['HEADER_MOBILE_MENU_CATALOG_EXPANDED']['VALUE'] === 'Y') {
    $arParams["CATALOG_MENU_EXPANDED"] = "Y";
}

if($arResult){
	if($bUseMegaMenu = $arTheme['USE_MEGA_MENU']['VALUE'] === 'Y'){
		CNext::replaceMenuChilds($arResult, $arParams);
	}

	foreach($arResult as $key=>$arItem)
	{
		if(isset($arItem['CHILD']))
		{
			foreach($arItem['CHILD'] as $key2=>$arItemChild)
			{
				if(isset($arItemChild['PARAMS']) && $arRegion && $arTheme['USE_REGIONALITY']['VALUE'] === 'Y' && $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y')
				{
					// filter items by region
					if(isset($arItemChild['PARAMS']['LINK_REGION']))
					{
						if($arItemChild['PARAMS']['LINK_REGION'])
						{
							if(!in_array($arRegion['ID'], $arItemChild['PARAMS']['LINK_REGION']))
								unset($arResult[$key]['CHILD'][$key2]);
						}
						else
							unset($arResult[$key]['CHILD'][$key2]);
					}
				}
			}
		}
	}
}
?>
<?
if(!function_exists('show_top_mobile_li')){
    function show_top_mobile_li($arItem, $arParams, $bParent, $style = array()){
    ?>
		
    	<?if(!$bParent):?>
    		<a href="<?=$arItem["LINK"]?>" class="clio-sidenav__dropdown-link"><?=$arItem['TEXT']?></a>
    	<?else:?>
    		<div class="clio-sidenav__dropdown-btn">
    			<div class="clio-dropdown__wrap">
    				<div class="clio-question__text"><?=$arItem['TEXT']?>
    					<span class="clio-chevron-right">
    						<svg width="20" height="11" viewBox="0 0 20 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L10 10L19 1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    					</span>
    				</div>
    			</div>
    			<div class="clio-dropdown-container">
    				<?foreach($arItem["CHILD"] as $arSubItem):?>
    					<a href="<?=$arSubItem['LINK']?>" class="clio-dropdown-container__link"><?=$arSubItem['TEXT']?></a>
    				<?endforeach?>
    			</div>
    		</div>
    	<?endif?>
    <?
	}
}
?>