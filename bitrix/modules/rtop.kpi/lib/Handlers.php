<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Rtop\KPI\Logger as Log;
Loc::loadMessages(__FILE__);

class HandlersTable extends Entity\DataManager
{
    protected static $module_id = "rtop.kpi";

    public static function getFilePath()
    {
        return __FILE__;
    }
	/**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'rtop_kpi_handlers';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('HANDLERS_ENTITY_ID_FIELD'),
            ),
            'NAME' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('HANDLERS_ENTITY_NAME_FIELD'),
            ),
            'CODE' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('HANDLERS_ENTITY_CODE_FIELD'),
            ),
            'TYPE' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('HANDLERS_ENTITY_TYPE_FIELD'),
            ),
            'FUNCTION' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => Loc::getMessage('HANDLERS_ENTITY_FUNCTION_FIELD'),
            ),
            'AUTO' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => Loc::getMessage('HANDLERS_ENTITY_AUTO_FIELD'),
            ),
            'PERIOD' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => Loc::getMessage('HANDLERS_ENTITY_PERIOD_FIELD'),
            )
        );
    }

    public static function getTypes()
    {
        $types = ['php', 'js', 'custom'];

        foreach($types as &$type){
            $type = [
                'CODE' => $type,
                'NAME' => Loc::getMessage('HANDLERS_TYPE_' . $type)
            ];
        }

        return $types;
    }

    public static function getPeriods()
    {
        $periods = ['day', 'month', 'quarter'];

        foreach($periods as &$period){
            $period = [
                'CODE' => $period,
                'NAME' => Loc::getMessage('HANDLERS_PERIOD_' . $period)
            ];
        }

        return $periods;
    }

    public static function OnAdd(Entity\Event $event)
    {
        $fields = $event->getParameter("fields");
        if($fields['TYPE'] == 'php' && $fields['AUTO'] == 'N' && !empty($fields['CODE']) && !empty($fields['FUNCTION']))
        {
            $list = self::getModulesList();
            $eventManager = \Bitrix\Main\EventManager::getInstance();
            $eventManager->registerEventHandler($list[$fields['CODE']], $fields['CODE'], self::$module_id, '\\Rtop\\KPI\\EventManager', $fields['FUNCTION']);
        }
    }

    public static function OnBeforeDelete(Entity\Event $event)
    {
        $primary = $event->getParameter("id");
        $tmp = self::getById($primary)->fetch();
        if($tmp['TYPE'] == 'php' && $tmp['AUTO'] == 'N')
        {
            $list = self::getModulesList();
            $eventManager = \Bitrix\Main\EventManager::getInstance();
            $eventManager->unregisterEventHandler($list[$tmp['CODE']], $tmp['CODE'], self::$module_id, '\\Rtop\\KPI\\EventManager', $tmp['FUNCTION']);
        }        
    }

    public static function OnUpdate(Entity\Event $event)
    {
        $primary = $event->getParameter("id");
        $fields = $event->getParameter("fields");
        if($fields['TYPE'] == 'php' && $fields['AUTO'] == 'N'){
            $list = self::getModulesList();
            $eventManager = \Bitrix\Main\EventManager::getInstance();
            $eventManager->registerEventHandler($list[$fields['CODE']], $fields['CODE'], self::$module_id, '\\Rtop\\KPI\\EventManager', $fields['FUNCTION']);
        }
    }

    protected static function getModulesList()
    {
        return [
            'OnAfterCrmContactAdd' => 'crm',
            'OnAfterCrmCompanyAdd' => 'crm',
            'OnBeforeCrmContactUpdate' => 'crm',
            'OnBeforeCrmCompanyUpdate' => 'crm',
            'OnSaleOrderSaved' => 'sale',
            'OnSaleOrderBeforeSaved' => 'sale',
            'OnSaleStatusOrderChange' => 'sale',
            'OnBeforeMailSend' => 'main',
            'OnImConnectorMessageAdd' => 'imconnector',
            'OnSaleOrderCanceled' => 'sale',
            'OnCalendarEntryAdd' => 'calendar',
            'OnAfterCrmDealUpdate' => 'crm',
            'OnCrmDealUpdate' => 'crm'
        ];
    }
}
?>