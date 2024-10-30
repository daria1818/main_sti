<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<?
$sliderID = "specials_slider_wrapp_" . $this->randString();
$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = unserialize($notifyOption);
?>


<?
if ($templateData['ASSOCIATED']) {
    $GLOBALS['arrFilterAssoc'] = array('ID' => $templateData['ASSOCIATED']);
}

$GLOBALS['arrFilterAssoc']['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
CNext::makeElementFilterInRegion($GLOBALS['arrFilterAssoc'], false, $bSetLinkRegionFilter = $arParams['FILTER_NAME'] === 'arRegionLink');


?>






<? if ($arResult["ITEMS"]): ?>
    <? foreach ($arResult["ITEMS"] as $key => $arItem): ?>
        <?
        $sku = CCatalogSKU::getOffersList(
            $arItem['ID'],
            // 91377,
            0,
            array('ACTIVE' => 'Y'),
            array('NAME', 'CODE', 'SECTION_CODE'),
            array("CODE" => array('HEIGHT', 'WIDTH'))
        );
        if (count($sku)) {
            foreach ($sku as $key => $value) {
                foreach ($value as $sku) {
                    $offer = CCatalogProduct::GetByID($sku['ID']);
                    if ($offer['QUANTITY']) {
                        $ar_res = CPrice::GetBasePrice($sku['ID']);
                        // получаем свойство торговых предложений
                        $arFilter = [
                            "IBLOCK_ID" => '81',
                            "ID" => $sku['ID'],

                        ];
                        $arSelect = [
                            "ID",
                            'PROPERTY_CML2_ATTRIBUTES',
                        ];
                        $dbGet = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
                        if ($arElem = $dbGet->Fetch()) $arItem['SROK'] = $arElem['PROPERTY_CML2_ATTRIBUTES_VALUE'];

                        // получаем скидку
                        $dbProductDiscounts = CCatalogDiscount::GetList(
                            array("SORT" => "ASC"),
                            array(
                                "PRODUCT_ID" => $sku['ID'],
                                "ACTIVE" => "Y",
                            ),
                            false,
                            false,
                            array(
                                "ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO",
                                "RENEWAL", "NAME", "SORT", "MAX_DISCOUNT", "VALUE_TYPE",
                                "VALUE", "CURRENCY", "PRODUCT_ID"
                            )
                        );
                        if ($arProductDiscounts = $dbProductDiscounts->Fetch()) $arItem['SALE'] = $arProductDiscounts;

                        $offer['NAME'] = $sku['NAME'];
                        $offer['CODE'] = $sku['CODE'];
                        break;
                    }
                }
            }
            $arItem['ID'] = $offer["ID"];
            $arItem['NAME'] = $offer["NAME"];
            $arItem['PRICE'] = $ar_res['PRICE'];
            $arItem['CURRENCY'] = $ar_res['CURRENCY'];
            if ($arItem['SROK'] == 'Базовый') $arItem['SROK'] = 'Более 14 мес';


            if ($arItem['SALE']) {
                $sale = $arItem['SALE']['VALUE'];
                $arItem['PRICE_SALE'] = ($arItem['PRICE'] / 100) * $sale;
                $arItem['NEW_PRICE'] = $arItem['PRICE'] - $arItem['PRICE_SALE'];
                $arItem['PRICE_ECONOMY'] = $arItem['PRICE'] - $arItem['NEW_PRICE'];
            }
        }

        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
        $totalCount = CNext::GetTotalCount($arItem, $arParams);
        $arQuantityData = CNext::GetQuantityArray($totalCount);
        $arItem["FRONT_CATALOG"] = "Y";

        $strMeasure = '';
        if ($arItem["OFFERS"]) {
            $strMeasure = $arItem["MIN_PRICE"]["CATALOG_MEASURE_NAME"];
        } else {
            if (($arParams["SHOW_MEASURE"] == "Y") && ($arItem["CATALOG_MEASURE"])) {
                $arMeasure = CCatalogMeasure::getList(array(), array("ID" => $arItem["CATALOG_MEASURE"]), false, false, array())->GetNext();
                $strMeasure = $arMeasure["SYMBOL_RUS"];
            }
        }

        // $elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);
        $elementName = $arItem['NAME'];
        ?>
        <? $arAddToBasketData = CNext::GetAddToBasketArray($arItem, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], true); ?>


		<li id="<?= $this->GetEditAreaId($arItem['ID']); ?>" class="catalog_item visible">
			<div class="inner_wrap">
				<div class="image_wrapper_block">
                    <? if ($arItem["PROPERTIES"]["HIT"]["VALUE"] || ($arParams["SALE_STIKER"] && $arItem["PROPERTIES"][$arParams["SALE_STIKER"]]["VALUE"])) { ?>
						<div class="stickers">
                            <? if ($arItem["PROPERTIES"]["HIT"]["VALUE"]): ?>
                                <? $prop = ($arParams["STIKERS_PROP"] ? $arParams["STIKERS_PROP"] : "HIT"); ?>
                                <? foreach (CNext::GetItemStickers($arItem["PROPERTIES"][$prop]) as $arSticker): ?>
									<div>
										<div class="<?= $arSticker['CLASS'] ?>"><?= $arSticker['VALUE'] ?></div>
									</div>
                                <? endforeach; ?>
                            <? endif; ?>
                            <? if ($arParams["SALE_STIKER"] && $arItem["PROPERTIES"][$arParams["SALE_STIKER"]]["VALUE"]) { ?>
								<div>
									<div class="sticker_sale_text"><?= $arItem["PROPERTIES"][$arParams["SALE_STIKER"]]["VALUE"]; ?></div>
								</div>
                            <? } ?>
						</div>
                    <? } ?>
                    <? if ($arParams["DISPLAY_WISH_BUTTONS"] != "N" || $arParams["DISPLAY_COMPARE"] == "Y" || $arParams['GALLERY_ITEM_SHOW'] == 'Y'): ?>
						<div class="like_icons">
                            <? if ($arAddToBasketData["CAN_BUY"] && empty($arItem["OFFERS"]) && $arParams["DISPLAY_WISH_BUTTONS"] != "N"): ?>

								<div class="wish_item_button" <?= ($arAddToBasketData['CAN_BUY'] ? '' : 'style="display:none"'); ?>>
									<span title="<?= GetMessage('CATALOG_WISH') ?>" class="wish_item to"
										  data-item="<?= $arItem["ID"] ?>"><i></i></span>
									<span title="<?= GetMessage('CATALOG_WISH_OUT') ?>" class="wish_item in added"
										  style="display: none;" data-item="<?= $arItem["ID"] ?>"><i></i></span>
								</div>
                            <? endif; ?>
                            <? if ($arParams["DISPLAY_COMPARE"] == "Y"): ?>
								<div class="compare_item_button">
									<span title="<?= GetMessage('CATALOG_COMPARE') ?>" class="compare_item to"
										  data-iblock="<?= $arParams["IBLOCK_ID"] ?>"
										  data-item="<?= $arItem["ID"] ?>"><i></i></span>
									<span title="<?= GetMessage('CATALOG_COMPARE_OUT') ?>" class="compare_item in added"
										  style="display: none;" data-iblock="<?= $arParams["IBLOCK_ID"] ?>"
										  data-item="<?= $arItem["ID"] ?>"><i></i></span>
								</div>
                            <? endif; ?>
                            <? if ($arParams['GALLERY_ITEM_SHOW'] == 'Y'): ?>
								<div class="fast_view_wrapper">
								<span>
									<? if ($fast_view_text_tmp = CNext::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
                                        $fast_view_text = $fast_view_text_tmp;
                                    else
                                        $fast_view_text = GetMessage('FAST_VIEW'); ?>
									<i class="fast_view_block" data-event="jqm" data-param-form_id="fast_view"
									   data-param-iblock_id="<?= $arParams["IBLOCK_ID"]; ?>"
									   data-param-id="<?= $arItem["ID"]; ?>"
									   data-param-fid="<?= $arItemIDs["strMainID"]; ?>"
									   data-param-item_href="<?= urlencode($arItem["DETAIL_PAGE_URL"]); ?>"
									   title="<?= $fast_view_text; ?>" data-name="fast_view">
									</i>
								</span>
								</div>
                            <? endif; ?>
						</div>

                    <? endif; ?>
                    <? $arParams['EVENT_TYPE'] = 'catalog_top_main_view' ?>
                    <? if ($arParams['GALLERY_ITEM_SHOW'] == 'Y'): ?>
                        <? \Aspro\Functions\CAsproNext::showSectionGallery(array('ITEM' => $arItem, 'RESIZE' => $arResult['CUSTOM_RESIZE_OPTIONS'])); ?>
                    <? else: ?>
                        <? \Aspro\Functions\CAsproNext::showImg($arParams, $arItem); ?>
                    <? endif; ?>
				</div>
				<div class="item_info">
					<div class="item_info--top_block">
						<div class="item-title">
							<a href="<?= $arItem["DETAIL_PAGE_URL"] ?>"
							   class="dark_link"><span><?= $elementName ?></span></a>
						</div>
                        <? if ($arParams["SHOW_RATING"] == "Y"): ?>
							<div class="rating">
                                <? //$frame = $this->createFrame('dv_'.$arItem["ID"])->begin('');?>
                                <? if ($arParams['REVIEWS_VIEW']): ?>
									<div class="blog-info__rating--top-info EXTENDED">
										<div class="votes_block nstar with-text">
											<div class="ratings">
                                                <? $message = $arItem['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arItem['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'])) : GetMessage('VOTES_RESULT_NONE') ?>
												<div class="inner_rating" title="<?= $message ?>">
                                                    <? for ($i = 1; $i <= 5; $i++): ?>
														<div class="item-rating <?= $i <= $arItem['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? 'filled' : '' ?>"></div>
                                                    <? endfor; ?>
												</div>
											</div>
										</div>
									</div>
                                <? else: ?>
                                    <? $APPLICATION->IncludeComponent(
                                        "bitrix:iblock.vote",
                                        "element_rating_front",
                                        array(
                                            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                            "IBLOCK_ID" => $arItem["IBLOCK_ID"],
                                            "ELEMENT_ID" => $arItem["ID"],
                                            "MAX_VOTE" => 5,
                                            "VOTE_NAMES" => array(),
                                            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                            "CACHE_TIME" => $arParams["CACHE_TIME"],
                                            "DISPLAY_AS_RATING" => 'vote_avg'
                                        ),
                                        $component, array("HIDE_ICONS" => "Y")
                                    ); ?>
                                <? endif; ?>
                                <? //$frame->end();?>
							</div>
                        <? endif; ?>
						<div class="sa_block">
                            <?= $arQuantityData["HTML"]; ?>
						</div>
					</div>
					<div class="item_info--bottom_block">
						<div class="cost prices clearfix">
                            <? if ($arItem["OFFERS"]): ?>
                                <? \Aspro\Functions\CAsproSku::showItemPrices($arParams, $arItem, $item_id, $min_price_id, array(), ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y")); ?>
                            <? else: ?>
                                <?
                                if (isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX']) // USE_PRICE_COUNT
                                {
                                    ?>
                                    <? if ($arItem['ITEM_PRICE_MODE'] == 'Q' && count($arItem['PRICE_MATRIX']['ROWS']) > 1):?>
                                    <?= CNext::showPriceRangeTop($arItem, $arParams, GetMessage("CATALOG_ECONOMY")); ?>
                                <?endif; ?>
                                    <?= CNext::showPriceMatrix($arItem, $arParams, $strMeasure, $arAddToBasketData); ?>
                                    <?
                                } elseif ($arItem["PRICES"]) {
                                    ?>
                                    <? \Aspro\Functions\CAsproItem::showItemPrices($arParams, $arItem["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y")); ?>
                                <? } ?>
                            <? endif; ?>
						</div>
					</div>
				</div>

				<div class="footer_button">
                    <?php if ($totalCount) : ?>
					<div class="item_info--bottom_block">

						<div class="cost prices clearfix">

<!--							<div class="with_matrix with_old" style="display:none;">-->
<!--								<div class="price price_value_block"><span class="values_wrapper"></span></div>-->
<!--								<div class="price discount"></div>-->
<!--								<div class="sale_block matrix" style="display:none;">-->
<!--									<div class="sale_wrapper">-->
<!--										<div class="text"><span class="title">Экономия</span><span-->
<!--													class="values_wrapper"></span></div>-->
<!--										<div class="clearfix"></div>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->
<!--							<div class="ce_cmp_visible">-->
<!--								<div class="price" id="bx_3966226736_91274_price">-->
<!--									от <span class="values_wrapper">1&nbsp;000 руб.</span></div>-->
<!--								<div class="price discount"><span class="values_wrapper">1&nbsp;250 руб.</span></div>-->
<!--								<div class="sale_block">-->
<!--									<div class="sale_wrapper"><span class="title">Экономия</span>-->
<!--										<div class="text"><span class="values_wrapper">250 руб.</span></div>-->
<!--										<div class="clearfix"></div>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->

							<div class="js_price_wrapper price">
								<div class="price_matrix_wrapper ">
									<?php
										if($arItem['SALE']) {
                                            $arItem['PRICE_OLD'] = $arItem['PRICE'];
                                            $arItem['PRICE'] = $arItem['NEW_PRICE'];
										}

										$arItem['PRICE'] = round($arItem['PRICE']);
										$pattern2 = '#(?<=\d)(?=(\d{3})+(?!\d))#';
										$arItem['PRICE'] = preg_replace($pattern2, ' ', $arItem['PRICE']);

                                    	$arItem['PRICE_OLD'] = round($arItem['PRICE_OLD']);
										$pattern2 = '#(?<=\d)(?=(\d{3})+(?!\d))#';
                                    	$arItem['PRICE_OLD'] = preg_replace($pattern2, ' ', $arItem['PRICE_OLD']);

                                    	$arItem['PRICE_ECONOMY'] = round($arItem['PRICE_ECONOMY']);
										$pattern2 = '#(?<=\d)(?=(\d{3})+(?!\d))#';
                                    	$arItem['PRICE_OLD'] = preg_replace($pattern2, ' ', $arItem['PRICE_ECONOMY']);

									?>



									<div class="price">
										<?php if ($arItem['PRICE']) : ?>
												<div class="price"">
													<span class="values_wrapper"><?= $arItem['PRICE'] . ' руб.' ?></span>
												</div>
										<?php endif; ?>
									</div>
								<?php if($arItem['SALE']) : ?>
									<div class="price discount">
										<span class="values_wrapper">
											<span class="price_value"><?= $arItem['PRICE_OLD']  ?></span>
											<span class="price_currency"> руб.</span>
										</span>
									</div>

									<div class="sale_block">
										<div class="sale_wrapper">
											<span class="title">Экономия</span>
											<div class="text">
												<span class="values_wrapper">
													<span class="price_value"><?= $arItem['PRICE_ECONOMY'] ?></span>
													<span class="price_currency"> руб.</span>
												</span>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								<?php endif; ?>



								</div>
							</div>
						</div>


                            <?php if ($arItem['SROK']) : ?>
								<div class="sku_props">
									<div class="bx_catalog_item_scu wrapper_sku">
										<div class="item_wrapper">
											<div class="bx_item_detail_size">
												<span class="show_class bx_item_section_name"><span>Срок годности</span></span>
												<div class="bx_size_scroller_container">
													<div class="bx_size">
														<ul class="list_values_wrapper">
															<li class="item active"><i></i>
																<span class="cnt"><?= $arItem['SROK'] ?></span>
															</li>
														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
                            <?php endif; ?>

							<div class="counter_wrapp ">
								<div class="counter_block" data-item="<?= $arItem["ID"]; ?>">
									<span class="minus">-</span>
									<input type="text" class="text focus" name="quantity" value="1">
									<span class="plus">+</span>
								</div>

								<div class="button_block ">

									<!--noindex--><span
											data-currency="RUB"
											class="small to-cart btn btn-default transition_bg animate-load"
											data-item="<?= $arItem["ID"]; ?>"
											data-float_ratio="1"
											data-ratio="1"
											data-props=""
											data-part_props="Y"
											data-add_props="Y"
											data-empty_props="Y"
											data-offers=""
											data-iblockid="30"
											data-quantity="1"><i></i>
								<span>В корзину</span></span>

									<a rel="nofollow" href="/basket/"
									   class="small in-cart btn btn-default transition_bg"
									   data-item="<?= $arItem["ID"]; ?>" style="display:none;"><i></i>
										<span>В корзине</span></a>
									<!--/noindex--></div>
							</div>
                            <?php else : ?>
                                <?= $arAddToBasketData["HTML"] ?>
                            <?php endif; ?>

						</div>
					</div>
		</li>


    <? endforeach; ?>
<? else: ?>
	<div class="empty_items"></div>
	<script type="text/javascript">
		$('.top_blocks li[data-code=BEST]').remove();
		$('.tabs_content tab[data-code=BEST]').remove();
		if (!$('.slider_navigation.top li').length) {
			$('.tab_slider_wrapp.best_block').remove();
		}
		if ($('.bottom_slider').length) {
			if ($('.tabs_content .empty_items').length) {
				$('.tabs_content .empty_items').each(function () {
					var _this = $(this);
					if (_this.closest('.drag_block_detail.separate_block').length) {
						_this.closest('.drag_block_detail.separate_block').remove();
					} else {
						var index = _this.closest('.tab').index();
						$('.top_blocks .tabs>li:eq(' + index + ')').remove();
						$('.tabs_content .tab:eq(' + index + ')').remove();
					}

				})
				$('.tabs_content .tab.cur').trigger('click');
			}
		}
	</script>
<? endif; ?>
