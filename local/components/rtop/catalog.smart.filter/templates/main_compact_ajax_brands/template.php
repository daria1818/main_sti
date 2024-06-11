<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;
if($arResult["ITEMS"]){?>
	<div class="">
		<div class="">
			<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilterBrands">


				<div class="bx_filter_parameters">
					<input type="hidden" name="del_url" id="del_url" value="<?echo str_replace('/filter/clear/apply/','/',$arResult["SEF_DEL_FILTER_URL"]);?>" />
					<?foreach($arResult["HIDDEN"] as $arItem):?>
					<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
					<?endforeach;
					$isFilter=false;
					$numVisiblePropValues = 5;
?>


					<?//not prices
					foreach($arResult["ITEMS"] as $key=>$arItem)
					{
						if (strtolower($arItem["CODE"]) !== 'brend') continue;

						if(
							empty($arItem["VALUES"])
							|| isset($arItem["PRICE"])
						)
							continue;

						if (
							$arItem["DISPLAY_TYPE"] == "A"
							&& (
								$arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0
							)
						)
							continue;
						$class="";
						/*if($arItem["OPENED"]){
							if($arItem["OPENED"]=="Y"){
								$class="active";
							}
						}else*//*if($arItem["DISPLAY_EXPANDED"]=="Y"){
							$class="active";
						}*/
						$isFilter=true;
							
						?>
						<div class="bx_filter_parameters_box_brands prop_type_<?=$arItem["PROPERTY_TYPE"];?> " data-prop_code=<?=strtolower($arItem["CODE"]);?> data-property_id="<?=$arItem["ID"]?>">
						
							<div class="" >
								<div class="row  flexbox" style="margin: 0">
								<?
								$arCur = current($arItem["VALUES"]);
								switch ($arItem["DISPLAY_TYPE"]){
								default://CHECKBOXES
										$count=count($arItem["VALUES"]);
										$i=1;
										if(!$arItem["FILTER_HINT"]){
											$prop = CIBlockProperty::GetByID($arItem["ID"], $arItem["IBLOCK_ID"])->GetNext();
											$arItem["FILTER_HINT"]=$prop["HINT"];
										}
										if($arItem["IBLOCK_ID"]!=$arParams["IBLOCK_ID"] && strpos($arItem["FILTER_HINT"],'line')!==false){
											$isSize=true;
										}else{
											$isSize=false;
										}?>
										<?$j=1;
										$isHidden = false;?>
										<?foreach($arItem["VALUES"] as $val => $ar):?>
											
											<div class=" " >
											<input style="display: none"
											class="input-filter-brends"
												type="checkbox"
												value="<? echo $ar["HTML_VALUE"] ?>"
												name="<? echo $ar["CONTROL_NAME"] ?>"
												id="<? echo $ar["CONTROL_ID"].'aaa' ?>"
												<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
												<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
												onclick="smartFilter.click(this)"
											/>
											<label data-role="label_<?=$ar["CONTROL_ID"].'aaa'?>"  class=" item_brands <?=($i==$count ? "last" : "");?> <? echo $ar["DISABLED"] ? 'disabled': '' ?>" for="<? echo $ar["CONTROL_ID"].'aaa' ?>">
												<span class="bx_filter_input_checkbox">

													<span class="bx_filter_param_text " title="<?=$ar["VALUE"];?>" ><?=$ar["VALUE"];?><?
													if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"]) && !$isSize):
														?> (<span data-role="count_<?=$ar["CONTROL_ID"].'aaa'?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
													endif;?></span>
												</span>
											</label>
											<?$i++;?>
											<?$j++;?>
											</div>
										<?endforeach;?>
										<?if($isHidden):?>
											</div>
										
										<?endif;?>
								<?}?>
								</div>
								<div class="clb"></div>
								<div class="char_name">
									<div class="props_list">
										<?if($arParams["SHOW_HINTS"]){
											if(!$arItem["FILTER_HINT"]){
												$prop = CIBlockProperty::GetByID($arItem["ID"], $arParams["IBLOCK_ID"])->GetNext();
												$arItem["FILTER_HINT"]=$prop["HINT"];
											}?>
											<?if( $arItem["FILTER_HINT"] && strpos( $arItem["FILTER_HINT"],'line')===false){?>
												<div class="hint"><span class="icon"><i>?</i></span><span class="text"><?=Loc::getMessage('HINT');?></span><div class="tooltip" style="display: none;"><?=$arItem["FILTER_HINT"]?></div></div>
											<?}?>
										<?}?>
									</div>
								</div>
								<?if($arItem['CODE'] != 'IN_STOCK'):?>
									<div class="bx_filter_button_box active clearfix">
										<?/*<span class="btn btn-default"><?=Loc::getMessage("CT_BCSF_SET_FILTER")?></span>*/?>
										<span data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>" data-fi="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TI')?>" data-fr="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TR')?>" data-frm="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TRM')?>" class="bx_filter_container_modef"></span>
									</div>
								<?endif;?>
							</div>
						</div>
					<?}?>
				</div>

			
			</form>
			<div style="clear: both;"></div>
		</div>
	</div>
	<script>
		var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=$arParams["VIEW_MODE"];?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
// console.log(smartFilter);
		<?if(!$isFilter){?>
			$('.bx_filter_vertical').remove();
		<?}?>

		BX.message({
			SELECTED: '<? echo GetMessage("SELECTED"); ?>',
		});

		$(document).ready(function(){
			$('.bx_filter_search_reset').on('click', function(){
				<?if($arParams["SEF_MODE"]=="Y"){?>
					location.href=$('form.smartfilter').find('#del_url').val();
				<?}else{?>
					location.href=$('form.smartfilter').attr('action');
				<?}?>
			})
		})
	</script>
<?}?>
