<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Server;
use Sotbit\Seometa\Helper\OGraphTWCard;
use Sotbit\Seometa\SeometaUrlTable;
use Sotbit\Seometa\SeometaStatisticsTable;

class CSeoMetaEvents
{
    protected static $lAdmin;
    private static $i = 1;

    function OnInit()
    {
        return array(
            "TABSET" => "seometa",
            "GetTabs" => array(
                "CSeoMetaEvents",
                "GetTabs"
            ),
            "ShowTab" => array(
                "CSeoMetaEvents",
                "ShowTab"
            ),
            "Action" => array(
                "CSeoMetaEvents",
                "Action"
            ),
            "Check" => array(
                "CSeoMetaEvents",
                "Check"
            )
        );
    }

    public static function OnBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu){
        \Sotbit\Seometa\Helper\Menu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }

    function Action($arArgs)
    {
        return true;
    }

    function Check($arArgs)
    {
        return true;
    }

    function GetTabs($arArgs)
    {
        global $APPLICATION;
        if($APPLICATION->GetGroupRight("sotbit.seometa") == "D")
            return false;

        $arTabs = array(
            array(
                "DIV" => "url-mode",
                "TAB" => GetMessage('seometa_title'),
                "ICON" => "sale",
                "TITLE" => GetMessage('seometa_list'),
                "SORT" => 5
            )
        );
        return $arTabs;
    }

    function ShowTab($divName, $arArgs, $bVarsFromForm)
    {
        if($divName == "url-mode")
        {
            define('B_ADMIN_SUBCONDITIONS', 1);
            define('B_ADMIN_SUBCONDITIONS_LIST', false);
            ?>
            <tr id="tr_COUPONS">
                <td colspan="2">
                    <?
                    require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sotbit.seometa/admin/templates/sub_list.php');
                    ?>
                </td>
            </tr>
            <?
        }
    }

    public static function PageStart()
    {
        global $APPLICATION, $PAGEN_1;

        //off autocompozite
        //\Bitrix\Main\Data\StaticHtmlCache::getInstance()->markNonCacheable();

        if(mb_strpos($APPLICATION->GetCurPage(false), '/bitrix') === 0)
            return;

        $excludeParams = \Bitrix\Main\Config\Option::get("sotbit.seometa", 'PARAMS_EXCEPTION_SETTINGS', '', SITE_ID);
        if($excludeParams = explode(";", $excludeParams)) {
            foreach ($excludeParams as $key => $excludeParam) {
                $value = explode('=', $excludeParam);
                unset($excludeParams[$key]);
                $excludeParams[mb_strtoupper($value[0])] = (mb_strtoupper($value[1]) ?: '');
            }
        }

        $context = Bitrix\Main\Context::getCurrent();
        if($context->getRequest()->isAjaxRequest() && \Bitrix\Main\Config\Option::get("sotbit.seometa", 'RETURN_AJAX_' . SITE_ID, 'N', SITE_ID) == 'Y')
            return;

        if(!$context->getRequest()->getQueryList()->isEmpty() && method_exists($context->getRequest()->getQueryList(), 'getValues')) {
            $queryValues = $context->getRequest()->getQueryList()->getValues();
            foreach ($queryValues as $key => $queryValue) {
                $queryValues[mb_strtoupper($key)] = is_array($queryValue) ? array_change_key_case($queryValue, CASE_UPPER) : mb_strtoupper($queryValue);
                unset($queryValues[$key]);
            }

            foreach ($excludeParams as $key => $excludeParam) {
                if( $excludeParam && ($queryValues[$key] == $excludeParam) || ($queryValues[$key] && !$excludeParam) ) {
                    return;
                }
            }
        }

        $server = $context->getServer();
        $server_array = $server->toArray();
        $url_parts = explode("?", $context->getRequest()->getRequestUri());

        $str = \Bitrix\Main\Config\Option::get("sotbit.seometa", 'PAGENAV_' . SITE_ID, '', SITE_ID);

        if($str != '')
        {
            $preg = str_replace('/', '\/', $str);
            $preg = '/' . str_replace('%N%', '\d', $preg) . '/';
            preg_match($preg, $url_parts[0], $matches);
            if($matches)
            {
                $exploted_pagen = explode('%N%', $str);
                $n = str_replace($exploted_pagen[0], '', $matches[0]);
                $n = str_replace($exploted_pagen[1], '', $n);
                $_REQUEST['PAGEN_1'] = (int)$n;
                $url_parts[0] = str_replace($matches[0], '', $url_parts[0]);
            }
            if(isset($_REQUEST['PAGEN_1']))
            {
                $n = $_REQUEST['PAGEN_1'];
                $pagen = str_replace('%N%', $n, $str);
                $url_parts[1] = '';

                unset($_GET['PAGEN_1']);
                foreach($_GET as $i => $p)
                {
                    $r[] = $i . '=' . $p;
                }
                $r[] = $pagen;
                $url_parts[1] = implode('&', $r);
                $PAGEN_1 = $n;
            }
        }

        if(!($instance = Sotbit\Seometa\SeometaUrlTable::getByNewUrl($url_parts[0])) && !($instance = Sotbit\Seometa\SeometaUrlTable::getByNewUrl($context->getRequest()->getRequestUri())))
        {
            $instance = Sotbit\Seometa\SeometaUrlTable::getByRealUrl($url_parts[0]);

            if(!$instance)
                $instance = Sotbit\Seometa\SeometaUrlTable::getByRealUrl($context->getRequest()->getRequestUri());

            if($instance && in_array(SITE_ID, unserialize($instance['SITE_ID'])) && CSeoMetaEvents::$i)
            {
                CSeoMetaEvents::$i = 0;
                if(isset($pagen))
                {
                    $instance['NEW_URL'] = '/' . trim($instance['NEW_URL'], '/') . $pagen;
                    $url_parts[1] = '';
                }
                LocalRedirect($instance['NEW_URL'] . ($url_parts[1] != '' ? "?" . $url_parts[1] : ''), false, '301 Moved Permanently');
            }
        }

        if($instance && ($instance['NEW_URL'] != $instance['REAL_URL']) && in_array(SITE_ID, unserialize($instance['SITE_ID'])))
        {
            $url_parts = explode("&", $url_parts[1]);
            $urlPartsCHPU = explode("?", $instance['REAL_URL']);

            if($urlPartsCHPU[1]) {
                $urlPartsCHPU = explode("&", $urlPartsCHPU[1]);

                if($urlPartsCHPU) {
                    $url_parts = array_merge($urlPartsCHPU);
                }
            }

            foreach($url_parts as $item)
            {
                $items = explode('=', $item);
                $_GET[$items[0]] = $items[1];
            }

            if(!isset($pagen))
            {
                $_SERVER['REQUEST_URI'] = $instance['REAL_URL'];
                $server_array['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                $server->set($server_array);

                $context->initialize(new Bitrix\Main\HttpRequest($server, $_GET, array(), array(), $_COOKIE), $context->getResponse(), $server);
                $APPLICATION->reinitPath();
                CSeoMetaEvents::$i = 0;
            }
            else
            {
                $url_parts[0] .= $pagen;
                $_SERVER['REQUEST_URI'] = $instance['REAL_URL'];
                $server_array['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                $server->set($server_array);
                $context->initialize(new Bitrix\Main\HttpRequest($server, $_GET, array(), array(), $_COOKIE), $context->getResponse(), $server);
                $APPLICATION->reinitPath();
                $APPLICATION->SetCurPage($url_parts[0]);
                CSeoMetaEvents::$i = 0;
            }
        }
    }

    /*
    * It is necessary to include processing of outdated events in settings of an e-commerce shop
    */
    public static function OrderAdd($ID, $arFields)
    {
        global $APPLICATION;
        $cookie = $APPLICATION->get_cookie("sotbit_seometa_statistic");
        echo $cookie;
        if(!empty($cookie) && $cookie == bitrix_sessid() && SeometaStatisticsTable::getBySessId($cookie))
        {
            $stat = SeometaStatisticsTable::getBySessId($cookie);
            $stat['ORDER_ID'] = intval($ID);
            SeometaStatisticsTable::update($stat['ID'], $stat);
        }
    }

    public function OnReindexHandler($NS, $oCallback, $callback_method)
    {
        self::clearTable();

        $writer = \Sotbit\Seometa\Link\ReindexWriter::getInstance($oCallback, $callback_method);
        $link = \Sotbit\Seometa\Helper\Linker::getInstance();
        $rsData = \Sotbit\Seometa\ConditionTable::getList(array('filter' => array('ACTIVE' => 'Y', 'SEARCH' => 'Y')));

        while($condition = $rsData->fetch())
        {
            $link->Generate($writer, $condition['ID']);
        }

        $data = $writer->getData();

        return !empty($data);
    }

    /**
     * clear table b_search_content by module_id = sotbit.seometa
     * */
    private function clearTable()
    {
        $DB = CDatabase::GetModuleConnection('search');
        $DB->Query("DELETE FROM b_search_content WHERE ITEM_ID LIKE 'seometa%'");
    }

    public static function OnAfterIndexAddHandler($ID, $arFields)
    {
        if($arFields['MODULE_ID'] == 'sotbit.seometa')
        {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->query('UPDATE `b_search_content` SET `MODULE_ID` = "iblock" WHERE `MODULE_ID` = "sotbit.seometa"');
        }
    }

    public static function ChangeContent(&$content) {
        global $APPLICATION;
        $seoData = new OGraphTWCard();

        if($_POST['AJAX'] != 'Y' && mb_strpos($APPLICATION->GetCurPage(false), '/bitrix') === false && $data = $seoData->getData()) {
            foreach ($data as $name => $value) {
                $count = 0;
                $searchPattern = "/<meta\s+?property=[\"|']" . $name . "[\"|']\scontent=[\"|'][\s\S]*?[\"|']\/?>/";
                $content = preg_replace($searchPattern, $seoData->createMeta($name, $value), $content, -1, $count);

                if($count === 0) {
                    $content = preg_replace('/<\/head>/', $seoData->createMeta($name, $value) . '</head>', $content);
                }
            }
        }
    }
}
