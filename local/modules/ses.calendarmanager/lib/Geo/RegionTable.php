<?php
namespace SES\CalendarManager\Geo;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
class RegionTable extends DataManager
{
    public static function getTableName()
    {
        return 'calendar_geo_regions';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new IntegerField('DISTRICT_ID', [
                'required' => true
            ]),
            new StringField('NAME', [
                'required' => true
            ]),
            new Reference(
                'DISTRICT',
                DistrictTable::class,
                Join::on('this.DISTRICT_ID', 'ref.ID')
            )
        ];
    }
}
