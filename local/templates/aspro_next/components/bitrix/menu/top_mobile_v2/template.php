<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<?$isCatalogMenuExpanded = isset($arParams["CATALOG_MENU_EXPANDED"]) && $arParams["CATALOG_MENU_EXPANDED"] === "Y";?>
<?if($arResult):?>
	<div class="clio-sidenav__content">
		<?foreach($arResult as $arItem):?>
			<?$bShowChilds = $arParams['MAX_LEVEL'] > 1;?>
			<?$bParent = $arItem['CHILD'] && $bShowChilds;?>
			<?show_top_mobile_li($arItem, $arParams, $bParent);?>
		<?endforeach;?>
	</div>
<?endif?>