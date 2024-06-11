<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $mobileColumns
 * @var array $arParams
 * @var string $templateFolder
 */

$usePriceInAdditionalColumn = in_array('PRICE', $arParams['COLUMNS_LIST']) && $arParams['PRICE_DISPLAY_MODE'] === 'Y';
$useSumColumn = in_array('SUM', $arParams['COLUMNS_LIST']);
$useActionColumn = in_array('DELETE', $arParams['COLUMNS_LIST']);

$restoreColSpan = 2 + $usePriceInAdditionalColumn + $useSumColumn + $useActionColumn;

$positionClassMap = array(
	'left' => 'basket-item-label-left',
	'center' => 'basket-item-label-center',
	'right' => 'basket-item-label-right',
	'bottom' => 'basket-item-label-bottom',
	'middle' => 'basket-item-label-middle',
	'top' => 'basket-item-label-top'
);

$discountPositionClass = '';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION']))
{
	foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos)
	{
		$discountPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION']))
{
	foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos)
	{
		$labelPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}
?>
<script id="basket-item-template" type="text/html">

	{{#SHOW_RESTORE}}
	{{/SHOW_RESTORE}}
	{{^SHOW_RESTORE}}
	<div class="clio-products-item clio-discount-product" id="basket-item-{{ID}}" data-entity="basket-item" data-id="{{ID}}">
			<div class="clio-products-item-inner">
				<div class="clio-products-item-inner__info">
					<a href="{{DETAIL_PAGE_URL}}" class="clio-products-item-open-product">
						<div class="clio-products-item-inner__img">
							{{#SHOW_DISCOUNT_PRICE}}
								<span class="clio-product-discount">-{{DISCOUNT_PRICE_PERCENT_FORMATED}}</span>
							{{/SHOW_DISCOUNT_PRICE}}
							<picture>
								<img src="{{{IMAGE_URL}}}{{^IMAGE_URL}}<?=$templateFolder?>/images/no_photo.png{{/IMAGE_URL}}" alt="" class="clio-card__img">
							</picture>
						</div>
						<div class="clio-products-item-inner__title">
							<p class="clio-products-item-inner__name">{{NAME}}</p>
							<?
							if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])):
								foreach($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName):
									switch(trim((string)$blockName)):
										case 'columns':
											?>
											{{#COLUMN_LIST}}
												{{#IS_TEXT}}
												<p class="clio-products-item-inner__setting">
													{{{NAME}}} {{{VALUE}}}
												</p>
												{{/IS_TEXT}}
												{{#IS_DISCOUNT}}
												<p class="clio-products-item-inner__setting clio-products-item-inner__setting_sale">
													{{{NAME}}} {{{VALUE}}}
												</p>
												{{/IS_DISCOUNT}}
											{{/COLUMN_LIST}}
											<?
										break;
									endswitch;
								endforeach;
							endif;
							?>
						</div>
					</a>
				</div>

				<div class="clio-products-item-inner__options">
					<div class="clio-products-item-inner__info-right">
						<div class="clio-products-item-inner__sale">
							{{#SHOW_DISCOUNT_PRICE}}-{{/SHOW_DISCOUNT_PRICE}}{{DISCOUNT_PRICE_PERCENT_FORMATED}}
						</div>
						<div class="clio-products-item-inner__sale-price">
							<div class="clio-products-item-inner__price">
								{{{PRICE_FORMATED}}}
							</div>
							{{#SHOW_DISCOUNT_PRICE}}
								<div class="clio-products-item-inner__price clio-old-price">
									{{{FULL_PRICE_FORMATED}}}
								</div>
							{{/SHOW_DISCOUNT_PRICE}}
						</div>
						<div class="clio-products-item-inner__quantity">
							<div class="clio-prod_count_btn" data-entity="basket-item-quantity-block">
								<span class="clio-btn-count clio-minus" data-entity="basket-item-quantity-minus">-</span>
								<input 
									class="clio-btn-count clio-count" 
									type="number" 
									value="{{QUANTITY}}"
									{{#NOT_AVAILABLE}} disabled="disabled"{{/NOT_AVAILABLE}}
									data-value="{{QUANTITY}}" 
									data-entity="basket-item-quantity-field" 
									id="basket-item-quantity-{{ID}}">

								<span class="clio-btn-count clio-plus" data-entity="basket-item-quantity-plus">+</span>
							</div>
						</div>
						<?
						if ($useSumColumn):
							?>
							<div class="clio-products-item-inner__sale-amount">
								<div class="clio-products-item-inner__amount" id="basket-item-sum-price-{{ID}}">
									{{{SUM_PRICE_FORMATED}}}
								</div>
								{{#SHOW_DISCOUNT_PRICE}}
									<div class="clio-products-item-inner__old-amount" id="basket-item-sum-price-old-{{ID}}">
										{{{SUM_FULL_PRICE_FORMATED}}}
									</div>
									<div class="clio-price-economy">
										<p class="clio-price-economy__text">
											<?=Loc::getMessage('SBB_BASKET_ITEM_ECONOMY')?>
										</p>
										<p class="clio-background" id="basket-item-sum-price-difference-{{ID}}">
											{{{SUM_DISCOUNT_PRICE_FORMATED}}}
										</p>
									</div>
								{{/SHOW_DISCOUNT_PRICE}}
							</div>
						<?endif?>
					</div>
				</div>
			</div>
			<div class="clio-products-item-remove" data-entity="basket-item-delete">
				<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M17.6234 0.376577C18.1255 0.87868 18.1255 1.69275 17.6234 2.19485L2.19485 17.6234C1.69275 18.1255 0.87868 18.1255 0.376577 17.6234C-0.125526 17.1213 -0.125526 16.3073 0.376577 15.8051L15.8051 0.376577C16.3073 -0.125526 17.1213 -0.125526 17.6234 0.376577Z" fill="#D0CFCF"></path>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M0.376577 0.376577C0.87868 -0.125526 1.69275 -0.125526 2.19485 0.376577L17.6234 15.8051C18.1255 16.3073 18.1255 17.1213 17.6234 17.6234C17.1213 18.1255 16.3073 18.1255 15.8051 17.6234L0.376577 2.19485C-0.125526 1.69275 -0.125526 0.87868 0.376577 0.376577Z" fill="#D0CFCF"></path>
				</svg>
			</div>
		</div>
	{{/SHOW_RESTORE}}
</script>