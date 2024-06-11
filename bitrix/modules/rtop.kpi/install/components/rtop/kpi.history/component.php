<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Rtop\KPI\Premission;

$module_id = "rtop.kpi";
if(!Loader::includeModule($module_id)){
	ShowError(GetMessage('KPI_MODULE_NOT_INSTALLED'));
	return;
}


$APPLICATION->SetTitle(Loc::getMessage("C_KPI_HISTORY_BROWSER_TITLE"));

$arResult = [
	'PREMISSION' => Premission::get()
];

$this->IncludeComponentTemplate();