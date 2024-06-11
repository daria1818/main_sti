<?
define("BX_SKIP_USER_LIMIT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("BX_SENDPULL_COUNTER_QUEUE_DISABLE", true);

if(
	!isset($_REQUEST['sessid'])
	&& !isset($_REQUEST['livechat_auth_id'])
	&& !isset($_REQUEST['call_auth_id'])
)
{
	define('BX_SECURITY_SESSION_VIRTUAL', true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/rest.log';
file_put_contents($logFile, PHP_EOL . '------------------------------------------------' . PHP_EOL, FILE_APPEND | LOCK_EX);
file_put_contents($logFile, 'Запрос' . PHP_EOL, FILE_APPEND | LOCK_EX);
$requestCopy = $_REQUEST;
if (isset($requestCopy['cmd']) && is_array($requestCopy['cmd'])) {
    foreach ($requestCopy['cmd'] as $key => $value) {
        parse_str($value, $parsedValue);
        $requestCopy['cmd'][$key] = $parsedValue;
    }
}
file_put_contents($logFile, json_encode($requestCopy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

ob_start();

file_put_contents($logFile, 'Ответ' . PHP_EOL, FILE_APPEND | LOCK_EX);

$APPLICATION->IncludeComponent(
    "bitrix:rest.provider", 
    "", 
    array(
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/rest/",
        "SEF_URL_TEMPLATES" => array(
            "path" => "#method#",
        )
    ),
    false,
    array('HIDE_ICONS' => 'Y')
);

$out1 = ob_get_clean();
file_put_contents($logFile, json_encode($out1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

// $APPLICATION->IncludeComponent("bitrix:rest.provider", "", array(
// 	"SEF_MODE" => "Y",
// 	"SEF_FOLDER" => "/rest/",
// 	"SEF_URL_TEMPLATES" => array(
// 		"path" => "#method#",
// 	)
// 	),
// 	false,
// 	array('HIDE_ICONS' => 'Y')
// );

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>