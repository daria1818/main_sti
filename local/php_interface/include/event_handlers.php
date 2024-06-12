<?

use Bitrix\Main\Context;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Api\Classes\Entity\OrderTable;
use Bitrix\Main\Application;
use Pwd\Entity\CatalogTable;
use Pwd\Tools\Logger;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);


$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    "sale",
    "OnSaleBasketItemBeforeSaved",
    array(
        "EventHandlers",
        "OnSaleBasketItemBeforeSaved",
    )
);
$eventManager->addEventHandler(
    "sale",
    "OnSaleComponentOrderOneStepPaySystem",
    array(
        "EventHandlers",
        "OnSaleComponentOrderOneStepPaySystem",
    )
);
$eventManager->addEventHandler(
    "sale",
    "OnSaleComponentOrderOneStepPersonType",
    array(
        "EventHandlers",
        "OnSaleComponentOrderOneStepPersonType",
    )
);
$eventManager->addEventHandler( //06.07.2020
    "sale",
    "OnSaleComponentOrderProperties",
    array(
        "EventHandlers",
        "OnSaleComponentOrderProperties",
    )
);

$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    ['EventHandlers', 'preventOverwritingCatalogElementDescriptionDuring1CExchange']
);

$eventManager->addEventHandler(
    "main",
    "OnAfterUserAuthorize",
    array(
        "EventHandlers",
        "OnAfterUserAuthorizeHandler",
    )
);

$eventManager->addEventHandler( //10.08.2021
    "sale",
    "OnSaleComponentOrderResultPrepared",
    array(
        "EventHandlers",
        "OnSaleComponentOrderResultPreparedHandler",
    )
);

$eventManager->addEventHandler(
    'sale',
    'OnSaleOrderBeforeSaved',
    ['\\Api\\Classes\\OrderTools', 'saleOrderBeforeSavedHandler']
);

$eventManager->addEventHandler(
    'main',
    'OnPageStart',
    ['EventHandlers', 'onPageStartHandler']
);

$eventManager->addEventHandler(
    "main",
    "OnEndBufferContent",
    "OnEndBufferContentPageSpeed"
);

$eventManager->addEventHandler(
    "socialservices", 
    "OnBeforeSocServUserAuthorize",
    ["OnBeforeSocServUserAuthorizeHandler", "splitAccount"]
);
$eventManager->addEventHandler(
    "socialservices", 
    "OnUserLoginSocserv",
    ["OnBeforeSocServUserAuthorizeHandler", "authAccount"]
); 


class OnBeforeSocServUserAuthorizeHandler
{
    function splitAccount($event)
    {
        global $USER;
        $USER_ID = $USER->GetId();
        if($USER_ID){
            $entityOAuth = $event->getEntityOAuth();
            $arVkUser = $entityOAuth->GetCurrentUser();

            $profileUrl = $event->getProfileUrl($arVkUser['response']['0']['id']);
        
            $arGroups = CUser::GetUserGroup($USER_ID);
            $arGroups[] = 33;
            //$USER->SetUserGroup($arGroups);

            $arFields = array(
                "UF_VK_ID" => $profileUrl,
                "GROUP_ID" => $arGroups
            );
            $USER->Update($USER_ID, $arFields);
   
        }
    }

    function authAccount($event)
    {
        global $USER;

        if($event["USER_ID"]){
            $USER_ID = $event["USER_ID"];
        }else{
            $USER_ID = $USER->GetId();
        }

        if($event["PERSONAL_WWW"]){
            $url = $event["PERSONAL_WWW"];
        }else{
            $url = $event->getProfileUrl($arVkUser['response']['0']['id']);
        }

        $arGroups = CUser::GetUserGroup($USER_ID);
        $arGroups[] = 33;
        $arFields = array(
            "GROUP_ID" => $arGroups,
            "UF_VK_ID" => $url,
        );
        $USER->Update($USER_ID, $arFields);


    }
}


$eventManager->addEventHandler('documentgenerator', 'onBeforeProcessDocument', function(\Bitrix\Main\Event $event){

    $document = $event->getParameter('document');
    Logger::writeLog($document, '/local/php_interface/logs/', 'log');
});


function OnEndBufferContentPageSpeed(&$content)
{
    $PS = new PageSpeed();
    $PS->improvePageSpeed($content);
}

$eventManager->addEventHandler('sale', 'OnSaleStatusOrderChange', 'OnSaleStatusOrderChangeHandler');

