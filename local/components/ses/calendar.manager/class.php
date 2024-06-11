<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use SES\CalendarManager\CalendarUsers;
use SES\CalendarManager\CalendarCourse;
use SES\CalendarManager\CalendarSchedule;
use SES\CalendarManager\Logger;
use Bitrix\Main\Type\DateTime;


class CalendarComponent extends \CBitrixComponent
{
    protected $prevMonth;
    protected $currentMonth;
    protected $nextMonth;
    protected $userRights;
    protected $currentYear;
    protected $selectDays;
    protected $HaveFilter = false;
    protected $days = [];
    protected $users = [];
    protected $course = [];
    protected $schedule = [];
    protected $userInfo = [];
    protected $DayAvailableList = [];
    protected $filter = [];
    protected $debug = [];

    /**
     * Загружает языковые файлы компонента.
     *
     * @return void
     */
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * Выполняет основной функционал компонента.
     *
     * @return void
     */
    public function executeComponent()
    {
        global $APPLICATION;
        if ($this->request->isAjaxRequest()) {
            $APPLICATION->RestartBuffer();
        }

        $this->includeComponentAssets(!$this->request->isAjaxRequest());

        if (!Loader::includeModule('ses.calendarmanager')) {
            $this->abortResultCache();
            ShowError(Loc::getMessage('CALENDAR_MODULE_NOT_INSTALLED'));
            return;
        }

        $this->selectDays = $this->arParams['SELECTION_DAYS'];
        if (!$this->checkAccess()) {
            return;
        }

        $this->checkAdminFilter();

        $monthId = $this->arParams['MONTH_ID'] ?? date('n');
        $year = $this->arParams['YEAR_ID'] ?? date('Y');


        $this->prepareData($monthId, $year);

        if ($this->request->isAjaxRequest()) {
            $this->processAjaxRequest();
            exit;
        }

        $this->includeComponentTemplate();
    }

    /**
     * Проверяет доступ текущего пользователя.
     *
     * @return bool
     */
    protected function checkAccess()
    {
        $userInfo = !empty($this->userInfo) ? $this->userInfo : $this->getUserInfo();

        if ($userInfo['ACCESS'] === 'N' && $this->selectDays != "temp2") {
            $this->AbortResultCache();
            $this->arResult['ERROR'][] = Loc::getMessage('NOT_AVAILABLE_RIGHTS', ['#TYPE#' => $userInfo['TYPE']]);
            $this->includeComponentTemplate();
            return false;
        }
        $this->setUserRights($userInfo['ROLE']);
        $this->setUserInfo($userInfo);

        $this->arResult['USER_INFO'] = $userInfo;
        return true;
    }

    public function checkAdminFilter()
    {
        if (isset($this->arParams['LECTOR_ID']) || isset($this->arParams['CITY_ID'])) {
            $this->setHaveFilter(true);

            if (isset($this->arParams['LECTOR_ID'])) {
                $this->setFilter('UF_LECTOR', $this->arParams['LECTOR_ID']);
            }

            if (isset($this->arParams['CITY_ID'])) {
                $this->setFilter('UF_CITY', $this->arParams['CITY_ID']);
            }
        }
    }

    public function setHaveFilter($value)
    {
        $this->HaveFilter = (bool)$value;
    }

    public function getHaveFilter()
    {
        return $this->HaveFilter;
    }

    public function setFilter($key, $value)
    {
        $this->filter[$key] = $value;
    }

    public function getFilter($key = null)
    {
        if ($key === null) {
            return $this->filter;
        }
        return $this->filter[$key] ?? null;
    }

    /**
     * Получает информацию о текущем пользователе.
     *
     * @return array
     */
    protected function getUserInfo()
    {
        $calendarUsers = new CalendarUsers();
        $userInfo = $calendarUsers->checkCurrentUserAccessAndRole();
        return $userInfo;
    }

    /**
     * Устанавливает информацию о пользователе.
     *
     * @param array $userInfo Информация о пользователе.
     * @return void
     */
    protected function setUserInfo($userInfo)
    {
       $this->userInfo = $userInfo;
    }

