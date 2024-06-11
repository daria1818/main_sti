<?

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Pwd\Tools\Logger;
use Rubyroid\Loyality\RBTransactions;

use function Sentry\init;

require_once __DIR__ . '/include/constants.php';
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/api/include.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/spaceonfire/bitrix-tools/resources/autoload.php';
require_once 'include/event_handlers.php'; // Файл с функциями
require_once($_SERVER["DOCUMENT_ROOT"] . "/debug/vendor/autoload.php");
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/core_overrides.php';

//echo $PATH = __DIR__ .'/logs/';
Logger::$PATH = __DIR__ .'/logs/';

define('SITE_ID_CUSTOM', 's1');
define('DOCUMENT_ROOT_CUSTOM', $_SERVER["DOCUMENT_ROOT"]);


if (class_exists('Dotenv\\Dotenv')) {
    $env = Dotenv\Dotenv::createImmutable(dirname($_SERVER['DOCUMENT_ROOT']));
    // Если на проекте используется другое имя файла, его можно задать вторым параметром
    // пример, $env = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT'], '.environment');
    try {
        $env->load();


        init([
                'dsn' => getenv('SENTRY_DSN'),
                'environment' => getenv('APP_ENV'),
            ]);

    } catch (InvalidFileException | InvalidPathException $e) {
    }
}

Loader::registerAutoLoadClasses($module = null, [
    'Api\\Classes\\Entity\\OrderTable' => '/local/php_interface/api/classes/entity/OrderTable.php', //Класс заказов
    'Api\\Classes\\OrderTools' => '/local/php_interface/api/classes/OrderTools.php', //Класс для работы с заказами
    'RtopTypeEventTable' => '/local/php_interface/api/classes/RtopTypeEvent.php',
    'RtopSaleActionGift' => '/local/php_interface/api/classes/RtopSaleActionGift.php',
    'RtopSaleFreeDelivery' => '/local/php_interface/api/classes/RtopSaleFreeDelivery.php',
	'Logger' => '/local/php_interface/classes/Logger.php',
    'Debug' => '/local/php_interface/classes/Debug.php',
]);

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandlerCompatible(
    "sale",
    "OnCondSaleControlBuildList",
    ["RtopSaleFreeDelivery", "GetControlDescr"]
);

if (!function_exists("pre")) {
    function pre($var, $die = false, $all = false)
    {
        global $USER;
        if ($USER->IsAdmin() || $all == true) {
            ?><?
            mb_internal_encoding('utf-8'); ?>

            <font style="text-align: left; font-size: 12px">
                <pre><? print_r($var) ?></pre>
            </font><br>
            <?
        }
        if ($die) {
            die;
        }
    }
}

function get_SOAP()
{

    $config = new matejsvajger\NTLMSoap\Common\NTLMConfig([
        'domain' => 'dentlmen-server',
        'username' => 'im_user',
        'password' => 'Ckj;ysqGfhjkm'
    ]);

    $client = new matejsvajger\NTLMSoap\Client("http://91.242.161.153/dentlmenTEST/ws/DtmWS/?wsdl", $config);

    $soap_func = print_r($client->__getFunctions(), true);
    debugfile($soap_func, '1c.log');

    //print_r($client->__getTypes());

    return $client;

}

