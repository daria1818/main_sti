<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!--noindex-->
	<?$count=count($arResult);?>
	<?
	$type_svg = '';
	if($arParams["CLASS_ICON"])
	{
		$tmp = explode(' ', $arParams["CLASS_ICON"]);
		$type_svg = '_'.$tmp[0];
	}
	?>
	<a class="clio-link basket-link compare  <?=$arParams["CLASS_LINK"];?> <?=$arParams["CLASS_ICON"];?> <?=($count ? 'basket-count' : '');?>" href="<?=$arParams["COMPARE_URL"]?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage('CATALOG_COMPARE_ELEMENTS_ALL');?>">
		<span class="js-basket-block">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/svg/grafik.svg" class="clio-menu_icon_item clio-active" alt="корзина">
			<span class="clio-red_notification count"><?=$count;?></span>
		</span>
	</a>
	<?global $compare_items;
	$compare_items = array_keys($arResult);?>
<!--/noindex-->