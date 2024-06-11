<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class HistoryBalanceTable extends Entity\DataManager
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
        return 'rtop_kpi_history_balance';
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
            new Entity\DateTimeField('TIMESTAMP_X', array(
                'default_value' => new Main\Type\DateTime
            )),
            new Entity\IntegerField('USERID', array(
                'required' => true
            )),
            new Entity\StringField('CODE', array(
                'required' => true,
            )),
            new Entity\StringField('CLIENT', array(
                'required' => false
            )),
            new Entity\FloatField('SUM', array(
                'required' => true
            )),
            new Entity\IntegerField('OFFER', array(
                'required' => false
            )),
            new Entity\StringField('INITIATOR', array(
                'required' => true
            )),
            new Entity\StringField('TYPE', array(
                'required' => false
            )),
            new Entity\TextField('COMMENT', array(
                'required' => false
            )),
            new Entity\ReferenceField(
                'FUNCTION',
                'Rtop\KPI\HandlersTable',
                array('=this.CODE' => 'ref.FUNCTION')
            ),
            new Entity\ReferenceField(
                'MANAGER',
                '\Bitrix\Main\UserTable',
                array('=this.USERID' => 'ref.ID')
            ),            
            new Entity\ExpressionField('MIN_TIMESTAMP_X', 'CONCAT(YEAR(MIN(TIMESTAMP_X)),"-",MONTH(MIN(TIMESTAMP_X)))'),
            new Entity\ExpressionField('MAX_TIMESTAMP_X', 'CONCAT(YEAR(MAX(TIMESTAMP_X)),"-",MONTH(MAX(TIMESTAMP_X)))')
        );
    }
}
?>