if (!function_exists('domovoyOnBeforeUserRegisterCheck')):
    function domovoyOnBeforeUserRegisterCheck(&$arFields)
    {
        global $APPLICATION;

        $countReplaceName = $countReplaceLastName = $countReplaceSecondName = 0;

        $APPLICATION->ResetException();

        $arFields['LOGIN'] = $arFields['EMAIL'];
        $arFields['~NAME'] = trim(preg_replace('/[^а-я^\s^\-]+/iu', '', $arFields['NAME'], -1, $countReplaceName));
        $arFields['NAME'] = mb_convert_case($arFields['NAME'], MB_CASE_TITLE);
        $arFields['~LAST_NAME'] = trim(preg_replace('/[^а-я^\s^\-]+/iu', '', $arFields['LAST_NAME'], -1, $countReplaceLastName));
        $arFields['LAST_NAME'] = mb_convert_case($arFields['LAST_NAME'], MB_CASE_TITLE);
        $arFields['~SECOND_NAME'] = trim(preg_replace('/[^а-я^\s^\-]+/iu', '', $_REQUEST['USER_SECOND_NAME'], -1, $countReplaceSecondName));
        $arFields['SECOND_NAME'] = mb_convert_case($_REQUEST['USER_SECOND_NAME'], MB_CASE_TITLE);
        $_REQUEST['SECOND_NAME'] = $arFields['SECOND_NAME'];
        $arFields['PERSONAL_PHONE'] = htmlspecialcharsbx($_REQUEST['USER_PERSONAL_PHONE']);
        $_REQUEST['PERSONAL_PHONE'] = $arFields['PERSONAL_PHONE'];
        $arFields['UF_PROMO_CODE'] = htmlspecialcharsbx($_REQUEST['UF_PROMO_CODE']);
        $arFields['UF_PROMO_CODE'] = empty($arFields['UF_PROMO_CODE']) ? '' : $arFields['UF_PROMO_CODE'];
        $arFields['UF_FB_PROFILE'] = htmlspecialcharsbx($_REQUEST['UF_FB_PROFILE']);
        $arFields['UF_FB_PROFILE'] = empty($arFields['UF_FB_PROFILE']) ? '' : $arFields['UF_FB_PROFILE'];
        $arFields['UF_UTM_SOURCE'] = isset($_SESSION['utm_source']) ? htmlspecialcharsbx($_SESSION['utm_source']) : '';
        $arFields['UF_UTM_MEDIUM'] = isset($_SESSION['utm_medium']) ? htmlspecialcharsbx($_SESSION['utm_medium']) : '';
        $arFields['UF_UTM_CAMPAIGN'] = isset($_SESSION['utm_campaign']) ? htmlspecialcharsbx($_SESSION['utm_campaign']) : '';
        $arFields['UF_UTM_TERM'] = isset($_SESSION['utm_term']) ? htmlspecialcharsbx($_SESSION['utm_term']) : '';
        $arFields['UF_UTM_CONTENT'] = isset($_SESSION['utm_content']) ? htmlspecialcharsbx($_SESSION['utm_content']) : '';

        $countReplaceName = $countReplaceName ? $APPLICATION->ThrowException('ERROR #' . __LINE__, 'REGISTER_ERROR_NOT_CYRILIC_NAME') : $countReplaceName;
        $countReplaceLastName = $countReplaceLastName ? $APPLICATION->ThrowException('ERROR #' . __LINE__, 'REGISTER_ERROR_NOT_CYRILIC_LAST_NAME') : $countReplaceLastName;
        $countReplaceSecondName = $countReplaceSecondName ? $APPLICATION->ThrowException('ERROR #' . __LINE__, 'REGISTER_ERROR_NOT_CYRILIC_SECOND_NAME') : $countReplaceSecondName;

        foreach (array('LOGIN', 'EMAIL', 'PERSONAL_PHONE', 'NAME', 'LAST_NAME', 'UF_EULA') as $keyField):
            if (!isset($arFields[$keyField]) || empty($arFields[$keyField])):
                $APPLICATION->ThrowException(GetMessage('REGISTER_ERROR_EMPTY_' . $keyField), 'REGISTER_ERROR_EMPTY_' . $keyField);
            endif;
        endforeach;

        /*$soap = xGuard\Main\Soap\Params::GetSoapInstance();
        $arFields['PROFILE_NAME'] = trim(implode(' ', array($arFields['LAST_NAME'], $arFields['NAME'], $arFields['SECOND_NAME'],)));
        $arFields['SOAP'] = array(
            'pStruct' => array(
                'Login'      => $arFields['LOGIN'],
                'Edit'       => false,
                'Phone'      => $arFields['PERSONAL_PHONE'],
                'Email'      => $arFields['EMAIL'],
                'FIO'        => $arFields['PROFILE_NAME'],
                'PromoCode'  => $arFields['UF_PROMO_CODE'],
                'FacebookID' => $arFields['UF_FB_PROFILE'],
            ),
        );
        debugfile($arFields, 'user.log');
        $result = $soap->RegUser($arFields['SOAP']);

        if (!$result->return->Status):
            debugfile(array($result), 'user.log');

            $APPLICATION->ThrowException($result->return->ErrorList->Error->_, 'REGISTER_ERROR_EMPTY_UF_FB_PROFILE');

            return false;
        endif;*/

        return !$APPLICATION->GetException();
    }