    /**
     * Проверяет доступность дня для редактирования.
     *
     * @param int $day День.
     * @param int $month Месяц.
     * @param int $year Год.
     * @param array $realDate Реальная текущая дата.
     * @return string
     */
    protected function checkDayAvailability($day, $month, $year, $realDate)
    {
        // Создаем объект DateTime для проверяемой даты
        $checkDate = new DateTime();
        $checkDate->setDate($year, $month, $day);
        $checkDate->setTime(0, 0, 0);

        // Создаем объект DateTime для реальной текущей даты из переданного массива
        $today = new DateTime();
        $today->setDate($realDate['year'], $realDate['month'], $realDate['day']);
        $today->setTime(0, 0, 0);
        
        // Проверяем, что дата не прошла и это текущий день или будущий
        if ($checkDate < $today) {
            return 'N'; // Дата прошла
        }

        // Теперь проверяем роль пользователя
        if ($this->userRights === 'Лектор') {
            return 'Y'; // Лектор может редактировать текущие и будущие дни
        } else {
            return 'N'; // Другие роли не могут редактировать дни
        }
    }

    /**
     * Обрабатывает AJAX-запросы.
     *
     * @return void
     */
    protected function processAjaxRequest()
    {
        $result = $this->arResult;
        echo json_encode($result);
    }
    
    /**
     * Получает базовый путь компонента.
     *
     * @return string
     */
    private function getComponentBasePath()
    {
        $classFilePath = str_replace('\\', '/', __FILE__);
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        $relativePath = str_replace($documentRoot, '', $classFilePath);
        $basePath = dirname($relativePath);

        return $basePath;
    }

    /**
     * Получает путь к AJAX-обработчику.
     *
     * @return string
     */
    public function getAjaxPath()
    {
        $basePath = $this->getComponentBasePath();
        $ajaxPath = $basePath . '/ajax.php';
        return $ajaxPath;
    }

    /**
     * Получает путь к шаблону компонента.
     *
     * @return string
     */
    public function customGetTemplatePath()
    {
        $basePath = $this->getComponentBasePath();
        $templatePath = $basePath . '/templates/' . $this->arParams['COMPONENT_TEMPLATE'];
        return $templatePath;
    }

    /**
     * Подключает ресурсы компонента (CSS и JS).
     *
     * @return void
     */
    protected function includeComponentAssets()
    {
        $basePath = $this->getComponentBasePath();
        $templateName = $this->arParams['COMPONENT_TEMPLATE'];
        if (empty($templateName)) $templateName = '.default';

        // Пути к ресурсам могут зависеть от шаблона
        $cssPath = $_SERVER['DOCUMENT_ROOT'] . $basePath . '/templates/' . $templateName . '/css/';
        $cssFiles = glob($cssPath . '*.css');
        foreach ($cssFiles as $cssFile) {
            // Добавляем CSS файлы через объект Asset
            \Bitrix\Main\Page\Asset::getInstance()->addCss($basePath . '/templates/' . $templateName . '/css/' . basename($cssFile));
        }

        $jsPath = $_SERVER['DOCUMENT_ROOT'] . $basePath . '/templates/' . $templateName . '/js/';
        $jsFiles = glob($jsPath . '*.js');
        foreach ($jsFiles as $jsFile) {
            // Добавляем JS файлы через объект Asset
            \Bitrix\Main\Page\Asset::getInstance()->addJs($basePath . '/templates/' . $templateName . '/js/' . basename($jsFile));
        }
    }

    /**
     * Подготавливает данные для компонента.
     *
     * @param int $monthId Идентификатор месяца.
     * @param int $year Год.
     * @return void
     */
    protected function prepareData($monthId, $year)
    {
        $this->setCurrentMonth($monthId);
        $this->setCurrentYear($year);

        // if ($this->userRights === 'Администратор') {
            $this->fillUsers();
        // }

        // Эти методы вызываются для обоих типов пользователей
        $this->fillAvailableList();//Значения для доступности дня
        $this->fillMonth();
        $this->getCourseCurMonth();
        if ($this->selectDays != 'temp2') {
            $this->fillSchedule();
        }
        $this->fillDays();
        $this->fillArResult();
    }


