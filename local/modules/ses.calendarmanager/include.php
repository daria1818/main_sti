<?php
\Bitrix\Main\Loader::registerAutoLoadClasses('ses.calendarmanager', [
    'SES\CalendarManager\HLBlockManager' => 'lib/HLBlockManager.php',
    'SES\CalendarManager\CalendarSchedule' => 'lib/CalendarSchedule.php',
    'SES\CalendarManager\CalendarCourse' => 'lib/CalendarCourse.php',
    'SES\CalendarManager\CalendarUsers' => 'lib/CalendarUsers.php',
    'SES\CalendarManager\Logger' => 'lib/CalendarLogger.php',
    'SES\CalendarManager\Geo\DistrictTable' => 'lib/Geo/DistrictTable.php',
    'SES\CalendarManager\Geo\RegionTable' => 'lib/Geo/RegionTable.php',
    'SES\CalendarManager\Geo\CityTable' => 'lib/Geo/CityTable.php'
]);
