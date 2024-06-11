<?
use \Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
Loc::loadMessages(__FILE__);

$module_id = "rtop.kpi";

$arClassesList = [
    "Rtop\\KPI\\Admin\\Menu" => "admin/menu.php",
];

Loader::registerAutoLoadClasses($module_id, $arClassesList);

//$eventManager = \Bitrix\Main\EventManager::getInstance();

// $ar_events[] = array(
//     "module" => 'main',
//     "NAME" => "OnEpilog",
//     "FUNCTION" => "OnEpilog"
// );

// $ar_events[] = array(
//     "module" => 'crm',
//     "NAME" => "OnAfterCrmContactAdd",
//     "FUNCTION" => "OnAfterCrmContactAddHandler"
// );

// foreach ($ar_events as $event) {    
//     $eventManager->registerEventHandler($event['module'], $event['NAME'], $module_id, '\\Rtop\\KPI\\EventManager', $event['FUNCTION']);
// }