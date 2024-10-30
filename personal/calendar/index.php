<?php
define("NEED_AUTH", true);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $APPLICATION;
$APPLICATION->SetTitle("Календарь учебных курсов центра STI Dent");
?>

<?$APPLICATION->IncludeComponent(
    "ses:calendar.manager", 
    "PC_STIDent", 
    array(
        "COMPONENT_TEMPLATE" => "PC_STIDent",
        "SELECTION_DAYS" => "temp1",
        "CALENDAR_TYPE" => array('DENT'),
        "FILTER" => array(
                        "UF_TYPE" => array(267,268,269,270,271,272,273,274,275),
                        "UF_ROLE" => array("Лектор STIDent"),
                        "UF_THIS_LOC_DENT" => 1,
                    ),
    ),
    false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>