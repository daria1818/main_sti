<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<?
global $arTheme, $noMegaMenu;
$iVisibleItemsMenu = ($arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] ? $arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] : 10);
$bManyItemsMenu = ($arTheme['USE_BIG_MENU']['VALUE'] == 'Y');

if ($noMegaMenu) {
	$bManyItemsMenu = false;
}
?>
<?if($arResult):?>
	<?if (!function_exists('showSubItemss')) {
		function showSubItemss($arParams = [
			'HAS_PICTURE' => false,
			'HAS_ICON' => false,
			'WIDE_MENU' => false,
			'SHOW_CHILDS' => false,
			'VISIBLE_ITEMS_MENU' => 0,
			'MAX_LEVEL' => 0,
			'ITEM' => [],
		]){?>
			<?if($arParams['HAS_PICTURE'] && $arParams['WIDE_MENU']):
				if ($arParams['ITEM']['PARAMS']['UF_CATALOG_ICON']) {
					$arImg=CFile::ResizeImageGet($arParams['ITEM']['PARAMS']['UF_CATALOG_ICON'], Array('width'=>50, 'height'=>50), BX_RESIZE_IMAGE_PROPORTIONAL, true);													
				} elseif($arParams['ITEM']['PARAMS']['PICTURE']) {
					$arImg=CFile::ResizeImageGet($arParams['ITEM']['PARAMS']['PICTURE'], array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_PROPORTIONAL);													
				}
																
				if(is_array($arImg)):?>
					<div class="clio-catalog-menu__image">
						<img src="<?=$arImg["src"]?>" alt="<?=$arParams['ITEM']["TEXT"]?>" title="<?=$arParams['ITEM']["TEXT"]?>" />
					</div>
				<?endif;?>
			<?endif;?>
			<div class="clio-catalog-menu__description">
				<p class="clio-catalog-menu__title">
					<a class="clio-catalog-menu__title_link" href="<?=$arParams['ITEM']["LINK"]?>"><?=$arParams['ITEM']["TEXT"]?></a>
				</p>
			

				<?if($arParams['ITEM']["CHILD"] && $arParams['SHOW_CHILDS']):?>
					<?$iCountChilds = count($arParams['ITEM']["CHILD"]);?>
					<?$iVisibleItemsMenu = $arParams['VISIBLE_ITEMS_MENU'];?>
					<ul class="clio-catalog-menu__nav-item">
						<?foreach($arParams['ITEM']["CHILD"] as $key => $arSubSubItem):?>
							<?$bShowChilds = $arParams["MAX_LEVEL"] > 3;?>
							<li class="">
								<a class="clio-catalog-menu__link" href="<?=$arSubSubItem["LINK"]?>"><?=$arSubSubItem["TEXT"]?></a>
								<?/*if($arSubSubItem["CHILD"] && $bShowChilds):?>
									<?foreach($arSubSubItem["CHILD"] as $arSubSubSubItem):?>
									<?endforeach?>
								<?endif*/?>
							</li>
						<?endforeach?>
					</ul>
				<?endif;?>
			</div>
		<?}?>
	<?}?>
<nav class="clio-header-menu__nav_">
	<ul class="clio-header-menu__list_">
		<?foreach($arResult as $arItem):?>
			<?
			$bShowChilds = $arParams["MAX_LEVEL"] > 1;
			$bWideMenu = (isset($arItem['PARAMS']['CLASS']) && strpos($arItem['PARAMS']['CLASS'], 'wide_menu') !== false);
			$arItem['bManyItemsMenu'] = $bManyItemsMenu;
			if(!$bWideMenu) {
				$arItem['bManyItemsMenu'] = false;
			}
			?>
			<li class="clio-header-menu__item_ <?=($arItem["CHILD"] && $bShowChilds ? "clio-header-menu__dropdown_" : "")?>">
				<a href="<?=$arItem["LINK"]?>" class="clio-header-menu__link_">
					<?=$arItem["TEXT"]?>
					<?if($arItem["CHILD"] && $bShowChilds):?>
						<svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.17899 1.17899C1.41764 0.940337 1.80458 0.940337 2.04323 1.17899L6.5 5.63576L10.9568 1.17899C11.1954 0.940337 11.5824 0.940337 11.821 1.17899C12.0597 1.41764 12.0597 1.80458 11.821 2.04323L6.93212 6.93212C6.69347 7.17077 6.30653 7.17077 6.06788 6.93212L1.17899 2.04323C0.940337 1.80458 0.940337 1.41764 1.17899 1.17899Z" fill="#EC1E22" stroke="#EC1E22" stroke-width="0.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<?endif?>
				</a>
				<?if($arItem["CHILD"] && $bShowChilds):?>
					<?if($arItem["PARAMS"]["IS_CATALOG"]):?>
						<div class="clio-catalog-menu-wrapper">
							<div class="clio-container">
								<div class="clio-catalog-menu-inner">
									<?foreach($arItem["CHILD"] as $arSubItem):?>
										<?
										$bShowChilds = $arParams["MAX_LEVEL"] > 2;
										$bHasSvgIcon = (isset($arSubItem['PARAMS']['UF_CATALOG_ICON']) && $arSubItem['PARAMS']['UF_CATALOG_ICON']);
										$bHasImg = (isset($arSubItem['PARAMS']['PICTURE']) && $arSubItem['PARAMS']['PICTURE']);
										$bHasPicture = (($bHasSvgIcon || $bHasImg) && $arTheme['SHOW_CATALOG_SECTIONS_ICONS']['VALUE'] == 'Y');
										?>
										<div class="clio-catalog-menu__item">
											<?=showSubItemss([
												'HAS_PICTURE' => $bHasPicture,
												'HAS_ICON' => $bIcon,
												'WIDE_MENU' => $bWideMenu,
												'SHOW_CHILDS' => $bShowChilds,
												'VISIBLE_ITEMS_MENU' => $iVisibleItemsMenu,
												'ITEM' => $arSubItem,
												'MAX_LEVEL' => $arParams["MAX_LEVEL"]
											]);?>
										</div>
									<?endforeach?>
								</div>
							</div>
						</div>
					<?else:?>
						<ul class="clio-header-submenu__list_">
							<?foreach($arItem["CHILD"] as $arSubItem):?>
								<li class="clio-menu-submenu__item">
									<a href="<?=$arSubItem["LINK"]?>" class="clio-menu-submenu__link"><?=$arSubItem["TEXT"]?></a>
								</li>
							<?endforeach?>
						</ul>
					<?endif?>
				<?endif?>
			</li>
		<?endforeach?>
	</ul>
</nav>
<?endif;?>