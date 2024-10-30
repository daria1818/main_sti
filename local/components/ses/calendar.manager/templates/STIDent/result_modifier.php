<?
$arResult['arParams'] = $arParams;

$groupedByMonth = [];

foreach ($arResult['DAYS'] as $day) {
    $month = $day['month'];
    if (!isset($groupedByMonth[$month])) {
        $groupedByMonth[$month] = [];
    }
    $groupedByMonth[$month][$day['day']] = $day;
}

ksort($groupedByMonth);

foreach ($groupedByMonth as $month => &$days) {
    ksort($days);
}

unset($days);
unset($arResult['DAYS']);
$arResult['DAYS'] = $groupedByMonth;

$arResult["sortMonth"] = array_keys($arResult['DAYS']);

function getMonthName($monthNumber) {
    $months = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь'
    ];

    return isset($months[$monthNumber]) ? $months[$monthNumber] : 'Неверный номер месяца';
}

?>