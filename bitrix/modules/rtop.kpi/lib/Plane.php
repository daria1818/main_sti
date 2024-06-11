<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class PlaneTable extends Entity\DataManager
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
        return 'rtop_kpi_plane';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\StringField('CODE', array(
                'required' => true
            )),
            new Entity\IntegerField('USERID', array(
                'required' => false
            )),
            new Entity\FloatField('PLANEVALUE', array(
                'required' => true
            )),
            new Entity\TextField('HISTORY', array(
                'serialized' => true
            ))
        );
    }
}
?>