<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
?>

<div class="bx-sbb-empty-cart-container">
	<div class="bx-sbb-empty-cart-image">
		<img src="" alt="">
	</div>
	<div class="bx-sbb-empty-cart-text"><?=Loc::getMessage("SBB_EMPTY_BASKET_TITLE")?></div>
	<?
	if (!empty($arParams['EMPTY_BASKET_HINT_PATH']))
	{
		?>
		<div class="bx-sbb-empty-cart-desc">
			<?=Loc::getMessage('SBB_EMPTY_BASKET_HINT')?>
			<br/><br/>
			<div>
				<a class="btn btn-default btn-lg" href="<?=$arParams['EMPTY_BASKET_HINT_PATH']?>"><?=Loc::getMessage('SBB_EMPTY_BASKET_BTN')?></a>
			</div>
		</div>
		<?
	}
	?>
</div>