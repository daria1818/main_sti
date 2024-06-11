<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!empty($arParams['PARENT_SECTION']))
{
	$city = CIBlockSection::GetList([], ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arParams['PARENT_SECTION']], false, ['ID', 'UF_*'])->Fetch();
	if(!empty($city['UF_COORDS']))
	{
		$arResult['COORDS'] = explode(',', $city['UF_COORDS']);
	}
}