    /**
     * Заполняет информацию о текущем, предыдущем и следующем месяцах.
     *
     * @return void
     */
    protected function fillAvailableList()
    {
        $CalendarSchedule = new CalendarSchedule;
        $list = $CalendarSchedule->getDayAvailableList();
        $this->setDayAvailableList($list);
    }    

    /**
     * Заполняет информацию о текущем, предыдущем и следующем месяцах.
     *
     * @return void
     */
    protected function fillMonth()
    {
        $currentMonthNum = $this->getCurrentMonth()['number'];

        $prevMonthNum = $currentMonthNum == 1 ? 12 : $currentMonthNum - 1;
        $nextMonthNum = $currentMonthNum == 12 ? 1 : $currentMonthNum + 1;

        $this->setPrevMonth($prevMonthNum);
        $this->setNextMonth($nextMonthNum);
    }

    /**
     * Получает курсы текущего месяца для лектора.
     *
     * @return void
     */
    protected function getCourseCurMonth()
    {
        $CalendarCourse = new CalendarCourse;
        $userInfo = !empty($this->userInfo) ? $this->userInfo : $this->getUserInfo();
        $month = $this->getCurrentMonth()['number'];
        $year = $this->getCurrentYear();

        $usrRights = $this->getUserRights();
        if($this->selectDays == 'temp1'){
            $ModuleUserID = $usrRights == 'Администратор' ? '' : $this->userInfo['MODULE_ID']; // если админ передаем пустоту, если лектор - его ID
        }else{
            $ModuleUserID = '';
        }
        $filters = [];
        if ($this->getHaveFilter()) {
            $filters = $this->getFilter();
        }
        if($this->selectDays == 'temp1'){
            $formattedMonth = sprintf("%02d", $month);
            $startDate = new \Bitrix\Main\Type\DateTime("$year-$formattedMonth-01 00:00:00", "Y-m-d H:i:s");
            $endDate = new \Bitrix\Main\Type\DateTime("$year-$formattedMonth-" . date('t', strtotime("$year-$formattedMonth-01")) . " 23:59:59", "Y-m-d H:i:s");
        }else if($this->selectDays == 'temp2'){
            $startDate = new DateTime();
            $startDateFormatted = $startDate->format("d.m.Y H:i:s");
            $nativeEndDate = new \DateTime($startDateFormatted);
            $nativeEndDate->add(new \DateInterval('P1M'));
            $endDateFormatted = $nativeEndDate->format("d.m.Y H:i:s");
            $startDate = new DateTime($startDateFormatted, "d.m.Y H:i:s");
            $endDate = new DateTime($endDateFormatted, "d.m.Y H:i:s");
            $dateFilt = array($startDate->format("d.m.Y H:i:s"), $endDate->format("d.m.Y H:i:s"));

        }


        // Проверка наличия фильтров
        if (!empty($filters)) {
            $result = $CalendarCourse->getEventsByLectorAndDate($ModuleUserID, $startDate, $endDate, $filters);
        } else {
            $result = $CalendarCourse->getEventsByLectorAndDate($ModuleUserID,  $startDate, $endDate);
        }

        $this->arResult['debug']['arParams'] = $this->arParams;
        if ($result['success']) {
            $this->course = $result['data'];
        } else {
            // Логирование ошибки с использованием вашего класса Logger
            Logger::log('CalendarCourse', 'getEventsByLectorAndDate', $result['error']);
        }
    }