function OnSaleStatusOrderChangeHandler($event)
{
    $parameters = $event->getParameters();
    $order = $parameters['ENTITY'];

    /*$logger = Logger::getLogger('OnSaleStatusOrderChange', 'OnSaleStatusOrderChange');
    $logger->log('$parameters = ');
    $logger->log($parameters);
    $logger->log('$order = ');
    $logger->log($order);
    $logger->log('STATUS = ');
    $logger->log($parameters['VALUE']);
    $logger->log('STATUS = ');
    $logger->log($order->getField('STATUS_ID'));*/

    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS
    );
}
/*$eventManager->addEventHandler('sale', 'onSaleOrderSaved', 'onSaleOrderSavedHandler');

function onSaleOrderSavedHandler($event)
{
    $logger = Logger::getLogger('onSaleOrderSaved', 'onSaleOrderSaved');
    $logger->log($event);
}*/

/*$eventManager->addEventHandler("sale", "OnOrderAdd", "OnOrderAddHandler");
function OnOrderAddHandler($orderID, $arFields)
{
    $logger = Logger::getLogger('OnOrderAdd', 'OnOrderAdd');
    $logger->log('orderID = ' . $orderID);
    $logger->log('fields = ');
    $logger->log($arFields);
}*/


$eventManager->addEventHandler("sale", "OnOrderUpdate", "OnOrderUpdateHandler");
function OnOrderUpdateHandler($orderID, $arFields)
{
    $logger = Logger::getLogger('OnOrderUpdate_test', 'OnOrderUpdate_test');
    $logger->log('orderID = ' . $orderID);
    $logger->log('fields = ');
    $logger->log($arFields);
}
/*$eventManager->addEventHandler("crm", "OnAfterCrmDealUpdate", "OnAfterCrmDealUpdateHandler");
function OnAfterCrmDealUpdateHandler(&$arFields)
{
    $logger = Logger::getLogger('OnOrderAdd_test', 'OnOrderAdd_test');
    $logger->log('fields = ');
    $logger->log($arFields);
}*/


$eventManager->addEventHandler("sale", "OnBeforeOrderUpdate", "OnBeforeOrderUpdateHandler");
function OnBeforeOrderUpdateHandler($orderID, &$arFields)
{
    // $order = \Bitrix\Sale\Order::load($orderID);
    // $arFields['PERSON_TYPE_ID'] = $order->getPersonTypeId();

    $logger = Logger::getLogger('OnBeforeOrderUpdate', 'OnBeforeOrderUpdate');
    $logger->log('orderID = ' . $orderID);
    $logger->log('fields = ');
    $logger->log($arFields);
}

$eventManager->addEventHandler("sale", "OnSaleStatusOrder", "OnSaleStatusOrderHandler");
function OnSaleStatusOrderHandler($orderID, $arFields)
{
    // $logger = Logger::getLogger('OnSaleStatusOrder', 'OnSaleStatusOrder');
    // $logger->log('orderID = ' . $orderID);
    // $logger->log('fields = ');
    // $logger->log($arFields);
}

$eventManager->addEventHandler("sale", "OnOrderSave", "OnOrderSaveHandler");
function OnOrderSaveHandler($orderID, $fields, $orderFields)
{
    /*$logger = Logger::getLogger('OnOrderSave', 'OnOrderSave');
    $logger->log('orderID = ' . $orderID);
    $logger->log('fields = ');
    $logger->log($fields);
    $logger->log('orderFields = ');
    $logger->log($orderFields);*/
}

$eventManager->addEventHandler(
    'sale',
    '\Bitrix\Sale\Internals\OrderTable::onBeforeUpdate',
    function (Bitrix\Main\Entity\Event $event) {
        /** @var \Bitrix\Main\Entity\EventResult $result */
        $result = new Bitrix\Main\Entity\EventResult();

//        $data = $event->getParameter('fields');

//        $logger = Logger::getLogger('OnBeforeUpdateD7', 'OnBeforeUpdateD7');
//        $logger->log('fields = ');
//        $logger->log($data);

        return $result;
    }
);

$eventManager->addEventHandler(
    'sale',
    '\Bitrix\Sale\Internals\OrderTable::onUpdate',
    function (Bitrix\Main\Entity\Event $event) {
        /** @var \Bitrix\Main\Entity\EventResult $result */
        $result = new Bitrix\Main\Entity\EventResult();

//        $data = $event->getParameter('fields');

//        $logger = Logger::getLogger('OnUpdateD7', 'OnUpdateD7');
//        $logger->log('fields = ');
//        $logger->log($data);

        return $result;
    }
);

