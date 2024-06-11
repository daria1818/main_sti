<?
if($arItems){
	foreach($arItems as $arItem){
		$prop = CIBlockElement::GetByID($arItem['ID'])->GetNextElement()->GetProperties();
		if (!empty($prop['BREND']['VALUE'])){
			$arrItems2[$prop['BREND']['VALUE_ENUM_ID']]['name']= $prop['BREND']['VALUE'];
			if(isset($arrItems2[$prop['BREND']['VALUE_ENUM_ID']]['count'])){
				$arrItems2[$prop['BREND']['VALUE_ENUM_ID']]['count'] = $arrItems2[$prop['BREND']['VALUE_ENUM_ID']]['count'] + 1;
			}else{
				$arrItems2[$prop['BREND']['VALUE_ENUM_ID']]['count'] = 1;
			}
		}
	};
	$arDeleteParams = array('brand');
	if(preg_match_all('/PAGEN_\d+/i'.BX_UTF_PCRE_MODIFIER, $_SERVER['QUERY_STRING'], $arMatches)){
		$arPagenParams = $arMatches[0];
		$arDeleteParams = array_merge($arDeleteParams, $arPagenParams);
	}
?>
		<div class="top_block_filter_section toggle_menu">
			<div class="title">
				<a class="dark_link" title="Бренды" href="<?=$APPLICATION->GetCurPageParam('', $arDeleteParams)?>"> Бренды</a>
			</div>
				<div class="items"><?
				$distinct = array_count_values($arrItems);
				foreach ($arrItems2 as $key => $value) {
					if(!empty($key)){
					?>
						<div class="item">
							<a href="<?=$APPLICATION->GetCurPageParam('brand='.str_replace(' ','_',$value['name']), $arDeleteParams)?>" class="dark_link">
								<span class="item_title"><?=$value['name']?></span>
								<noindex><span class="item_count"><?=$value['count']?></span></noindex>
							</a>
						</div>
					<?
					}
				}
				?>
				</div>
		</div>
	<?
}
?><?