    /**
     * Заполняет расписание.
     *
     * @return void
     */
    protected function fillSchedule()
    {
        $currentMonthNum = $this->getCurrentMonth()['number'];
        $currentYear = $this->getCurrentYear();
        $schedule = new CalendarSchedule();

        $usrRights = $this->getUserRights();
        $ModuleUserID = $usrRights == 'Администратор' ? '' : $this->userInfo['MODULE_ID']; //если админ передаем пустоту(фильтр без лектора), если лектор передаем его ID

        $filters = [];
        if ($this->getHaveFilter()) {
            $filters = $this->getFilter();
        }

        if (!empty($filters)) {
            $result = $schedule->getEventsByLectorAndDate($ModuleUserID, $currentMonthNum, $currentYear, $filters);
        } else {
            $result = $schedule->getEventsByLectorAndDate($ModuleUserID, $currentMonthNum, $currentYear);
        }

        if ($result['success']) {
            $this->schedule =  $result['data'];
        }else{
            // Логирование ошибки с использованием вашего класса Logger
            Logger::log('CalendarSchedule', 'getEventsByLectorAndDate', $result['error']);
        }
    }

    /**
     * Ищет курсы по дате.
     *
     * @param \Bitrix\Main\Type\Date $date Дата.
     * @return array
     */
    protected function findCoursesByDate($date)
    {
        $coursesForDate = [];

        foreach ($this->course as $course) { // Предполагается, что $this->course содержит все курсы
            $courseDate = $course['UF_DATE']->format('Y-m-d');
            $formattedDate = $date->format('Y-m-d');

            if ($courseDate === $formattedDate) {
                $course["UF_DATE"] = $this->parseDateTimeCustom($course["UF_DATE"]);
                $course["UF_DATE_END"] = $this->parseDateTimeCustom($course["UF_DATE_END"]);
                $coursesForDate[] = $course; // Добавляем курс в массив
            }
        }

        return $coursesForDate;
    }

    protected function parseDateTimeCustom($arDate) {
        // Проверяем, является ли $arDate объектом Bitrix\Main\Type\DateTime
        if ($arDate instanceof \Bitrix\Main\Type\DateTime) {
            // Извлекаем значение даты и времени в формате строки
            $dateString = $arDate->format("d.m.Y H:i:s");

            // Разделяем строку на дату и время
            $date = $arDate->format('d.m.Y'); // Формат даты: 29.05.2024
            $time = $arDate->format('H:i');   // Формат времени: 02:30

            // Возвращаем массив с датой и временем
            return $dateString;
        } else {
            // Обрабатываем ошибку или возвращаем значение по умолчанию
            return [
                'date' => null,
                'time' => null
            ];
        }
    }


    /**
     * Ищет расписание по дате.
     *
     * @param \Bitrix\Main\Type\Date $date Дата.
     * @return array
     */
    protected function findScheduleByDate($date)
    {
        $scheduleForDate = [];

        foreach ($this->schedule as $schedule) {
            $scheduleDate = $schedule['UF_DATE']->format('Y-m-d');
            $formattedDate = $date->format('Y-m-d');
            $usrRights = $this->getUserRights();
            if ($scheduleDate === $formattedDate) {
                $scheduleAccess = new CalendarSchedule();
                //реализация для лектора была изначально
                // $scheduleForDate = $scheduleAccess->getEnumValueById("UF_DAY_AVAILABLE", $schedule['UF_DAY_AVAILABLE']); // Добавляем курс в массив
                //  if ($scheduleDate === $formattedDate) {
                //     $scheduleAccess = new CalendarSchedule();
                //     $usrRights = $this->getUserRights();
                //     if($usrRights == 'Лектор'){
                //         $scheduleForDate = $scheduleAccess->getEnumValueById("UF_DAY_AVAILABLE", $schedule['UF_DAY_AVAILABLE']); // Добавляем курс в массив     
                //     }else if($usrRights == 'Администратор'){
                //         $availbable = $scheduleAccess->getEnumValueById("UF_DAY_AVAILABLE", $schedule['UF_DAY_AVAILABLE']);
                //         if($availbable == 'Y'){
                //             $scheduleForDate[] = $schedule;
                //         }
                //     }      
                // }
                $availbable = $scheduleAccess->getEnumValueById("UF_DAY_AVAILABLE", $schedule['UF_DAY_AVAILABLE']);
                if($usrRights == 'Администратор'){
                    if($availbable == 'Y'){
                        $scheduleForDate[] = $schedule;
                    }
                }else if($usrRights == 'Лектор'){
                     if($availbable == 'Y' || $availbable == 'N'){
                        $scheduleForDate[] = $schedule;
                    }
                }
            }
        }

        return $scheduleForDate;
    }