$eventManager->addEventHandler("sale", "OnOrderNewSendEmail", "bxModifySaleMails");
function bxModifySaleMails($orderID, &$eventName, &$arFields)
{

    $arOrder = CSaleOrder::GetByID($orderID);

    //mail("erynrandir@yandex.ru", "OnBeforeUserAddHandler", "{$arFields['LOGIN']} / {$arFields['PASSWORD']}");
    mail("erynrandir@yandex.ru", "OnBeforeUserAddHandler", "bxModifySaleMails");

}

$eventManager->addEventHandler('iml.v1', 'onCalculate', 'changeIMLTerms');

function changeIMLTerms(&$arReturn, $profile, $config, $arOrder)
{


    /*
        здесь задаются условия в зависимости от значений параметров:
        $profile - профиль
        $arConfig - настройки СД
        $arOrder - параметры заказа
        $arResult - массив вида
            RESULT - OK, если рассчет верен, ERROR - если ошибка
            VALUE - стоимость доставки в рублях
            TRANSIT - срок доставки в днях

        !Не забудьте, что $arResult - указатель на массив
    */


    //$weight = 2; // ?

    //print_r($arOrder); die('changeIMLTerms');

    //$arOrder['WEIGHT'] = 2;

    // !! IML минимальный вес 2кг?
    //if( $arOrder['WEIGHT'] < 2) {
    //$arReturn['WEIGHT'] = 2;
    //}

    // debug - стоимость доставки меняется
    //$arReturn['VALUE'] = $arOrder['WEIGHT']; //999;

}

/**
 * Class PageSpeed
 */
class PageSpeed
{
    private $improveCss;
    private $improveJs;
    private $improveHtml;
    private $improveLazyLoad;
    private $improveWebp;

    public function __construct()
    {
        $this->improveCss = true;
        $this->improveJs = false;
        $this->improveHtml = true;
        $this->improveLazyLoad = true;
        $this->improveWebp = true;
    }

    public function improvePageSpeed(&$content = '')
    {
        if ($this->useImprove()) {
            $this->improveCss($content);
            $this->improveJs($content);
            $this->improveHtml($content);
            $this->improveLazyLoad($content);
            $this->improveWebp($content);
        }
    }

    protected function improveCss(&$content = '')
    {
        if ($this->improveCss) {
            preg_match_all('#<link(.*?)/>#is', $content, $css, PREG_SET_ORDER);
            if ($css) {
                $textCss = '';
                foreach ($css as $c) {
                    if (strpos($c[1], 'text/css') !== false) {
                        preg_match('/href=["\']?([^"\'>]+)["\']?/', $c[1], $url);
                        if ($url[1] && substr($url[1], 0, 1) == '/' && strpos($url[1], '.ico') === false) {
                            $exp = explode('?', $_SERVER['DOCUMENT_ROOT'] . $url[1]);
                            $text = file_get_contents($exp[0]);
                            if ($text) {
                                $textCss .= $text;
                                $content = str_replace($c[0], '', $content);
                            }
                        }
                    }
                }
                if ($textCss) {
                    $textCss = $this->minifyCss($textCss);
                    $textCss = str_replace('../images/panel/top-panel-sprite-2.png', '/bitrix/js/main/core/images/panel/top-panel-sprite-2.png', $textCss);
                }
                $exp = explode('</head>', $content);
                $content = $exp[0] . '<style>' . $textCss . '</style></head>' . $exp[1];
            }
        }
    }