endif;

if (!function_exists('domovoyOnAfterUserRegisterCheck')):

    function domovoyOnAfterUserRegisterCheck($arFields)
    {

        global $APPLICATION, $USER;


        $client = get_SOAP();


        if (empty($arFields['USER_ID'])):

            $APPLICATION->ThrowException('ERROR #' . __LINE__, 'ERROR_EXCHANGE_1C_EMPTY_USER');
            debugfile(array($arFields), 'user.log');

            return false;

        endif;


        $headers = "From: test@" . $_SERVER['HTTP_HOST'] . "\r\n" .
            "Reply-To: test@" . $_SERVER['HTTP_HOST'] . "\r\n" .
            "X-Mailer: PHP/" . phpversion();
        mail("erynrandir@yandex.ru", "dev.stionline.ru test event", "testOnAfterUserRegisterCheck" . time(), $headers);


        /*
        $arEventFields = array(
            'NAME'  => 'Имя',
            'PHONE' => 'телефон',
            'EMAIL' => 'Почта'
        );

        CEvent::Send("SALE_NEW_ORDER", SITE_ID, $arEventFields);
        */


        try {


            if (Loader::includeModule('sale')):

                $arParams['SALE_ORDER_PROPERTIES']['GETLIST'] = array(
                    'ORDER' => array('sort' => 'asc'),
                    'FILTER' => array('ACTIVE' => 'Y', 'USER_PROPS' => 'Y', 'PERSON_TYPE_ID' => PERSON_TYPE_ID_PP),
                );

                $nsItem = \CSaleOrderProps::GetList(
                    $arParams['SALE_ORDER_PROPERTIES']['GETLIST']['ORDER'],
                    $arParams['SALE_ORDER_PROPERTIES']['GETLIST']['FILTER']
                );

                $arGroups = array();

                while ($arItem = $nsItem->Fetch()):
                    $arGroups[$arItem['SORT']] = !isset($arGroups[$arItem['SORT']]) ? (1) : (++$arGroups[$arItem['SORT']]);
                    $arResult['ORDER_PROPS'][$arItem['PERSON_TYPE_ID']][] = $arItem;
                    $arResult['~ORDER_PROPS'][$arItem['CODE']] = $arItem['ID'];
                endwhile;

                $arFields['USER_PROFILE_ID'] = '';
                $arFields['PERSON_TYPE_ID'] = PERSON_TYPE_ID_PP;
                $arFields['PROFILE_NAME'] = trim(implode(' ', array($arFields['LAST_NAME'], $arFields['NAME'], $arFields['SECOND_NAME'],)));
                $arFields['PROPS'] = array(
                    $arResult['~ORDER_PROPS']['FULL_NAME'] => $arFields['PROFILE_NAME'],
                    $arResult['~ORDER_PROPS']['PHONE'] => $arFields['PERSONAL_PHONE'],
                    $arResult['~ORDER_PROPS']['EMAIL'] => $arFields['EMAIL'],
                    $arResult['~ORDER_PROPS']['APPROVE'] => 'N',
                );

                \CSaleOrderUserProps::DoSaveUserProfile($arFields['USER_ID'], $arFields['USER_PROFILE_ID'], $arFields['PROFILE_NAME'], $arFields['PERSON_TYPE_ID'], $arFields['PROPS'], $arErrors);

                $arFields['USER_PROFILE'] = \CSaleOrderUserProps::GetList(
                    array(),
                    array('USER_ID' => $arFields['USER_ID'])
                )->Fetch();
                $arFields['USER_PROFILE_ID'] = $arFields['USER_PROFILE']['ID'];

            endif;


            $arFields['SOAP'] = array(
                'pStruct' => array(
                    'Login' => $arFields['LOGIN'],
                    'Edit' => false,
                    'Phone' => $arFields['PERSONAL_PHONE'],
                    'Email' => $arFields['EMAIL'],
                    'FIO' => $arFields['PROFILE_NAME'],
                    'PromoCode' => $arFields['UF_PROMO_CODE'],
                    'FacebookID' => $arFields['UF_FB_PROFILE'],
                ),
            );
            debugfile($arFields['SOAP'], 'user.log');

            //$result = $soap->RegUser($arFields['SOAP']);
            $result = $client->RegUser($arFields['SOAP']);

            if (!$result->return->Status):

                debugfile(array($result), '1c.log');

                $APPLICATION->ThrowException($result->return->ErrorList->Error, 'ERROR_EXCHANGE_1C_CREATE_USER');

                return false;

            endif;


            $arFields['SOAP'] = array(
                'pStruct' => array(
                    'TypeClient' => "ЮрЛицо", //constant('PERSON_TYPE_'.$arFields['PERSON_TYPE_ID']),
                    'Login' => $arFields['LOGIN'],
                    'Phone' => $arFields['PERSONAL_PHONE'],
                    'Email' => $arFields['EMAIL'],
                    'Name' => $arFields['PROFILE_NAME'],
                    'AddrrLegal' => '',
                ),
            );
            debugfile($arFields['SOAP'], 'user.log');

            //$result = $soap->CreateClient($arFields['SOAP']);
            $result = $client->CreateClient($arFields['SOAP']);

            if (!$result->return->Status):

                debugfile(array($result), '1c.log');

                $APPLICATION->ThrowException($result->return->ErrorList->Error, 'ERROR_EXCHANGE_1C_CREATE_ACCOUNTS');

                return false;

            else:

                $arOrderProperty = \CSaleOrderProps::GetList(
                    array(),
                    array("PERSON_TYPE_ID" => $arFields['PERSON_TYPE_ID'], 'CODE' => 'GUID'),
                    false,
                    false,
                    array("ID", "NAME",)
                )->Fetch();
                $arFields['USER_PROPS_GUID'] = array(
                    "USER_PROPS_ID" => $arFields['USER_PROFILE_ID'],
                    "ORDER_PROPS_ID" => $arOrderProperty["ID"],
                    "NAME" => $arOrderProperty["NAME"],
                    "VALUE" => $result->return->GUID,
                );
                CSaleOrderUserPropsValue::Add($arFields['USER_PROPS_GUID']);
            endif;

            $arFields['SECOND_NAME'] = $_REQUEST['SECOND_NAME'];
            $arFields['PERSONAL_PHONE'] = $_REQUEST['PERSONAL_PHONE'];
            $arFields['UF_UTM_SOURCE'] = isset($_SESSION['utm_source']) ? htmlspecialcharsbx($_SESSION['utm_source']) : '';
            $arFields['UF_UTM_MEDIUM'] = isset($_SESSION['utm_medium']) ? htmlspecialcharsbx($_SESSION['utm_medium']) : '';
            $arFields['UF_UTM_CAMPAIGN'] = isset($_SESSION['utm_campaign']) ? htmlspecialcharsbx($_SESSION['utm_campaign']) : '';
            $arFields['UF_UTM_TERM'] = isset($_SESSION['utm_term']) ? htmlspecialcharsbx($_SESSION['utm_term']) : '';
            $arFields['UF_UTM_CONTENT'] = isset($_SESSION['utm_content']) ? htmlspecialcharsbx($_SESSION['utm_content']) : '';

            unset(
                $_SESSION['utm_source'],
                $_SESSION['utm_medium'],
                $_SESSION['utm_campaign'],
                $_SESSION['utm_term'],
                $_SESSION['utm_content']
            );

            $user = new \CUser;
            $user->Update($arFields['USER_ID'], $arFields);

            debugfile(array($user->LAST_ERROR), 'user.log');


        } catch (\SoapFault $e) {

            $APPLICATION->ThrowException($e->GetMessage(), 'EXCHANGE_1C_ERROR');
            debugfile(array($e->GetMessage()), '1c.log');

            return false;

        }

        return true;
    }
