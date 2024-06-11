<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class EventsTable extends Entity\DataManager
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
        return 'rtop_kpi_events';
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
            new Entity\IntegerField('HANDLER', array(
                'required' => true,
            )),
            new Entity\ReferenceField(
                'EVENT',
                'Rtop\KPI\HandlersTable',
                array('=this.HANDLER' => 'ref.ID')
            ),
            new Entity\FloatField('VALUE', array(
                'required' => false
            )),
            new Entity\IntegerField('MIN_COST', array(
                'required' => false
            )),
            new Entity\IntegerField('MAX_COST', array(
                'required' => false
            )),
            new Entity\TextField('ROLE', array(
                'serialized' => true,
                'required' => true
            )),
            new Entity\TextField('DEPARTMENT', array(
                'serialized' => true,
                'required' => false
            ))
        );
    }
}
?>