    protected function improveJs(&$content = '')
    {
        if ($this->improveJs) {
            preg_match_all('#<script(.*?)</script>#is', $content, $js, PREG_SET_ORDER);
            if ($js) {
                $textJs = '';
                foreach ($js as $j) {
                    if (strpos($j[1], 'src="') !== false) {
                        preg_match('/src=["\']?([^"\'>]+)["\']?/', $j[1], $url);
                        if ($url[1] && substr($url[1], 0, 1) == '/') {
                            $exp = explode('?', $url[1]);
                            if (in_array($exp[0], [
                                '/bitrix/js/main/admin_tools.js',
                                '/bitrix/js/main/public_tools.min.js'
                            ]))
                                continue;
                            $text = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $exp[0]);

                            if ($text) {
                                $textJs .= $text;
                                $textJs .= "\n";
                                $content = str_replace($j[0], '', $content);
                            }
                        }
                    } else {
                        preg_match('#<script(.*?)>(.*?)</script>#is', $j[0], $code);

                        if (strpos($code[2], 'm,e,t,r,i,k,a') !== false) {
                            continue;
                        }

                        if (strpos($code[2], 'googletagmanager') !== false)
                            continue;

                        if (strpos($code[2], "gtag('config'") !== false)
                            continue;

                        if (strpos($code[2], "var __cs = __cs || [];") !== false)
                            continue;

                        $textJs .= $code[2];
                        $textJs .= "\n";
                        $content = str_replace($j[0], '', $content);
                    }
                }
                $exp = explode('</body>', $content);
                $content = $exp[0] . '<script defer>' . $textJs . '</script></body>' . $exp[1];
            }
        }
    }

    protected function improveHtml(&$content = '')
    {
        if ($this->improveHtml) {
            $content = str_replace("\t\n", "\n", $content);
            $content = preg_replace('~>\s*\n\s*<~', '><', $content);
        }
    }

    protected function improveLazyLoad(&$content = '')
    {
        if ($this->improveLazyLoad) {
            $content = str_replace("<img ", "<img loading=\"lazy\" ", $content);
        }
    }

    protected function improveWebp(&$content = '')
    {
        if ($this->improveWebp) {
            if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') === false || strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) && strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') === false && function_exists('imagewebp')) {
                preg_match_all('/<img[^>]+>/i', $content, $img);
                if ($img[0]) {
                    foreach ($img[0] as $i => $v) {
                        preg_match_all('/src="([^"]+)/i', $v, $attr);
                        if ($attr[1][0] && strpos($attr[1][0], '.webp') === false) {
                            $path = str_replace(['.png', '.jpeg', '.jpg'], '.webp', $attr[1][0]);
                            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                /*    if (strpos($attr[1][0], '.png')) {
                                        $newImg = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $attr[1][0]);
                                        imagealphablending($newImg, false);
                                        imagesavealpha($newImg, true);
                                        $newImgPath = str_replace('.png', '.webp', $attr[1][0]);
                                    } elseif (strpos($attr[1][0], '.jpg') !== false || strpos($attr[1][0], '.jpeg') !== false) {
                                        $newImg = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $attr[1][0]);
                                        $newImgPath = str_replace(array('.jpg', '.jpeg'), '.webp', $attr[1][0]);
                                    }
                                    if ($newImg) {
                                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $newImgPath)) {
                                            imagewebp($newImg, $_SERVER['DOCUMENT_ROOT'] . $newImgPath, 90);
                                        }
                                        imagedestroy($newImg);
                                    }
    */
                            } else {
                                $content = str_replace('src="' . $attr[1][0] . '"', 'src="' . $path . '"', $content);
                            }
                        }
                    }
                }
                preg_match_all('/background:url\("([^"]+)/i', $content, $img);
                if ($img[1]) {
                    $img[1] = array_unique($img[1]);
                    foreach ($img[1] as $i => $v) {
                        if (strpos($v, '.png') !== false || strpos($v, '.jpg') !== false || strpos($v, '.jpeg') !== false) {
                            $path = str_replace(['.png', '.jpeg', '.jpg'], '.webp', $v);
                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                $content = str_replace($v, $path, $content);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function minifyCss($css = '')
    {
        $css = trim($css);
        $css = str_replace("\r\n", "\n", $css);
        $search = array("/\/\*[^!][\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
        $replace = array(null, " ", "}\n");
        $css = preg_replace($search, $replace, $css);
        $search = array("/;[\s+]/", "/[\s+];/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i", "/\{\\s+/", "/;}/");
        $replace = array(";", ";", "{", ":#", ",", ":\'", ":$1", "{", "}");
        $css = preg_replace($search, $replace, $css);
        $css = str_replace("\n", null, $css);
        return $css;
    }

    protected function useImprove()
    {
        global $USER;
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        return (!$request->isAdminSection() && !$request->isAjaxRequest() && !CSite::InDir('/order/') /*&& !$USER->isAdmin()*/);
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}

class EventHandlers
{
    const IB_CATALOG = 'CRM_PRODUCT_CATALOG';
    const SPETSTOVAR = 'промарт';
    public static $arParamsSpetstovar = array(
        960 => array( // enum_id элементов списочного сво-ва SPETSTOVAR  "ПромАрт"
            "PERSON_TYPE" => array(
                "bx_5e09d15650830", // юр лицо XML_ID
            ),
            "PAY_SYSTEM" => array(
                "bx_5d712f28ae926", // Яндекс касса
            ),
        ),
        957 => array( // enum_id элементов списочного сво-ва SPETSTOVAR  "Лекарственное средство"
            "PERSON_TYPE" => array(
                "bx_5e09d16c8246b", // физ лицо XML_ID
                "bx_5e09d15650830", // юр лицо XML_ID
            ),
            "PAY_SYSTEM" => array(
                "bx_5ebb9967d5bbb", // Выставление счета менеджером XML_ID
            ),
            "TEXT_TO_BASKET" => 'UF_TEXT_TO_BASKET_FIRST',
        ),
        958 => array( // enum_id элементов списочного сво-ва SPETSTOVAR  "Прекурсор (бутандиол)"
            "PERSON_TYPE" => array(
                "bx_5e09d16c8246b", // физ лицо XML_ID
                "bx_5e09d15650830", // юр лицо XML_ID
            ),
            "PAY_SYSTEM" => array(
                "bx_5ebb9967d5bbb", // Выставление счета менеджером XML_ID
            ),
            "TEXT_TO_BASKET" => 'UF_TEXT_TO_BASKET_SECOND',
        ),
        959 => array( // enum_id элементов списочного сво-ва SPETSTOVAR  "Прекурсор (метилметакрилат)"
            "PERSON_TYPE" => array(
                "bx_5e09d16c8246b", // физ лицо XML_ID
                "bx_5e09d15650830", // юр лицо XML_ID
            ),
            "PAY_SYSTEM" => array(
                "bx_5ebb9967d5bbb", // Выставление счета менеджером XML_ID
            ),
            "TEXT_TO_BASKET" => 'UF_TEXT_TO_BASKET_SECOND',
        ),
    );
    private static $arData = array();

    public static function getBasketItem($addItemToBasket = false)
    {
        $arResult = array();
        $basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
        $basketItems = $arResult["BASKET_ITEMS"] = $basket->getBasketItems();

        $arIdProduct = array();
        if ($addItemToBasket) {
            $arIdProduct[] = $addItemToBasket;
        }
        if ($basketItems) {
            foreach ($basketItems as $basketItem) {
                $arIdProduct[] = $basketItem->getProductId();
            }
        }
        if ($arIdProduct) {
            $arOrder = array();
            $arFilter = array("IBLOCK_CODE" => self::IB_CATALOG, "ID" => $arIdProduct);
            $arSelect = array("ID", "PROPERTY_SPETSTOVAR");
            $dbResult = CIBlockElement::GetList(
                $arOrder,
                $arFilter,
                false,
                false,
                $arSelect
            );
            $spetstovarItem = '';
            $spetstovarBasket = array();
            while ($result = $dbResult->Fetch()) {
                $arResult["ITEMS"][] = $result;
            }
        }
        return $arResult;
    }

    public static function OnSaleBasketItemBeforeSaved(Bitrix\Main\Event $event)
    {
        $item = $event->getParameter("ENTITY");
        $isNew = $event->getParameter("IS_NEW");
        if ($isNew) {
            if ($arResult = self::getBasketItem($item->getProductId())) {
                $spetstovarItem = '';
                $spetstovarBasket = array();
                foreach ($arResult["ITEMS"] as $key => $value) {
                    if ($item->getProductId() == $value["ID"]) {
                        $spetstovarItem = ToLower($value["PROPERTY_SPETSTOVAR_VALUE"]);
                    } else {
                        $spetstovarBasket[] = ToLower($value["PROPERTY_SPETSTOVAR_VALUE"]);
                    }
                }
                if (($spetstovarItem != self::SPETSTOVAR && !empty($spetstovarBasket) && in_array(self::SPETSTOVAR, $spetstovarBasket)) ||
                    ($spetstovarItem == self::SPETSTOVAR && !empty($spetstovarBasket) && !in_array(self::SPETSTOVAR, $spetstovarBasket))
                ) {
                    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(\COption::GetOptionString("askaron.settings", "UF_EVENT_RESULT_POPUP_ERROR") . Loc::getMessage('EVENT_RESULT_POPUP_ERROR_BUTTON'), 'code'), 'sale');
                }
            }
        }
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
    }

    public static function OnSaleComponentOrderOneStepPersonType(&$arResult, &$request = false, $arParams = false)
    {
        if (!empty(self::$arData) || self::$arData = self::getBasketItem()) {
            foreach (self::$arData["ITEMS"] as $key => $value) {
                $code = $value["PROPERTY_SPETSTOVAR_ENUM_ID"];
                if (self::$arParamsSpetstovar[$code]) {
                    $arPersonType = array();
                    if (self::$arParamsSpetstovar[$code]["PERSON_TYPE"]) {
                        $i = 1;
                        $arPaySystem = array();
                        $checked = false;
                        $idChecked = 0;
                        foreach ($arResult["PERSON_TYPE"] as $key => $value) {
                            if (in_array($value["XML_ID"], self::$arParamsSpetstovar[$code]["PERSON_TYPE"])) {
                                if ($value["CHECKED"]) {
                                    $checked = true;
                                    $idChecked = $value["ID"];
                                }
                                $arPersonType[] = $value;
                            }
                        }
                        if ($arPersonType) {
                            if ($checked == false) {
                                $arPersonType[0]["CHECKED"] = "Y";
                                $request["PERSON_TYPE_ID"] = $arPersonType[0]["ID"];
                            } elseif ($checked == true && $idChecked > 0) {
                                $request["PERSON_TYPE_ID"] = $idChecked;
                            }
                        }
                    }
                    if (!empty($arPersonType)) {
                        $arResult["PERSON_TYPE"] = $arPersonType;
                    }
                    break;
                }
            }
        }
    }

    public static function OnSaleComponentOrderOneStepPaySystem(&$arResult, $request = false, $arParams = false)
    {
        if (!empty(self::$arData) || self::$arData = self::getBasketItem()) {
            $arPaySystem = array();
            foreach (self::$arData["ITEMS"] as $key => $value) {
                $code = $value["PROPERTY_SPETSTOVAR_ENUM_ID"];
                if (self::$arParamsSpetstovar[$code]) {
                    if (self::$arParamsSpetstovar[$code]["PAY_SYSTEM"]) {
                        $checked = false;
                        foreach ($arResult["PAY_SYSTEM"] as $key => $value) {
                            if (in_array($value["XML_ID"], self::$arParamsSpetstovar[$code]["PAY_SYSTEM"])) {
                                if ($value["CHECKED"]) {
                                    $checked = true;
                                }
                                $arPaySystem[] = $value;
                            }
                        }
                        if ($checked == false) {
                            $arPaySystem[0]["CHECKED"] = "Y";
                        }
                        if (!empty($arPaySystem)) {
                            $arResult["PAY_SYSTEM"] = $arPaySystem;
                        }
                        break;
                    }
                }
            }
            if (empty($arPaySystem)) {
                foreach ($arResult["PAY_SYSTEM"] as $key => $value) {
                    if ($value["XML_ID"] == 'bx_5ebb9967d5bbb') {
                        array_splice($arResult["PAY_SYSTEM"], $key, 1);
                    }
                }
            }
        }
    }

    public static function OnSaleComponentOrderProperties(&$arUserResult, $request, &$arParams, &$arResult) //06.07.2020
    {
        $SAME_AS_SHIPPING_ADDRESS = 106; //Совпадает с адресом доставки
        $ADDRESS_REGISTER = 9; //Юридический адрес
        $DELIVERY_ADDRESS = 19; //Адрес доставки

        if ($arUserResult["ORDER_PROP"][$SAME_AS_SHIPPING_ADDRESS] == "Y") {
            $arUserResult["ORDER_PROP"][$DELIVERY_ADDRESS] = $arUserResult["ORDER_PROP"][$ADDRESS_REGISTER];
        }
    }

    public static function OnSaleComponentOrderResultPreparedHandler($order, &$arUserResult, $request, &$arParams, &$arResult)
    {
        foreach($arResult['JS_DATA']['DELIVERY'] as &$delivery) {
            if(!empty($delivery['EXTRA_SERVICES']) && $delivery['ID'] == 14){
                $select = $delivery['EXTRA_SERVICES'][0]['editControl'];
                $delivery['EXTRA_SERVICES'][0]['editControl'] = str_replace("(0 руб.)", "", $select);
            }
        }
    }

    public static function preventOverwritingCatalogElementDescriptionDuring1CExchange(&$elementFields)
    {
        if (!isset($elementFields['PREVIEW_TEXT']) && !isset($elementFields['DETAIL_TEXT'])) {
            return;
        }

        $requestUri = new Uri(Context::getCurrent()->getServer()->getRequestUri());
        $requestPath = $requestUri->getPath();

//        Debug::dumpToFile($requestPath, '$requestPath', '__iblock_element_update_log.txt');

        if ($requestPath === '/bitrix/admin/1c_exchange.php') {
            unset(
                $elementFields['PREVIEW_TEXT'],
                $elementFields['PREVIEW_TEXT_TYPE'],
                $elementFields['DETAIL_TEXT'],
                $elementFields['DETAIL_TEXT_TYPE']
            );
        }
    }

    /**
     * обработка события после залогинивания пользователя
     * @param array $arUser
     */
    public static function OnAfterUserAuthorizeHandler($arUser)
    {
        $arUserOrders = OrderTable::getList([
            'select' => ['ID', 'USER_ID'],
            'filter' => ['USER_ID' => $arUser['user_fields']['ID']]
        ])->fetchAll();

        if ($arUserOrders) {
            // заказы есть
            $_SESSION['CHECK_USERS_ORDERS'] = 'Old';
        } else {
            // заказов нет
            $_SESSION['CHECK_USERS_ORDERS'] = 'New';
        }
    }

    /**
     * обработчик события на старт страницы
     */
    public static function onPageStartHandler()
    {
        $arRequest = Application::getInstance()->getContext()->getRequest()->toArray();
        // запоминаем utm метки в сессию
        foreach ($arRequest as $sKey => $mVal) {
            if (strpos($sKey, 'utm_') === 0) {
                $_SESSION[$sKey] = $mVal;
            }
        }
    }
}

$eventManager->addEventHandlerCompatible('search', 'BeforeIndex',    ['\\CatalogProductIndexer','handleBeforeIndex']);

class CatalogProductIndexer
{
    /**
     * @var int Идентификатор инфоблока каталога
     */

    /**
     * Дополняет индексируемый массив нужными значениями
     * подписан на событие BeforeIndex модуля search
     * @param array $arFields
     * @return array
     */
    public static function handleBeforeIndex( $arFields = [] )
    {
        if ( !static::isInetesting( $arFields ) )
        {
            return $arFields;
        }

        /**
         * @var array Массив полей элемента, которые нас интересуют
         */
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PROPERTY_CML2_ARTICLE',
        ];

        /**
         * @var CIblockResult Массив описывающий индексируемый элемент
         */
        $resElements = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => $arFields['PARAM2'],
                'ID'        => $arFields['ITEM_ID']
            ],
            false,
            [
                'nTopCount'=>1
            ],
            $arSelect
        );

        /**
         * В случае, если элемент найден мы добавляем нужные поля
         * в соответсвующие столбцы поиска
         */
        if ( $arElement = $resElements->fetch() )
        {
            $arFields['TITLE'] .= ' '.$arElement['PROPERTY_CML2_ARTICLE_VALUE'];
        }

        return $arFields;
    }

    /**
     * Возвращает true, если это интересующий нас элемент
     * @param array $fields
     * @return boolean
     */
    public static function isInetesting( $fields = [] )
    {
        return ( $fields["MODULE_ID"] == "iblock" && $fields['PARAM2'] == CatalogTable::getIblockId() );
    }

}



