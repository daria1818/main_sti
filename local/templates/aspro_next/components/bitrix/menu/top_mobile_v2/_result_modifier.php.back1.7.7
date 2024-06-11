<?
$arResult = CNext::getChilds($arResult);
global $arRegion, $arTheme;

if(isset($arTheme['HEADER_MOBILE_MENU_CATALOG_EXPANDED']['VALUE']) && $arTheme['HEADER_MOBILE_MENU_CATALOG_EXPANDED']['VALUE'] === 'Y') {
    $arParams["CATALOG_MENU_EXPANDED"] = "Y";
}

if($arResult){
	if($bUseMegaMenu = $arTheme['USE_MEGA_MENU']['VALUE'] === 'Y'){
		$arMegaLinks = $arMegaItems = array();

		$menuIblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_megamenu'][0];
		if($menuIblockId){
			$arMenuSections = CNextCache::CIblockSection_GetList(
				array(
					'SORT' => 'ASC',
					'ID' => 'ASC',
					'CACHE' => array(
						'TAG' => CNextCache::GetIBlockCacheTag($menuIblockId),
						'GROUP' => array('DEPTH_LEVEL'),
						'MULTI' => 'Y',
					)
				),
				array(
					'ACTIVE' => 'Y',
					'GLOBAL_ACTIVE' => 'Y',
					'IBLOCK_ID' => $menuIblockId,
					'<=DEPTH_LEVEL' => $arParams['MAX_LEVEL'],
				),
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID',
					'DEPTH_LEVEL',
					'PICTURE',
					'UF_MEGA_MENU_LINK',
				)
			);

			if($arMenuSections){
				$cur_page = $GLOBALS['APPLICATION']->GetCurPage(true);
				$cur_page_no_index = $GLOBALS['APPLICATION']->GetCurPage(false);
				$some_selected = false;
				$bMultiSelect = $arParams['ALLOW_MULTI_SELECT'] === 'Y';

				foreach($arMenuSections as $depth => $arLinks){
					foreach($arLinks as $arLink){
						$url = trim($arLink['UF_MEGA_MENU_LINK']);
						if(
							(
								$depth == 1 &&
								strlen($url)
							) ||
							$depth > 1
						){
							$arMegaItem = array(
								'TEXT' => htmlspecialcharsbx($arLink['NAME']),
								'LINK' => strlen($url) ? $url : 'javascript:;',
								'SELECTED' => false,
								'PARAMS' => array(
									'PICTURE' => $arLink['PICTURE'],
								),
								'CHILD' => array(),
							);

							$arMegaItems[$arLink['ID']] =& $arMegaItem;

							if($depth > 1){
								if(
									strlen($url) &&
									($bMultiSelect || !$some_selected)
								){
									$arMegaItem['SELECTED'] = CMenu::IsItemSelected($url, $cur_page, $cur_page_no_index);
								}

								if($arMegaItems[$arLink['IBLOCK_SECTION_ID']]){
									$arMegaItems[$arLink['IBLOCK_SECTION_ID']]['IS_PARENT'] = 1;
									$arMegaItems[$arLink['IBLOCK_SECTION_ID']]['CHILD'][] =& $arMegaItems[$arLink['ID']];
								}
							}
							else{
								$arMegaLinks[] =& $arMegaItems[$arLink['ID']];
							}

							unset($arMegaItem);
						}
					}
				}
			}
		}
	}

	if($bUseMegaMenu && $arMegaLinks){
		foreach($arResult as $key => $arItem){
			foreach($arMegaLinks as $arLink){
				if($arItem['LINK'] == $arLink['LINK']){
					if($arResult[$key]['PARAMS']['MEGA_MENU_CHILDS']){
						array_splice($arResult, $key, 1, $arLink['CHILD']);
					}
					else{
						$arResult[$key]['CHILD'] =& $arLink['CHILD'];
						$arResult[$key]['IS_PARENT'] = boolval($arLink['CHILD']);
					}
				}
			}
		}
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
		<li<?=($arItem['SELECTED'] ? ' class="selected"' : '')?>>
			<a class="<?=isset($style["a"])?$style["a"]:""?> dark-color<?=($bParent ? ' parent' : '')?>" href="<?=$arItem["LINK"]?>" title="<?=$arItem["TEXT"]?>">
				<span><?=$arItem['TEXT']?></span>
				<?if($bParent):?>
					<span class="arrow"><i class="svg svg_triangle_right"></i></span>
				<?endif;?>
			</a>
			<?if($bParent):?>
				<ul class="dropdown">
					<li class="menu_back"><a href="" class="dark-color" rel="nofollow"><i class="svg svg-arrow-right"></i><?=GetMessage('NEXT_T_MENU_BACK')?></a></li>
					<li class="menu_title"><a href="<?=$arItem['LINK'];?>"><?=$arItem['TEXT']?></a></li>
					<?foreach($arItem['CHILD'] as $arSubItem):?>
						<?$bShowChilds = $arParams['MAX_LEVEL'] > $arSubItem['DEPTH_LEVEL'];?>
						<?$bParent = $arSubItem['CHILD'] && $bShowChilds;?>
						<li<?=($arSubItem['SELECTED'] ? ' class="selected"' : '')?>>
							<a class="dark-color<?=($bParent ? ' parent' : '')?>" href="<?=$arSubItem["LINK"]?>" title="<?=$arSubItem["TEXT"]?>">
								<span><?=$arSubItem['TEXT']?></span>
								<?if($bParent):?>
									<span class="arrow"><i class="svg svg_triangle_right"></i></span>
								<?endif;?>
							</a>
							<?if($bParent):?>
								<ul class="dropdown">
									<li class="menu_back"><a href="" class="dark-color" rel="nofollow"><i class="svg svg-arrow-right"></i><?=GetMessage('NEXT_T_MENU_BACK')?></a></li>
									<li class="menu_title"><a href="<?=$arSubItem['LINK'];?>"><?=$arSubItem['TEXT']?></a></li>
									<?foreach($arSubItem["CHILD"] as $arSubSubItem):?>
										<?$bShowChilds = $arParams['MAX_LEVEL'] > $arSubSubItem['DEPTH_LEVEL'];?>
										<?$bParent = $arSubSubItem['CHILD'] && $bShowChilds;?>
										<li<?=($arSubSubItem['SELECTED'] ? ' class="selected"' : '')?>>
											<a class="dark-color<?=($bParent ? ' parent' : '')?>" href="<?=$arSubSubItem["LINK"]?>" title="<?=$arSubSubItem["TEXT"]?>">
												<span><?=$arSubSubItem['TEXT']?></span>
												<?if($bParent):?>
													<span class="arrow"><i class="svg svg_triangle_right"></i></span>
												<?endif;?>
											</a>
											<?if($bParent):?>
												<ul class="dropdown">
													<li class="menu_back"><a href="" class="dark-color" rel="nofollow"><i class="svg svg-arrow-right"></i><?=GetMessage('NEXT_T_MENU_BACK')?></a></li>
													<li class="menu_title"><a href="<?=$arSubSubItem['LINK'];?>"><?=$arSubSubItem['TEXT']?></a></li>
													<?foreach($arSubSubItem["CHILD"] as $arSubSubSubItem):?>
														<li<?=($arSubSubSubItem['SELECTED'] ? ' class="selected"' : '')?>>
															<a class="dark-color" href="<?=$arSubSubSubItem["LINK"]?>" title="<?=$arSubSubSubItem["TEXT"]?>">
																<span><?=$arSubSubSubItem['TEXT']?></span>
															</a>
														</li>
													<?endforeach;?>
												</ul>
											<?endif;?>
										</li>
									<?endforeach;?>
								</ul>
							<?endif;?>
						</li>
					<?endforeach;?>
				</ul>
			<?endif;?>
		</li>
    <?
	}
}
?>