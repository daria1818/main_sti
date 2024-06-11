<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RolesTable extends Entity\DataManager
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
        return 'rtop_kpi_roles';
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
                'title' => Loc::getMessage('ROLE_ENTITY_ID_FIELD'),
            ),
            'CODE' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('ROLE_ENTITY_CODE_FIELD'),
            ),
            'ROLE' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => Loc::getMessage('ROLE_ENTITY_ROLE_FIELD'),
            )
        );
    }
}
?>