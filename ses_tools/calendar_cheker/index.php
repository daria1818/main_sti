<?php
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
global $APPLICATION;

?>

<?$APPLICATION->IncludeComponent(
    "ses:calendar.manager", 
    "", 
    array(
        "COMPONENT_TEMPLATE" => ".default",
    ),
    false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>