    /**
     * Заполняет список пользователей.
     *
     * @return void
     */
    protected function fillUsers()
    {
        $CalendarUsers = new CalendarUsers;
        $this->users['ADMIN'] = $CalendarUsers->getAdminUsers();
        $this->users['LECTOR'] = $CalendarUsers->getLecturerUsers();
    }

    /**
     * Заполняет дни.
     *
     * @return void
     */
    protected function fillDays()
    {
        if ($this->selectDays == 'temp1') {
            $this->fillPreviousMonthDays();
            $this->fillCurrentMonthDays();
            $this->fillNextMonthDays();
            
        } else if ($this->selectDays == 'temp2'){
            $this->fillDaysForCurrentDatePlusMonth();
        }
    }

    /**
     * Заполняет дни для текущей даты + 1 месяц.
     *
     * @return void
     */
    protected function fillDaysForCurrentDatePlusMonth()
    {
        $currentDate = new \Bitrix\Main\Type\DateTime();
        $endDate = new \Bitrix\Main\Type\DateTime();
        $endDate->add("1M");

        // Получаем текущую дату и дату через месяц
        $startTimestamp = $currentDate->getTimestamp();
        $endTimestamp = $endDate->getTimestamp();

        while ($startTimestamp <= $endTimestamp) {
            $date = \Bitrix\Main\Type\DateTime::createFromTimestamp($startTimestamp);
            $day = $date->format('j'); // День месяца без ведущих нулей
            $month = $date->format('n'); // Месяц без ведущих нулей
            $year = $date->format('Y'); // Год
            $courses = $this->findCoursesByDate($date); // Получаем курсы для текущего дня

            if (!empty($courses)) {
                $accessEdit = 'N';
                $dayAttributes = [
                    'day' => $day,
                    'availability' => true,
                    'accessEdit' => $accessEdit,
                    'month' => $month,
                    'courses' => !empty($courses) ? $courses : false, // Заполняем массив ID курсов
                ];

                $this->days[] = $dayAttributes;
            }

            // Переходим к следующему дню
            $startTimestamp = strtotime('+1 day', $startTimestamp);
        }
    }

    /**
     * Заполняет дни предыдущего месяца.
     *
     * @return void
     */
    protected function fillPreviousMonthDays()
    {
        $prevMonthNum = $this->getPrevMonth()['number'];
        $currentMonthNum = $this->getCurrentMonth()['number'];

        // Определяем первый день текущего месяца
        $firstDayOfMonth = mktime(0, 0, 0, $currentMonthNum, 1, $this->currentYear);
        
        // Определяем день недели первого дня месяца
        $firstDayOfWeek = date('N', $firstDayOfMonth);

        // Если первый день месяца не понедельник, добавляем дни предыдущего месяца в начало массива
        if ($firstDayOfWeek != 1) {
            // Определяем количество дней в предыдущем месяце
            $prevMonthDays = date('t', mktime(0, 0, 0, $prevMonthNum, 1, $this->currentYear));
            $realCurDate = $this->getCurrentDateInfo();
            // Добавляем дни предыдущего месяца в начало массива
            for ($i = $firstDayOfWeek - 1; $i >= 1; $i--) {
                $dayAttributes = [
                    'day' => $prevMonthDays - ($i - 1),
                    'prevMonth' => true,
                    'availability' => false,
                    'accessEdit' => "N",
                ];
                $this->days[] = $dayAttributes;
            }
        }
    }

