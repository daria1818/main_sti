<?php
define("NEED_AUTH", true);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/plugins/nouislider/nouislider.min.css');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/nouislider/nouislider.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/inputmask.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/just-validate.min.js');

global $APPLICATION;
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
$APPLICATION->SetTitle("Курсы учебного центра S.T.I. Dent для стоматологов");
$APPLICATION->AddChainItem("Курсы учебного центра S.T.I. Dent для стоматологов");
?>

<?$APPLICATION->IncludeComponent(
    "ses:calendar.manager", 
    "STIDent", 
    array(
        "COMPONENT_TEMPLATE" => "STIDent",
        "CALENDAR_TYPE" => array('DENT'),
        "SELECTION_DAYS" => "temp2",
        "FILTER" => array(
            "UF_TYPE" => array(267,268,269,270,271,272,273,274,275),
            "UF_ROLE" => array("Лектор STIDent"),
            "UF_THIS_LOC_DENT" => 1,
        ),
    ),
    false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>