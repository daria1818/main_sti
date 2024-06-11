<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
global $arTheme, $arRegion;
$arRegions = CNextRegionality::getRegions();
if($arRegion)
	$bPhone = ($arRegion['PHONES'] ? true : false);
else
	$bPhone = ((int)$arTheme['HEADER_PHONES'] ? true : false);
$logoClass = ($arTheme['COLORED_LOGO']['VALUE'] !== 'Y' ? '' : ' colored');
?>
<?
if("2022-08-12" <= date("Y-m-d") && date("Y-m-d") <= "2022-08-29"){
	if(!$_COOKIE['view_alert']){
		setcookie('view_alert','Y', 0, '/');?>
		<div class="alert-text-wrap">
			<div class="alert-text">
				Дорогие друзья! В связи с проведением ремонтных работ на складе с <span>24</span> по <span>29</span> августа включительно наш магазин не будет осуществлять отгрузку товаров. Просим вас заблаговременно разместить заказы!
				<button class="alert-accept js-accept-alert">ОК</button>
			</div>
		</div>
	<?}
}?>
<div class="clio-desktop_header">
	<div class="clio-adress-wrap">
	<address class="clio-header_first_part maxwidth-theme">
			<div class="clio-addres_flex">
				<?if(SITE_ID_CUSTOM == 's1'):?>
					<?$APPLICATION->IncludeFile(
						SITE_DIR."include/top_page/site-address_v2.php", 
						array(), 
						array(
							"MODE" => "html",
							"NAME" => "Address",
							"TEMPLATE" => "include_area.php",
						)
					);?>
				<?else:?>
					<?$APPLICATION->IncludeFile(
						SITE_DIR."include_".SITE_ID_CUSTOM."/top_page/site-address.php", 
						array(), 
						array(
							"MODE" => "html",
							"NAME" => "Address",
							"TEMPLATE" => "include_area.php",
						)
					);?>
				<?endif?>
				<div class="clio-contact_header">
					<img class="inline-search-show clio-icon_search" src="<?=SITE_TEMPLATE_PATH?>/images/svg/lupa.svg" alt="поиск">
					<div class="clio-back_call_btn" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"><?=GetMessage("CALLBACK")?></div>
					<?if($bPhone):?>
						<?CNextCustom::ShowHeaderPhones();?>
					<?endif?>
				</div>
			</div>
		</address>
	</div>
	<div class="clio-header_second_part maxwidth-theme">
		<div class="clio-header_mobl">
			<button type="button" class="clio-mobl_menu_btn clio-dropdown-menu" aria-expanded="false" data-menu-mobl-button="">
				<img src="<?=SITE_TEMPLATE_PATH?>/images/svg/mobl_menu.svg" alt="кнопка мобильного меню">
			</button>

			<div class="clio-addres_flex clio-second_part">
				<div class="clio-logo-title-wrap">
					<div class="clio-logo">
						<?=CNext::ShowLogo();?>
					</div>
					<div class="clio-logo_titel">
						<p class="clio-logo_titel_text">
							<?
							$APPLICATION->IncludeFile(SITE_DIR."include/top_page/slogan.php", array(), 
								array(
									"MODE" => "html",
									"NAME" => "Text in title",
									"TEMPLATE" => "include_area.php",
								)
							);?>
						</p>
					</div>
				</div>
				<div class="clio-header-menu_">
					<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
						array(
							"COMPONENT_TEMPLATE" => ".default",
							"PATH" => SITE_DIR."include/menu/menu.top.php",
							"AREA_FILE_SHOW" => "file",
							"AREA_FILE_SUFFIX" => "",
							"AREA_FILE_RECURSIVE" => "Y",
							"EDIT_TEMPLATE" => "include_area.php"
						),
						false, array("HIDE_ICONS" => "Y")
					);?>
				</div>
				<div class="clio-menu-options-prodact">
					<?=CNextCustom::ShowBasketWithCompareLink()?>
				</div>
			</div>
		</div>
	</div>
</div>