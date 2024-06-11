<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CalendarComponent extends \CBitrixComponent
{
    protected $currentMonth;
    protected $days = [];

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('calendar')) {
            $this->abortResultCache();
            ShowError(Loc::getMessage('CALENDAR_MODULE_NOT_INSTALLED'));
            return;
        }

        $this->prepareData();
        $this->includeComponentTemplate();
    }

    protected function prepareData()
    {
        // Получаем текущий месяц и год
        $this->currentMonth = date('n');
        
        // Заполняем массив дней
        $this->fillDays();
        
        // Добавляем массив дней в $arResult
        $this->arResult['DAYS'] = $this->days;
    }

    protected function fillDays()
    {
        // Определяем первый день текущего месяца
        $firstDayOfMonth = mktime(0, 0, 0, $this->currentMonth, 1, date('Y'));

        // Определяем день недели первого дня месяца (0 - воскресенье, 1 - понедельник, ..., 6 - суббота)
        $firstDayOfWeek = date('N', $firstDayOfMonth);

        // Определяем количество дней в месяце
        $daysInMonth = date('t', $firstDayOfMonth);

        // Определяем день недели последнего дня месяца
        $lastDayOfWeek = date('N', strtotime("last day of this month"));

        // Заполняем массив дней текущего месяца
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayAttributes = array(
                'day' => $i,
                // Добавляем атрибуты дня, например, доступность и т.д.
                'availability' => $this->checkDayAvailability($i),
            );
            $this->days[] = $dayAttributes;
        }

        // Если первый день месяца не понедельник, добавляем дни предыдущего месяца в начало массива
        if ($firstDayOfWeek != 1) {
            $prevMonthDays = date('t', mktime(0, 0, 0, $this->currentMonth - 1, 1, date('Y')));
            for ($i = $firstDayOfWeek - 1; $i > 0; $i--) {
                $dayAttributes = array(
                    'day' => $prevMonthDays - ($i - 1),
                    // Добавляем атрибуты дня предыдущего месяца
                    'prevMonth' => true,
                );
                $this->days[] = $dayAttributes;
            }
        }

        // Если последний день месяца не пятница, добавляем дни следующего месяца в конец массива
        if ($lastDayOfWeek != 5) {
            $nextMonthDays = 7 - $lastDayOfWeek;
            for ($i = 1; $i <= $nextMonthDays; $i++) {
                $dayAttributes = array(
                    'day' => $i,
                    // Добавляем атрибуты дня следующего месяца
                    'nextMonth' => true,
                );
                $this->days[] = $dayAttributes;
            }
        }
    }

    protected function checkDayAvailability($day)
    {
        // Здесь можно реализовать вашу логику проверки доступности дня
        // Например, можно вернуть true или false в зависимости от доступности дня
        return true;
    }
}
