<?php
namespace SES\CalendarManager\Geo;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class CityTable extends DataManager
{
    public static function getTableName()
    {
        return 'calendar_geo_city';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new IntegerField('REGION_ID', [
                'required' => true
            ]),
            new StringField('NAME', [
                'required' => true
            ]),
            new Reference(
                'REGION',
                RegionTable::class,
                Join::on('this.REGION_ID', 'ref.ID')
            )
        ];
    }
}
