<?php

namespace SES\CalendarManager;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use SES\CalendarManager\CalendarSchedule;
use SES\CalendarManager\CalendarUsers;
use SES\CalendarManager\Geo\CityTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\ContactTable;
use Bitrix\Crm\FieldMultiTable;
use SES\CalendarManager\Logger;

class CalendarCourse
{
    private $hlblockId;

    /**
     * Конструктор класса.
     * Инициализирует идентификатор highload блока и загружает модули.
     *
     * @throws \Exception Если не удалось подключить модули highloadblock или iblock.
     */
    public function __construct()
    {
        $this->hlblockId = (int)Option::get('ses.calendarmanager', 'ID_COURSES_HL');
        try {
            Loader::includeModule('highloadblock');
            Loader::includeModule('iblock');
        } catch (LoaderException $e) {
            throw new \Exception("Ошибка подключения модуля highloadblock или iblock: " . $e->getMessage());
        }
    }

    /**
     * Получает класс данных сущности highload блока.
     *
     * @return string|null Класс данных или null, если не удалось получить сущность.
     */
    private function getEntity()
    {
        $hlblock = HL\HighloadBlockTable::getById($this->hlblockId)->fetch();
        if ($hlblock) {
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            return $entity->getDataClass();
        }
        return null;
    }

    public function getCourseByLink($hash, $type)
    {
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return null;
        }

        $link = ($type === 'internal') ? '/internal/?form_hash=' . $hash : '/external/?form_hash=' . $hash;
        $filterField = ($type === 'internal') ? 'UF_LINK_INTERNAL' : 'UF_LINK_EXTERNAL';

        $result = $dataClass::getList([
            'select' => ['*'],
            'filter' => [$filterField => $link],
            'limit' => 1
        ]);

