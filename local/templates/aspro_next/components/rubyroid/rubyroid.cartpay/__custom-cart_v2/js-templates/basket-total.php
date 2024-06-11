<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 */
?>
<script id="basket-total-template" type="text/html">
	<div class="clio-products__info" data-entity="basket-checkout-aligner">
		<?
		if ($arParams['HIDE_COUPON'] !== 'Y'):
			?>
			<div class="clio-products__info_promo">
				<p class="clio-promo-title"><?=Loc::getMessage('SBB_COUPON_ENTER')?>:</p>
				<div class="clio-promo-btn-wrap">
					<input class="clio-promo-input" type="text" placeholder="Купон" data-entity="basket-coupon-input">
					<button class="clio-promo-btn" type="button">Применить</button>
				</div>

				<div class="clio-promo-info_text">
					{{#COUPON_LIST}}
						<div class="clio-number-promocode">
							<b>{{COUPON}}</b> - <?=Loc::getMessage('SBB_COUPON')?> {{JS_CHECK_CODE}}
							{{#DISCOUNT_NAME}}({{DISCOUNT_NAME}}){{/DISCOUNT_NAME}}
							<span class="clio-promocode-remove" data-entity="basket-coupon-delete" data-coupon="{{COUPON}}">
								<svg width="10" height="10" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.6234 0.376577C18.1255 0.87868 18.1255 1.69275 17.6234 2.19485L2.19485 17.6234C1.69275 18.1255 0.87868 18.1255 0.376577 17.6234C-0.125526 17.1213 -0.125526 16.3073 0.376577 15.8051L15.8051 0.376577C16.3073 -0.125526 17.1213 -0.125526 17.6234 0.376577Z" fill="#D0CFCF"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M0.376577 0.376577C0.87868 -0.125526 1.69275 -0.125526 2.19485 0.376577L17.6234 15.8051C18.1255 16.3073 18.1255 17.1213 17.6234 17.6234C17.1213 18.1255 16.3073 18.1255 15.8051 17.6234L0.376577 2.19485C-0.125526 1.69275 -0.125526 0.87868 0.376577 0.376577Z" fill="#D0CFCF"></path></svg>
							</span>
						</div>
					{{/COUPON_LIST}}
				</div>

				<?
?>
			</div>
		<?endif?>
		
		<div class="clio-products__info_total">
			<div class="clio-summary-info">
				<div class="clio-quantity-price">В корзине <span>{{BASKET_ITEMS_COUNT}} товара</span></div>
				<div class="clio-total-price-wrap">
					<div class="clio-total-price">Итого: <span data-entity="basket-total-price"> {{{PRICE_FORMATED}}}</span></div>
					{{#DISCOUNT_PRICE_FORMATED}}
						<div class="clio-old-price">{{{PRICE_WITHOUT_DISCOUNT_FORMATED}}}</div>
						<div class="clio-price-economy">
							<p class="clio-price-economy__text"><?=Loc::getMessage('SBB_BASKET_ITEM_ECONOMY')?></p>
							<p class="clio-background">{{{DISCOUNT_PRICE_FORMATED}}}</p>
						</div>
					{{/DISCOUNT_PRICE_FORMATED}}
					{{#RB_PAY_COUNT}}
						<div class="clio-price-economy">
							<p class="clio-price-economy__text"><?=Loc::getMessage('RB_BASKET_ITEMS_PAY')?></p>
							<p class="clio-background">{{{RB_PAY_COUNT}}}</p>
						</div>
					{{/RB_PAY_COUNT}}
				</div>
			</div>
			<div class="basket-checkout-block basket-checkout-block-btn">
				<button class="clio-product-order{{#DISABLE_CHECKOUT}} disabled{{/DISABLE_CHECKOUT}}" data-entity="basket-checkout-button">
					<?=Loc::getMessage('SBB_ORDER')?>
				</button>
			</div>
		</div>
	</div>
</script>