endif;

if (!function_exists('domovoyOnAfterUserUpdateCheck')):
    function domovoyOnAfterUserUpdateCheck($arFields)
    {
        global $APPLICATION, $USER;


        $client = get_SOAP();


        if (count($arFields) && isset($arFields['PASSWORD'])):
            $_SESSION['PASSWORD_CHANGES_TIME'] = time();
        endif;

        try {

            //$soap = xGuard\Main\Soap\Params::getSoapInstance();

            $arFields['PROFILE_NAME'] = trim(implode(' ', array($arFields['LAST_NAME'], $arFields['NAME'], $arFields['SECOND_NAME'],)));
            $arFields['SOAP'] = array(
                'pStruct' => array(
                    'Login' => $arFields['LOGIN'],
                    'Edit' => true,
                    'Phone' => $arFields['PERSONAL_PHONE'],
                    'Email' => $arFields['EMAIL'],
                    'FIO' => $arFields['PROFILE_NAME'],
                    //'PromoCode' => $arFields['UF_PROMO_CODE'],
                    'FacebookID' => $arFields['UF_FB_PROFILE'],
                ),
            );

            $result = $client->RegUser($arFields['SOAP']);
            debugfile(array($result, $arFields['SOAP']), 'user.log');

            if (!$result->return->Status):
                debugfile(array($result), '1c.log');

                $APPLICATION->ThrowException($result->return->ErrorList->Error, 'ERROR_EXCHANGE_1C_CREATE_USER');

                return false;
            endif;


            $arFields['UF_PROMO_CODE'] = trim($arFields['UF_PROMO_CODE']);
            $arFields['UF_PROMO_CODE'] = preg_replace('/[\s\t]/', '', $arFields['UF_PROMO_CODE']);

            debugfile($arFields, 'user.log');


            if (!empty($arFields['UF_PROMO_CODE'])):
                $arFields['SOAP'] = array(
                    'User' => $arFields['LOGIN'],
                    'Promo' => $arFields['UF_PROMO_CODE'],
                );

                $result = $client->VerifiPromo($arFields['SOAP']);
                debugfile(array($result, $arFields), 'promo_code.log');
                if (!$result->return || !$result->return->Status):
                    $APPLICATION->ThrowException(GetMessage('XGUARD_PROMO_CODE_RETURNS_ERROR'));

                    unset($_SESSION['PROMO_CODE_CHANGES_TIME']);

                    return false;
                endif;

                $group = \Bitrix\Main\GroupTable::getList(['filter' => ['STRING_ID' => $arFields['UF_PROMO_CODE']]])->fetch();

                if (!empty($group)) {
                    $userId = $USER->getId();
                    $arGroupsQuery = CUser::GetUserGroupEx($userId);
                    $arGroups = [];

                    while ($arGroup = $arGroupsQuery->Fetch()) {
                        $arGroups[] = $arGroup;
                    }
                    debugfile($arGroups, 'promo_code.log');

                    $arGroups[] = [
                        'GROUP_ID' => $group['ID'],
                        'DATE_ACTIVE_FROM' => date('d.m.Y H:i:s'),
                        'DATE_ACTIVE_TO' => date('d.m.Y H:i:s', strtotime($result->return->DateEnd)),
                    ];

                    CUser::SetUserGroup($userId, $arGroups);

                    if (is_array($_SESSION['SESS_AUTH']['GROUPS'])) {
                        $_SESSION['SESS_AUTH']['GROUPS'][] = $group['ID'];
                    }
                }

                $_SESSION['PROMO_CODE_CHANGES_TIME'] = strtotime($result->return->DateEnd);
            endif;

            debugfile($arFields, 'user.log');

        } catch (\xGuard\Main\Exception $e) {
            $APPLICATION->ThrowException($e->getMessage(), 'EXCHANGE_1C_ERROR');

            return false;
        }

        return true;
    }
endif;

// AddEventHandler('main', 'OnEpilog', '_Check404Error', 1);  
// function _Check404Error(){
//    if(defined('ERROR_404') && ERROR_404=='Y' || CHTTP::GetLastStatus() == "404 Not Found"){
//       GLOBAL $APPLICATION;
//       $APPLICATION->RestartBuffer();
//       require_once $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/header.php';
//       require $_SERVER['DOCUMENT_ROOT'].'/404.php';
//       require_once $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/footer.php';
//    }
// }