    /**
     * Заполняет дни текущего месяца.
     *
     * @return void
     */
    protected function fillCurrentMonthDays()
    {
        $currentMonthNum = $this->getCurrentMonth()['number'];
        $currentYear = $this->getCurrentYear();

        // Определяем количество дней в текущем месяце
        $daysInMonth = date('t', mktime(0, 0, 0, $currentMonthNum, 1, $this->currentYear));
        $realCurDate = $this->getCurrentDateInfo();

        // Заполняем массив дней текущего месяца
        for ($i = 1; $i <= $daysInMonth; $i++) {
            // Форматируем день с ведущими нулями
            $formattedDay = sprintf("%02d", $i);
            $date = new \Bitrix\Main\Type\Date("$currentYear-$currentMonthNum-$formattedDay", "Y-m-d");
            $courses = $this->findCoursesByDate($date); // Получаем курсы для текущего дня
            if(empty($courses)){
                $accessEdit = $this->checkDayAvailability($i, $currentMonthNum, $this->currentYear, $realCurDate);
            }else{
                $accessEdit = 'N';
            }

            $usrRights = $this->getUserRights();
            if (empty($courses) || $usrRights == 'Администратор') {
                $schedule = $this->findScheduleByDate($date);
            }

            $dayAttributes = [
                'day' => $i,
                'availability' => true,
                'accessEdit' => $accessEdit,
                'courses' => !empty($courses) ? $courses : false, // Заполняем массив ID курсов
                'schedule' => !empty($schedule) ? $schedule : false, // Заполняем массив ID курсов
            ];

            $this->days[] = $dayAttributes;
        }
    }

    /**
     * Заполняет дни следующего месяца.
     *
     * @return void
     */
    protected function fillNextMonthDays()
    {
        $currentMonthNum = $this->getCurrentMonth()['number'];
        $nextMonthNum = $this->getNextMonth()['number'];

        // Определяем последний день текущего месяца
        $lastDayOfMonth = mktime(0, 0, 0, $currentMonthNum, date('t', mktime(0, 0, 0, $currentMonthNum, 1, $this->currentYear)), $this->currentYear);
        
        // Определяем день недели последнего дня месяца
        $lastDayOfWeek = date('N', $lastDayOfMonth);

        // Если последний день месяца не воскресенье, добавляем дни следующего месяца в конец массива
        if ($lastDayOfWeek != 7) {
            // Определяем первый день следующего месяца
            $firstDayOfNextMonth = mktime(0, 0, 0, $nextMonthNum, 1, $this->currentYear);
            $realCurDate = $this->getCurrentDateInfo();
            // Добавляем дни следующего месяца в конец массива
            for ($i = 1; $i <= (7 - $lastDayOfWeek); $i++) {
                $dayAttributes = [
                    'day' => $i,
                    'nextMonth' => true,
                    'availability' => false,
                    'accessEdit' => "N",
                ];
                $this->days[] = $dayAttributes;
            }
        }
    }