        return $result->fetch();
    }

    /**
     * Получает события по лектору и дате.
     *
     * @param int $lectorId Идентификатор лектора.
     * @param int $month Месяц.
     * @param int $year Год.
     * @return array Массив с результатом выполнения и данными событий.
     */
    public function getEventsByLectorAndDate($lectorId = '', $startDate = '', $endDate = '', $filters = [])
    {
        // Проверка переданных значений
        if (empty($startDate) || empty($endDate)) {
            return [
                'success' => false,
                'error' => "Пустые значения дат."
            ];
        }

        // Преобразование дат в формат Bitrix
        //$startDate1 = new \Bitrix\Main\Type\DateTime($startDate, "d.m.Y H:i:s");
        //$endDate1 = new \Bitrix\Main\Type\DateTime($endDate, "d.m.Y H:i:s");

        // Получение класса данных highload блока
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return [
                'success' => false,
                'error' => "Не удалось получить класс данных для highload блока."
            ];
        }

        // Формирование фильтра для выборки событий
        if(!empty($startDate) && !empty($endDate)){
            if ($startDate instanceof DateTime && $endDate instanceof DateTime) {
                $filter = [
                    '>=UF_DATE' => $startDate->format("d.m.Y H:i:s"),
                    '<=UF_DATE' => $endDate->format("d.m.Y H:i:s"),
                ];
            }
        }

        if (!empty($lectorId)) {
            $filter['UF_LECTOR'] = $lectorId;
        }

        if (!empty($filters['UF_TYPE']) && is_array($filters['UF_TYPE']) && !isset($filters["UF_THIS_LOC_DENT"])) {
            $filter['UF_TYPE'] = $filters['UF_TYPE'];
        }

        if(!empty($filters["UF_THIS_LOC_DENT"])){
            unset($filters['UF_TYPE']);
        }

        if (!empty($filters)) {
            if ($this->arrayKeyExistsRecursive('>=UF_DATE', $filters)) {
                unset($filter['>=UF_DATE']);
            }
            if($this->arrayKeyExistsRecursive('<=UF_DATE', $filters)){
                unset($filter['<=UF_DATE']);
            }
            if($this->arrayKeyExistsRecursive('UF_ROLE', $filters)){
                unset($filters['UF_ROLE']);
            }
            $filter = array_merge($filter, $filters);
        }

        // Параметры выборки событий
        $parameters = [
            'select' => ['*'],
            'order' => ['ID' => 'ASC', 'UF_SORT' => 'ASC'],
            'filter' => $filter
        ];

        // Выполнение выборки событий
        $result = $dataClass::getList($parameters);
        $events = [];
        while ($event = $result->fetch()) {
            $events[$event['ID']] = $event;
            $CUser = new CalendarUsers();
            $UserArr = $CUser->getCurUserModuleAr($event['UF_LECTOR']);
            $events[$event['ID']]["LECTOR_NAME"] = $UserArr["UF_FIRST_NAME"] . " " . $UserArr["UF_LAST_NAME"];
        }
        Logger::log('CalendarCourse', 'startDate',  print_r($startDate, true));
        Logger::log('CalendarCourse', 'endDate', print_r($endDate, true));
        // Возврат результатов
        return [
            'success' => true,
            'data' => $events,
            'return_startDate' => $startDate,
            'return_endDate' => $endDate,
            'return_filter' => $filter,
            'get_filter' => $filters
        ];
    }

    private function arrayKeyExistsRecursive($key, $array) {
        // Проверяем, если ключ существует в текущем уровне массива
        if (array_key_exists($key, $array)) {
            return true;
        }

        // Проходим по элементам массива
        foreach ($array as $value) {
            // Если значение также является массивом, вызываем функцию рекурсивно
            if (is_array($value) && $this->arrayKeyExistsRecursive($key, $value)) {
                return true;
            }
        }

        // Если ключ не найден ни на одном уровне вложенности
        return false;
    }

    public function saveCourse($data)
    {
        try {
            $dataClass = $this->getEntity();
            if (!$dataClass) {
                throw new SystemException("Не удалось получить класс данных для highload блока.");
            }

            $id = isset($data['ID']) ? (int)$data['ID'] : 0;

            if (empty($data['UF_DATE']) || empty($data['UF_DATE_END'])) {
                throw new ArgumentException("Поля даты не должны быть пустыми.");
            }

            try {
                $courseDate = new DateTime($data['UF_DATE'], 'd.m.Y H:i:s');
                $courseDateEnd = new DateTime($data['UF_DATE_END'], 'd.m.Y H:i:s');
            } catch (\Bitrix\Main\ObjectException $e) {
                throw new ArgumentException("Некорректная дата или время: " . $e->getMessage());
            }

            $fields = [
                'UF_NAME' => $data['UF_NAME'],
                'UF_TYPE' => $data['UF_TYPE'],
                'UF_CITY' => $data['UF_CITY'],
                'UF_DATE' => $courseDate,
                'UF_DATE_END' => $courseDateEnd,
                'UF_LOCATION' => $data['UF_LOCATION'],
                'UF_ADDRESS' => $data['UF_ADDRESS'],
                'UF_TICKETS' => $data['UF_TICKETS'],
                'UF_TICKETS_BASE' => $data['UF_TICKETS'],
                'UF_DESCRIPTION' => $data['UF_DESCRIPTION'],
                'UF_LECTOR' => $data['UF_LECTOR'],
                'UF_PRICE' => $data['UF_PRICE'],
                'UF_THEME' => !empty($data['UF_THEME']) ? $data['UF_THEME'] : '',
                'UF_SPEC' => !empty($data['UF_SPEC']) ? $data['UF_SPEC'] : "",
                'UF_THIS_LOC_DENT' => !empty($data['UF_THIS_LOC_DENT']) ? $data['UF_THIS_LOC_DENT'] : 0,
                'UF_SORT' => 100,
            ];

            if ($id > 0) {
                // Обновление курса
                $result = $dataClass::update($id, $fields);

                if ($result->isSuccess()) {
                    // Получение существующих данных курса
                    $courseData = $dataClass::getById($id)->fetch();
                    if (!$courseData || !isset($courseData['UF_CRM_EVENT_CALENDAR'])) {
                        throw new SystemException('Не удалось найти существующую запись курса или календарного события.');
                    }

                    $eventId = $courseData['UF_CRM_EVENT_CALENDAR'];

                    // Обновление события в календаре CRM
                    $calendarEventResult = $this->updateCalendarEvent($eventId, $data, $courseDate);
                    if ($calendarEventResult['success']) {
                        return [
                            'success' => true,
                            'id' => $id,
                            'calendar_event_id' => $eventId
                        ];
                    } else {
                        throw new SystemException($calendarEventResult['error']);
                    }
                } else {
                    throw new SystemException(implode(', ', $result->getErrorMessages()));
                }
            } else {
                // Создание события в календаре CRM
                $calendarEventResult = $this->createCalendarEvent($data, $courseDate);
                if ($calendarEventResult['success'] || $calendarEventResult['create_without']) {
                    $fields['UF_CRM_EVENT_CALENDAR'] = $calendarEventResult['event_id'] ? $calendarEventResult['event_id'] : '';
                    $fields['UF_DATE_CREATE'] = new DateTime();
                    $fields['UF_CREATOR'] = $data['UF_CREATOR'];
                    $fields['UF_LINK_EXTERNAL'] = '/external/?form_hash=' . bin2hex(random_bytes(5));
                    $fields['UF_LINK_INTERNAL'] = '/internal/?form_hash=' . bin2hex(random_bytes(5));
                    $result = $dataClass::add($fields);

                    if ($result->isSuccess()) {
                        $schedule = new CalendarSchedule();
                        $formattedDate = $courseDate->format('Y-m-d');
                        $updateResult = $schedule->updateSchedule($data['UF_LECTOR'], $formattedDate, 'D');
                        if (!$updateResult['success'] && !$updateResult['exception']) {
                            \CCalendar::DeleteEvent($calendarEventResult['event_id']);
                            throw new SystemException($updateResult['error']);
                        } else if($updateResult['exception']){
                            return [
                                'success' => true,
                                'id' => $result->getId(),
                                'calendar_event_id' => $calendarEventResult['event_id'] ? $calendarEventResult['event_id'] : '',
                                'swap_schedule' => false
                            ];
                        }else {
                            return [
                                'success' => true,
                                'id' => $result->getId(),
                                'calendar_event_id' => $calendarEventResult['event_id'] ? $calendarEventResult['event_id'] : ''
                            ];
                        }
                    } else {
                        // Удаление созданного события календаря, если создание курса не удалось
                        \CCalendar::DeleteEvent($calendarEventResult['event_id']);
                        throw new SystemException(implode(', ', $result->getErrorMessages()));
                    }
                } else {
                    throw new SystemException($calendarEventResult['error']);
                }
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function createCalendarEvent($data, $courseDate)
    {
        if (Loader::includeModule('calendar')) {
            global $USER;
            $userId = $USER->GetID();

            try {
                $courseDate = new DateTime($data['UF_DATE'], 'd.m.Y H:i:s');
                $courseDateEnd = new DateTime($data['UF_DATE_END'], 'd.m.Y H:i:s');
            } catch (\Bitrix\Main\ObjectException $e) {
                throw new ArgumentException("Некорректная дата или время: " . $e->getMessage());
            }

            $CM = new CalendarUsers();
            $userAr = $CM->getCurUserModuleAr($data['UF_LECTOR']);
            $bx_ID_lector = $userAr;

            $cityQuery = CityTable::getList([
                'filter' => ['ID' => $data['UF_CITY']],
                'select' => ['ID', 'NAME', 'REGION_ID', 'REGION_NAME' => 'REGION.NAME'],
            ]);

            if ($city = $cityQuery->fetch()) {
                $cityData = [
                    'ID' => $city['ID'],
                    'NAME' => $city['NAME'],
                    'REGION_ID' => $city['REGION_ID'],
                    'REGION_NAME' => $city['REGION_NAME'],
                ];
            }

            $dateFromTsUtc = $courseDate->getTimestamp();
            $dateToTsUtc = $courseDateEnd->getTimestamp();
            $eventFields = [
                'CAL_TYPE' => 'company_calendar',
                'OWNER_ID' => 0,
                'CREATED_BY' => 4035, //$userId
                'NAME' => $data['UF_NAME'] . ' / ' . $userAr['UF_LAST_NAME'] . ' ' . $userAr['UF_FIRST_NAME'] . ' / ' . $cityData['NAME'],
                'DESCRIPTION' => $data['UF_DESCRIPTION'],
                'DATE_FROM' => $courseDate,
                'DATE_TO' => $courseDateEnd,
                'DATE_FROM_TS_UTC' => $dateFromTsUtc,
                'DATE_TO_TS_UTC' => $dateToTsUtc,
                'LOCATION' =>  $data['UF_LOCATION'] . " " . $data['UF_ADDRESS'],
                'SECTIONS' => [5],
                'ACCESSIBILITY' => 'busy',
                'IMPORTANCE' => 'normal',
                'SKIP_TIME' => 'N',
                'VERSION' => 1,
                'MEETING_STATUS' => 'H',
                'DATE_CREATE' => $courseDate,
                'TIMESTAMP_X' =>  $courseDate,
                'DT_SKIP_TIME' => 'Y',
                'DT_LENGTH' => 86400,
                'ATTENDEES_CODES' => 'U'. 4035, //$bx_ID_lector['UF_USER_ID']
            ];

            $eventId = \CCalendar::SaveEvent(['arFields' => $eventFields, 'autoDetectSection' => true]);
            
            if ($eventId) {
                return [
                    'success' => true,
                    'event_id' => $eventId
                ];
            } else {
                return [
                    'success' => false,
                    'create_without' => true,
                    'error' => 'Ошибка при создании события в календаре'
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'Модуль календаря не подключен.'
            ];
        }
    }

    public function updateCalendarEvent($eventId, $data, $courseDate)
    {
        if (Loader::includeModule('calendar')) {
            // Форматирование даты и времени
            try {
                $courseDate = new DateTime($data['UF_DATE'], 'd.m.Y H:i:s');
                $courseDateEnd = new DateTime($data['UF_DATE_END'], 'd.m.Y H:i:s');
            } catch (\Bitrix\Main\ObjectException $e) {
                throw new ArgumentException("Некорректная дата или время: " . $e->getMessage());
            }
            $CM = new CalendarUsers();
            $userAr = $CM->getCurUserModuleAr($data['UF_LECTOR']);
            $bx_ID_lector = $userAr;

            $cityQuery = CityTable::getList([
                'filter' => ['ID' => $data['UF_CITY']],
                'select' => ['ID', 'NAME', 'REGION_ID', 'REGION_NAME' => 'REGION.NAME'],
            ]);

            if ($city = $cityQuery->fetch()) {
                $cityData = [
                    'ID' => $city['ID'],
                    'NAME' => $city['NAME'],
                    'REGION_ID' => $city['REGION_ID'],
                    'REGION_NAME' => $city['REGION_NAME'],
                ];
            }
            // Convert date and time to Unix timestamps
            $dateFromTsUtc = $courseDate->getTimestamp();
            $dateToTsUtc = $courseDateEnd->getTimestamp();
            $eventFields = [
                'ID' => $eventId,
                'CAL_TYPE' => 'company_calendar',
                'OWNER_ID' => 0,
                'NAME' => $data['UF_NAME'] . ' / ' . $userAr['UF_LAST_NAME'] . ' ' . $userAr['UF_FIRST_NAME'] . ' / ' . $cityData['NAME'],
                'DESCRIPTION' => $data['UF_DESCRIPTION'],
                'DATE_FROM' => $courseDate,
                'DATE_TO' => $courseDateEnd,
                'DATE_FROM_TS_UTC' => $dateFromTsUtc,
                'DATE_TO_TS_UTC' => $dateToTsUtc,
                'LOCATION' => $data['UF_LOCATION'] . " " . $data['UF_ADDRESS'],
                'SECTIONS' => [5],
                'ACCESSIBILITY' => 'busy',
                'IMPORTANCE' => 'normal',
                'SKIP_TIME' => 'N',
                'VERSION' => 1,
                'MEETING_STATUS' => 'H',
                'TIMESTAMP_X' =>  $courseDate,
                'DT_SKIP_TIME' => 'Y',
                'DT_LENGTH' => 86400,
                'ATTENDEES_CODES' => 'U'.$bx_ID_lector['UF_USER_ID'],
            ];

            $eventId = \CCalendar::SaveEvent(['arFields' => $eventFields, 'autoDetectSection' => true]);
            if ($eventId) {
                return [
                    'success' => true,
                    'event_id' => $eventId
                ];
            } else {
                return [
                    'success' => false,
                    'create_without' => true,
                    'error' => 'Ошибка при обновлении события в календаре:'
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'Модуль календаря не подключен.'
            ];
        }
    }


    public function updateCourseTickets($courseId, $newTicketCount)
    {
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return [
                'success' => false,
                'error' => "Не удалось получить класс данных для highload блока."
            ];
        }

        $result = $dataClass::update($courseId, ['UF_TICKETS' => $newTicketCount]);

        if ($result->isSuccess()) {
            return [
                'success' => true
            ];
        } else {
            return [
                'success' => false,
                'error' => $result->getErrorMessages()
            ];
        }
    }


    public function buyTicketOnCourse($data, $course_type = 'SDA')
    {
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return [
                'success' => false,
                'error' => "Не удалось получить класс данных для highload блока."
            ];
        }

        $id = isset($data['ID']) ? (int)$data['ID'] : 0;

        if ($id <= 0) {
            return [
                'success' => false,
                'error' => 'Идентификатор курса не указан или некорректен.'
            ];
        }

        $courseData = $dataClass::getById($id)->fetch();
        if (!$courseData) {
            return [
                'success' => false,
                'error' => 'Не удалось найти существующую запись курса.'
            ];
        }

        // Проверка и создание контакта в CRM
        $email = $data['email'];
        $contactId = $this->getContactIdByEmail($email);
        $companyId = null;
        $debug = [];
        if($contactId != null ) {
            $debug['select_contact'] = $contactId;
            $debug['send_email'] = $data['email'];
        }
        if (!$contactId) {
            // Создание компании
            $companyFields = [
                'TITLE' => $data['clinic'],
                'ASSIGNED_BY_ID' => 5754,
                'CREATED_BY_ID' => 1,
                'MODIFY_BY_ID' => 1,
                'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
            ];
            $companyResult = \Bitrix\Crm\CompanyTable::add($companyFields);

            if (!$companyResult->isSuccess()) {
                return [
                    'success' => false,
                    'error' => 'Ошибка создания компании: ' . implode(', ', $companyResult->getErrorMessages())
                ];
            }

            $companyId = $companyResult->getId();

            // Добавление телефона и email для компании
            $multiFields = [
                [
                    'ENTITY_ID' => 'COMPANY',
                    'ELEMENT_ID' => $companyId,
                    'TYPE_ID' => 'PHONE',
                    'VALUE' => $data['phone'],
                    'VALUE_TYPE' => 'WORK',
                    'COMPLEX_ID' => 'PHONE_WORK'
                ],
                [
                    'ENTITY_ID' => 'COMPANY',
                    'ELEMENT_ID' => $companyId,
                    'TYPE_ID' => 'EMAIL',
                    'VALUE' => $email,
                    'VALUE_TYPE' => 'WORK',
                    'COMPLEX_ID' => 'EMAIL_WORK'
                ]
            ];

            foreach ($multiFields as $field) {
                $result = \Bitrix\Crm\FieldMultiTable::add($field);
                if (!$result->isSuccess()) {
                    return [
                        'success' => false,
                        'error' => 'Ошибка добавления многозначного поля: ' . implode(', ', $result->getErrorMessages())
                    ];
                }
            }

            $arCity = $this->findCityByID($courseData['UF_CITY']);
            $event = $courseData["UF_TYPE"] . ', ' . $data['name'] . ' ' . $data['surname'] . ', ' . $courseData["UF_DATE"] . ', ' . $arCity["NAME"];
            
            // Создание контакта
            $contactFields = [
                'NAME' => $data['name'],
                'LAST_NAME' => $data['surname'],
                'COMPANY_ID' => $companyId,
                'ASSIGNED_BY_ID' => 5754,
                'CREATED_BY_ID' => 1,
                'MODIFY_BY_ID' => 1,
                'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                'UF_CRM_1616125505615' => $event,
            ];
            $contactResult = \Bitrix\Crm\ContactTable::add($contactFields);

            if (!$contactResult->isSuccess()) {
                return [
                    'success' => false,
                    'error' => 'Ошибка создания контакта: ' . implode(', ', $contactResult->getErrorMessages())
                ];
            }

            $contactId = $contactResult->getId();

            // Добавление телефона и email для контакта
            $multiFields = [
                [
                    'ENTITY_ID' => 'CONTACT',
                    'ELEMENT_ID' => $contactId,
                    'TYPE_ID' => 'PHONE',
                    'VALUE' => $data['phone'],
                    'VALUE_TYPE' => 'WORK',
                    'COMPLEX_ID' => 'PHONE_WORK'
                ],
                [
                    'ENTITY_ID' => 'CONTACT',
                    'ELEMENT_ID' => $contactId,
                    'TYPE_ID' => 'EMAIL',
                    'VALUE' => $email,
                    'VALUE_TYPE' => 'WORK',
                    'COMPLEX_ID' => 'EMAIL_WORK'
                ]
            ];

            foreach ($multiFields as $field) {
                $result = \Bitrix\Crm\FieldMultiTable::add($field);
                if (!$result->isSuccess()) {
                    return [
                        'success' => false,
                        'error' => 'Ошибка добавления многозначного поля: ' . implode(', ', $result->getErrorMessages())
                    ];
                }
            }
        }
        // Создание сделки
        $dealFields = [
            'TITLE' => "{$course_type} {$courseData["UF_DATE"]} {$arCity["NAME"]} {$data['name']} {$data['surname']}",
            // 'TITLE' => 'Сделка по курсу ' . $courseData['UF_NAME'],
            'CONTACT_ID' => $contactId,
            'COMPANY_ID' => $companyId,
            'ASSIGNED_BY_ID' => 5754,
            'CREATED_BY_ID' => 1,
            'MODIFY_BY_ID' => 1,
            'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
            'CATEGORY_ID' => ($course_type == "STIDent") ? 4 : 5,
            'IS_NEW' => "Y",
            'IS_RECURRING' => "N",
            'SOURCE_DESCRIPTION' => $id,
            'STAGE_ID' => ($course_type == "STIDent") ? "C4:NEW" : "C5:NEW",
        ];
        if (!empty($courseData["UF_PRICE"])) {
            // Удаляем пробелы и приводим строку к числовому значению с двумя десятичными знаками
            $dealFields['OPPORTUNITY'] = number_format(floatval(str_replace(' ', '', $courseData["UF_PRICE"])), 2, '.', '');
            // Устанавливаем валюту сделки
            $dealFields['CURRENCY_ID'] = 'RUB';
        }
        
        $dealResult = \Bitrix\Crm\DealTable::add($dealFields);

        if (!$dealResult->isSuccess()) {
            return [
                'success' => false,
                'error' => 'Ошибка создания сделки: ' . implode(', ', $dealResult->getErrorMessages())
            ];
        }

        // Списание билета
        $remainingTickets = (int)$courseData['UF_TICKETS'];
        if ($remainingTickets <= 0) {
            return [
                'success' => false,
                'error' => 'Нет доступных билетов.'
            ];
        }

        $updateTicketsResult = $this->updateCourseTickets($id, $remainingTickets - 1);
        if (!$updateTicketsResult['success']) {
            return [
                'success' => false,
                'error' => $updateTicketsResult['error']
            ];
        }

        return [
            'success' => true,
            'id' => $id,
            'company_id' => $companyId,
            'contact_id' => $contactId,
            'deal_id' => $dealResult->getId(),
            'new_tickets' => $remainingTickets - 1,
            'debug' => $debug,
        ];
    }

    private function getContactIdByEmail($email)
    {
        $contact = \Bitrix\Crm\ContactTable::getList([
            'filter' => ['=EMAIL' => $email],
            'select' => ['ID']
        ])->fetch();

        return $contact ? $contact['ID'] : null;
    }

    private function findCityByID($cityId)
    {
        if (!is_numeric($cityId) || $cityId <= 0) {
            return 'Некорректный ID города';
        }

        try {
            $cityQuery = CityTable::getList([
                'filter' => ['ID' => $cityId],
                'select' => ['ID', 'NAME', 'REGION_ID', 'REGION_NAME' => 'REGION.NAME'],
            ]);

            if ($city = $cityQuery->fetch()) {
                $cityData = [
                    'ID' => $city['ID'],
                    'NAME' => $city['NAME'],
                    'REGION_ID' => $city['REGION_ID'],
                    'REGION_NAME' => $city['REGION_NAME'],
                ];
                return $cityData;
            } else {
               return 'Город не найден';
            }
        } catch (Exception $e) {
            return 'Ошибка при поиске города: ' . $e->getMessage();
        }
    }

    public function getFieldList($field)
    {
        // Получаем класс данных HL-блока
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return [
                'success' => false,
                'error' => "Не удалось получить класс данных для highload блока."
            ];
        }

        // Получаем информацию о свойстве из HL-блока
        $userField = \CUserTypeEntity::GetList([], [
            'ENTITY_ID' => 'HLBLOCK_' . $this->hlblockId,
            'FIELD_NAME' => $field
        ])->Fetch();

        // Проверяем, существует ли переданное свойство и является ли оно типа "список"
        if (!$userField || $userField['USER_TYPE_ID'] !== 'enumeration') {
            return false;
        }

        // Получаем все значения из списка для данного свойства
        $enum = new \CUserFieldEnum();
        $rsEnum = $enum->GetList([], ['USER_FIELD_ID' => $userField['ID']]);

        $result = [];
        while ($value = $rsEnum->Fetch()) {
            $result[$value['ID']] = $value['VALUE'];
        }

        return $result;
    }
    public function getCourseById($courseId)
    {
        // Получаем класс данных highload блока
        $dataClass = $this->getEntity();
        if (!$dataClass) {
            return [
                'success' => false,
                'error' => "Не удалось получить класс данных для highload блока."
            ];
        }

        // Проверяем, передан ли корректный идентификатор курса
        if (!is_numeric($courseId) || $courseId <= 0) {
            return [
                'success' => false,
                'error' => 'Некорректный идентификатор курса.'
            ];
        }

        // Выполняем выборку курса по его идентификатору
        $result = $dataClass::getById($courseId);
        $courseData = $result->fetch();

        // Проверяем, удалось ли найти курс
        if (!$courseData) {
            return [
                'success' => false,
                'error' => 'Курс с указанным идентификатором не найден.'
            ];
        }

        return [
            'success' => true,
            'data' => $courseData
        ];
    }
}