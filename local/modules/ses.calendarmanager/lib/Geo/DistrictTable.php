<?php
namespace SES\CalendarManager\Geo;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class DistrictTable extends DataManager
{
    public static function getTableName()
    {
        return 'calendar_geo_district';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new StringField('NAME', [
                'required' => true
            ])
        ];
    }
}