$eventManager->addEventHandler('main', 'OnBeforeEventSend', "my_OnBeforeEventSend");

function my_OnBeforeEventSend(&$arFields, $arTemplate){

    if($arFields["EVENT_NAME"]=="CATALOG_PRODUCT_SUBSCRIBE_NOTIFY"){
        if(CModule::IncludeModule('iblock')){

            $arSelect = Array("ID", "DETAIL_PICTURE", "DETAIL_PAGE_URL");
            $arFilter = Array("IBLOCK_ID"=>IntVal($arFields["IBLOCK_ID"]), "ID"=>IntVal($arFields["ITEM_ID"]));
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

            while($ob = $res->GetNextElement()){
                $arFields2[] = $ob->GetFields();
            }

            switch ($arFields["SITE_ID"]) {
                case 's1':
                    $arFields["PAGE_URL"] = "https://stionline.ru".$arFields2[0]["DETAIL_PAGE_URL"]."?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    $arFields["CATALOG_URL"] = "https://stionline.ru/catalog/?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    break;

                case 's2':
                    $arFields["PAGE_URL"] = "https://krd.stionline.ru".$arFields2[0]["DETAIL_PAGE_URL"]."?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    $arFields["CATALOG_URL"] = "https://krd.stionline.ru/catalog/?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    break;

                case 's3':
                    $arFields["PAGE_URL"] = "https://stionline.ru/stavropol".$arFields2[0]["DETAIL_PAGE_URL"]."?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    $arFields["CATALOG_URL"] = "https://stionline.ru/stavropol/catalog/?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    break;

                case 's4':
                    $arFields["PAGE_URL"] = "https://krd.stionline.ru/novorossiisk".$arFields2[0]["DETAIL_PAGE_URL"]."?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    $arFields["CATALOG_URL"] = "https://krd.stionline.ru/novorossiisk/catalog/?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    break;

                default:
                    $arFields["PAGE_URL"] = "https://stionline.ru".$arFields2[0]["DETAIL_PAGE_URL"]."?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    $arFields["CATALOG_URL"] = "https://stionline.ru/catalog/?utm_source=shop&utm_medium=mail&utm_campaign=preorder";
                    break;
            }

            $arFields["IMG_URL"] = CFile::GetPath($arFields2[0]["DETAIL_PICTURE"]);

            $arService[] = $arFields;
            $arService[] = $arTemplate;

            $str = json_encode($arService);
            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/log_tr123_2.txt", $str);
        }
    }

}

