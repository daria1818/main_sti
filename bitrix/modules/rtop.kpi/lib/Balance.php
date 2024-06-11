<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class BalanceTable extends Entity\DataManager
{
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
        return 'rtop_kpi_balance';
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
                'title' => Loc::getMessage('USER_ENTITY_ID_FIELD'),
            ),
            'USERID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_USER_ID_FIELD'),
            ),
            'ROLE' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_ROLE_FIELD'),
            ),
            'DEPARTMENT' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_DEPARTMENT_FIELD'),
            ),
            'BALANCE' => array(
                'data_type' => 'float',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_BALANCE_FIELD'),
            ),
        );
    }
}
?>