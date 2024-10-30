<?php
define("NEED_AUTH", true);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $APPLICATION, $USER;

// if ($USER->IsAdmin()) {
    $APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
    $APPLICATION->SetTitle("Календарь курсов");
    ?>

    <?$APPLICATION->IncludeComponent(
        "ses:calendar.manager",
        "",
        array(
            "COMPONENT_TEMPLATE" => ".default",
            "SELECTION_DAYS" => "temp1",
            "CALENDAR_TYPE" => array('SDA'),
            "FILTER" => array(
                "UF_TYPE" => array(244, 245, 252),
                "UF_ROLE" => array("Лектор"),
            ),
        ),
        false
    );?>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
