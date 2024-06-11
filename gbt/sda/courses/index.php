<?php
define("NEED_AUTH", true);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $APPLICATION;
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
$APPLICATION->SetTitle("Календарь курсов");
?>

<?$APPLICATION->IncludeComponent(
    "ses:calendar.manager", 
    "list", 
    array(
        "COMPONENT_TEMPLATE" => "list",
        "SELECTION_DAYS" => "temp2",
    ),
    false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>