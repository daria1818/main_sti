<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$aMenuLinksExt = array();

$catalog_id = \Bitrix\Main\Config\Option::get('aspro.next', 'CATALOG_IBLOCK_ID', CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0]);
//echo $catalog_id; die();//-

$arSections = CNextCache::CIBlockSection_GetList(array('SORT' => 'ASC', 'ID' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($catalog_id), 'MULTI' => 'Y')), array('IBLOCK_ID' => $catalog_id, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y', '<DEPTH_LEVEL' => \Bitrix\Main\Config\Option::get("aspro.next", "MAX_DEPTH_MENU", 2)), false, array('ID', 'ACTIVE', 'IBLOCK_ID', 'NAME', 'SECTION_PAGE_URL', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID', 'PICTURE', 'UF_REGION'));
$arSectionsByParentSectionID = CNextCache::GroupArrayBy($arSections, array('MULTI' => 'Y', 'GROUP' => array('IBLOCK_SECTION_ID')));

if($arSections)
	CNext::getSectionChilds(false, $arSections, $arSectionsByParentSectionID, $arItemsBySectionID, $aMenuLinksExt);

if($bUseMegaMenu = $GLOBALS['arTheme']['USE_MEGA_MENU']['VALUE'] === 'Y'){
	if($catalog_id){
		if($arCatalogIblock = CNextCache::$arIBlocksInfo[$catalog_id]){
			if($catalogPageUrl = str_replace('#SITE_DIR#', SITE_DIR, $arCatalogIblock['LIST_PAGE_URL'])){
				$menuIblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_megamenu'][0];
				if($menuIblockId){
				$menuRootCatalogSectionId = CNextCache::CIblockSection_GetList(array('SORT' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($menuIblockId), 'RESULT' => array('ID'), 'MULTI' => 'N')), array('ACTIVE' => 'Y', 'IBLOCK_ID' => $menuIblockId, 'DEPTH_LEVEL' => 1, 'UF_MEGA_MENU_LINK' => $catalogPageUrl), false, array('ID'), array('nTopCount' => 1));
					if($menuRootCatalogSectionId){
						$aMenuLinksExt = array(array('', $catalogPageUrl, array(), array('FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => 1, 'MEGA_MENU_CHILDS' => 1)));
					}
				}
			}
		}
	}

}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>