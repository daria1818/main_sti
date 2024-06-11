<?php
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("iblock")) {
    echo json_encode(['status' => 'error', 'message' => 'Модуль Инфоблоков не загружен']);
    return;
}

global $APPLICATION;

// Функция для проверки и фильтрации входящих данных
function getPostValue($key, $default = null) {
    return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key])) : $default;
}

$monthId = getPostValue('month');
$yearId = getPostValue('year');
$lectorId = getPostValue('lector');
$cityId = getPostValue('city');

try {
    $componentParams = [];
    // Проверка обязательных параметров month и year
    if (!is_null($monthId) && !is_null($yearId)) {
        $componentParams["MONTH_ID"] = $monthId;
        $componentParams["YEAR_ID"] = $yearId;
        $componentParams["SELECTION_DAYS"] = "temp1";
    } else {
        $componentParams["SELECTION_DAYS"] = "temp2";
        $componentParams["IS_AJAX"] = true;
    }

    if (!is_null($lectorId) && $lectorId !== 'all') {
        $componentParams["LECTOR_ID"] = $lectorId;
    }
    if (!is_null($cityId)) {
        $componentParams["CITY_ID"] = $cityId;
    }

    $APPLICATION->IncludeComponent(
        "ses:calendar.manager", 
        ".default", 
        $componentParams
    );
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
    return;
}
