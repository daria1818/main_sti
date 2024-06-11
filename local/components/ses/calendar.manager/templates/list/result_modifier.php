<?
$groupedByMonth = [];

foreach ($arResult['DAYS'] as $day) {
    $month = $day['month'];
    if (!isset($groupedByMonth[$month])) {
        $groupedByMonth[$month] = [];
    }
    $groupedByMonth[$month][$day['day']] = $day;
}

// Сортируем месяцы в порядке возрастания
ksort($groupedByMonth);

// Сортируем дни в каждом месяце в порядке возрастания
foreach ($groupedByMonth as $month => &$days) {
    ksort($days);
}

// Удаляем ссылку на последний элемент
unset($days);
unset($arResult['DAYS']);
$arResult['DAYS'] = $groupedByMonth;

// Удаляем старую структуру, если она больше не нужна

?>