function vkparse()
{
    if(!CModule::IncludeModule('rubyroid.bonusloyalty'))
        return "vkparse();";

    $date = date("Y-m-d");
    file_get_contents("http://admin:123456789@vk.stident.ru/export?start_date=".$date);
    $parse_array = file_get_contents("http://admin:123456789@vk.stident.ru/files/export/main.csv");
    $parse_array = explode("\n", $parse_array);
    foreach ($parse_array as $parse_user_fields)
    {
        $arParseUsers[] = explode(";",$parse_user_fields); 
    }

    $Filter = array("!UF_VK_ID" => false);
    $order = array('sort' => 'asc');
    $tmp = 'sort';
    $rsUsers = CUser::GetList(
        $order, 
        $tmp, 
        $Filter,
        array("SELECT"=>array("UF_VK_ID","UF_LOYALTY_COIN","UF_PREV_VK_LIKE"),"FIELDS"=>array("ID"))
    );

    while($arBXUser = $rsUsers->NavNext())
    {
        $BX_VK_ID =$arBXUser['UF_VK_ID'];
        $arBXUsers[$BX_VK_ID] = $arBXUser; 
    }

    $ratio_like =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_like')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_like');
    $ratio_coment =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_coment')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_coment');
    $ratio_repost =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_repost')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_repost');

    $logger = Logger::getLogger('vkparse', 'vkparse');
    $logger->log([$ratio_like, $ratio_coment, $ratio_repost]);

    foreach ($arParseUsers as $ParseUser)
    {
        if (array_key_exists($ParseUser[1], $arBXUsers))
        {
            $SelectBXuser = $arBXUsers[$ParseUser[1]];
            $user_coin = ($ParseUser[2]*$ratio_repost) + ($ParseUser[3]*$ratio_coment);
            $user_likes = $ParseUser[4] - $SelectBXuser['UF_PREV_VK_LIKE'];
            $user_likes1 = $user_likes <= 0 ? 0 : $user_likes * $ratio_like;

            $user = new CUser;
            $UFields = array(
                "UF_LOYALTY_COIN" => $user_coin + $SelectBXuser["UF_LOYALTY_COIN"] + $user_likes1,
                "UF_PREV_VK_LIKE" => $user_likes + $SelectBXuser['UF_PREV_VK_LIKE'],
            );
            //$user->Update($SelectBXuser["ID"], $UFields);
            if($user->LAST_ERROR)
            {
                $strError .= $user->LAST_ERROR;
            }
            else
            {
                $arPoints = array();
                if($ParseUser[2])
                {
                    $arPoints["REPOST"] = $ParseUser[2] * $ratio_repost;
                }
                if($ParseUser[3])
                {
                    $arPoints["COMMENT"] = $ParseUser[3] * $ratio_coment;
                }
                if($ParseUser[4])
                {
                    $arPoints["LIKE"] = $user_likes1;
                }
                if(count($arPoints) > 0)
                {
                    foreach($arPoints as $event => $point)
                    {
                        /*RBTransactions::bonus([
                            "TYPE_EVENT" => $event,
                            "COIN" =>  $point,
                            "USER_ID" => $SelectBXuser["ID"]
                        ]);*/
                    }
                }
            }
        }
    }

    return "vkparse();";
}