    /**
     * Заполняет массив arResult.
     *
     * @return void
     */
    protected function fillArResult()
    {
        if (!empty($this->users['LECTOR'])) {
            $this->arResult['LECTOR'] = $this->users['LECTOR'];
        }

        if (!empty($this->DayAvailableList)) {
            $this->arResult['DayAvailableList'] = $this->DayAvailableList;
        }
        $this->arResult['haveFilter'] = $this->getHaveFilter();
        $this->arResult['MONTH'] = [
            'prevMonth' => $this->getPrevMonth(),
            'currentMonth' => $this->getCurrentMonth(),
            'nextMonth' => $this->getNextMonth()
        ];

        if (!empty($this->course)) {
            $this->arResult['COURSE'] = $this->course;
        }

        if (!empty($this->days)) {
            if ($this->selectDays == 'temp2' &&  $this->arParams["IS_AJAX"] == true){
                $groupedByMonth = [];
                foreach ($this->days as $day) {
                    $month = $day['month'];
                    
                    // Преобразование объектов \Bitrix\Main\Type\DateTime в строки
                    if ($day['UF_DATE'] instanceof \Bitrix\Main\Type\DateTime) {
                        $day['UF_DATE'] = $day['UF_DATE']->format('Y-m-d');
                    }
                    if ($day['UF_DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime) {
                        $day['UF_DATE_CREATE'] = $day['UF_DATE_CREATE']->format('Y-m-d');
                    }
                    if ($day['UF_DATE_END'] instanceof \Bitrix\Main\Type\DateTime) {
                        $day['UF_DATE_END'] = $day['UF_DATE_END']->format('Y-m-d');
                    }

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
                $this->arResult['DAYS'] = $groupedByMonth;

            }else{
                $this->arResult['DAYS'] = $this->days;
            }
        }else{
     
            $this->arResult['DAYS'] = serialize($this->arParams);
        }

        if (!empty($this->currentYear)) {
            $this->arResult['YEAR'] = $this->currentYear;
        }

        if (!empty($this->schedule)) {
            $this->arResult['SCHEDULE'] = $this->schedule;
        }

        $this->arResult['AJAX_URL'] = $this->getAjaxPath();
        $this->arResult['TEMPLATE_PATH'] = $this->customGetTemplatePath();

    }

    /**
     * Возвращает предыдущий месяц.
     *
     * @return array
     */
    protected function getPrevMonth()
    {
        return $this->prevMonth;
    }

    /**
     * Возвращает текущий месяц.
     *
     * @return array
     */
    protected function getCurrentMonth()
    {
        return $this->currentMonth;
    }

    /**
     * Возвращает следующий месяц.
     *
     * @return array
     */
    protected function getNextMonth()
    {
        return $this->nextMonth;
    }

    /**
     * Возвращает права пользователя.
     *
     * @return string
     */
    protected function getUserRights()
    {
        return $this->userRights;
    }

    /**
     * Возвращает текущую дату.
     *
     * @return array
     */
    protected function getCurrentDateInfo()
    {
        $currentDay = date('j');
        $currentMonth = date('n');  // n - номер месяца без ведущих нулей (1 до 12)
        $currentYear = date('Y');   // Y - год в четырехзначном формате

        return [
            'day' => $currentDay,
            'month' => $currentMonth,
            'year' => $currentYear
        ];
    }


    /**
     * Устанавливает список значений доступности дней.
     *
     * @param string $list список значений.
     * @return void
     */
    protected function setDayAvailableList($list)
    {
        $this->DayAvailableList = $list;
    }

    /**
     * Устанавливает права пользователя.
     *
     * @param string $right Права пользователя.
     * @return void
     */
    protected function setUserRights($right)
    {
        $this->userRights = $right;
    }

    /**
     * Устанавливает текущий месяц.
     *
     * @param int $monthNum Номер месяца.
     * @return void
     */
    protected function setCurrentMonth($monthNum)
    {
        $this->currentMonth = [
            'number' => $monthNum,
            'name' => $this->getMonthName($monthNum)
        ];
    }

    /**
     * Устанавливает предыдущий месяц.
     *
     * @param int $monthNum Номер месяца.
     * @return void
     */
    protected function setPrevMonth($monthNum)
    {
        $this->prevMonth = [
            'number' => $monthNum,
            'name' => $this->getMonthName($monthNum)
        ];
    }

    /**
     * Устанавливает текущий год.
     *
     * @param int $year Год.
     * @return void
     */
    protected function setCurrentYear($year)
    {
        $this->currentYear = $year;
    }

    /**
     * Возвращает текущий год.
     *
     * @return int
     */
    protected function getCurrentYear()
    {
        return $this->currentYear;
    }

    /**
     * Устанавливает следующий месяц.
     *
     * @param int $monthNum Номер месяца.
     * @return void
     */
    protected function setNextMonth($monthNum)
    {
        $this->nextMonth = [
            'number' => $monthNum,
            'name' => $this->getMonthName($monthNum)
        ];
    }

    /**
     * Возвращает название месяца по его номеру.
     *
     * @param int $monthNum Номер месяца.
     * @return string
     */
    protected function getMonthName($monthNum)
    {
        $months = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март',
            4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь',
            10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        return $months[$monthNum] ?? 'Неизвестный месяц';
    }
}
