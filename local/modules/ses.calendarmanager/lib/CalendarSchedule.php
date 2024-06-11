<?php

namespace SES\CalendarManager;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class CalendarSchedule
{
    private $hlblockId;
    private $roleFieldCode = 'UF_ROLE';

    /**
     * Конструктор класса.
     * Инициализирует идентификатор highload блока и загружает модули.
     *
     * @throws \Exception Если не удалось подключить модули highloadblock или iblock.
     */
    public function __construct()
    {
        $this->hlblockId = (int)Option::get('ses.calendarmanager', 'ID_COURSES_SCHEDULE_HL');
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

    /**
     * Получает ID значения поля типа список по XML_ID.
     *
     * @param string $fieldName Название поля.
     * @param string $value XML_ID значения поля.
     * @return int|false ID значения или false, если не найдено.
     */
    public function getListItemId($fieldName, $value)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return false;
        }

        $userFieldEnum = new \CUserFieldEnum();
        $rsEnum = $userFieldEnum->GetList([], ['USER_FIELD_NAME' => $fieldName, 'XML_ID' => $value]);

        if ($enumValue = $rsEnum->Fetch()) {
            return $enumValue['ID'];
        }

        return false;
    }

    /**
     * Получает значение из свойства типа список по его ID.
     *
     * @param string $fieldName Название поля.
     * @param int $valueId ID значения поля.
     * @return string|null Значение поля или null, если не найдено.
     */
    public function getEnumValueById($fieldName, $valueId)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return false;
        }

        $userFieldEnum = new \CUserFieldEnum();
        $rsEnum = $userFieldEnum->GetList([], ['USER_FIELD_NAME' => $fieldName, 'ID' => $valueId]);

        if ($enumValue = $rsEnum->Fetch()) {
            return $enumValue['VALUE'];
        }

        return "zero";
    }


    /**
     * Возвращает список всех элементов поля UF_DAY_AVAILABLE в формате ID элемента - XML_ID.
     *
     * @return array Список элементов в формате ID элемента - XML_ID.
     * @throws \Exception Если не удалось получить список элементов.
     */
    public function getDayAvailableList()
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            throw new \Exception('Entity class is not available.');
        }

        $userFieldEnum = new \CUserFieldEnum();
        $rsEnum = $userFieldEnum->GetList([], ['USER_FIELD_NAME' => 'UF_DAY_AVAILABLE']);

        $dayAvailableList = [];
        while ($enumValue = $rsEnum->Fetch()) {
            $dayAvailableList[$enumValue['ID']] = $enumValue['XML_ID'];
        }

        return $dayAvailableList;
    }
    
    /**
     * Проверяет наличие расписания на определенный день для лектора.
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата в формате 'YYYY-MM-DD'.
     * @return bool
     */
    public function checkScheduleForDay($lecturerId, $date)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return false;
        }

        // Преобразование даты из формата 'YYYY-MM-DD' в 'DD.MM.YYYY'
        $formattedDate = DateTime::createFromPhp(new \DateTime($date))->format('d.m.Y');

        // Формирование запроса к HL-блоку
        $result = $entityDataClass::getList([
            'select' => ['ID'],
            'filter' => [
                'UF_LECTURER_ID' => $lecturerId,
                'UF_DATE' => $formattedDate
            ],
            'limit' => 1
        ]);

        if ($row = $result->fetch()) {
            return true;
        }

        return false;
    }

    /**
     * Обновляет расписание для лектора на определенный день.
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата в формате 'YYYY-MM-DD'.
     * @param string $type Тип расписания.
     * @return array
     */
    public function updateSchedule($lecturerId, $date, $type)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return [
                'success' => false,
                'error' => "Entity class is not available."
            ];
        }

        $formattedDate = DateTime::createFromPhp(new \DateTime($date))->format('d.m.Y');
        $result = $entityDataClass::getList([
            'select' => ['ID'],
            'filter' => [
                'UF_LECTURER_ID' => $lecturerId,
                'UF_DATE' => $formattedDate
            ]
        ])->fetch();

        if ($result) {
            $ufDayAvailableId = $this->getListItemId('UF_DAY_AVAILABLE', $type);

            if ($ufDayAvailableId) {
                $updateResult = $entityDataClass::update($result['ID'], [
                    'UF_DAY_AVAILABLE' => $ufDayAvailableId,
                    'UF_DATE_MODIFIED' => new DateTime()
                ]);

                if ($updateResult->isSuccess()) {
                    return [
                        'success' => true
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => "Ошибка обновления записи: " . implode('; ', $updateResult->getErrorMessages())
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => "Invalid type value: $type"
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => "Запись для обновления не найдена."
            ];
        }
    }

    /**
     * Создает новое расписание для лектора.
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата в формате 'YYYY-MM-DD'.
     * @param string $type Тип расписания.
     * @return array
     */
    public function createSchedule($lecturerId, $date, $type)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return [
                'success' => false,
                'error' => "Entity class is not available."
            ];
        }

        $formattedDate = DateTime::createFromPhp(new \DateTime($date))->format('d.m.Y');
        $ufDayAvailableId = $this->getListItemId('UF_DAY_AVAILABLE', $type);

        if ($ufDayAvailableId) {
            $addResult = $entityDataClass::add([
                'UF_LECTURER_ID' => $lecturerId,
                'UF_DAY_AVAILABLE' => $ufDayAvailableId,
                'UF_DATE' => $formattedDate,
                'UF_SORT' => 100,
                'UF_DATE_ADDED' => new DateTime(),
                'UF_DATE_MODIFIED' => new DateTime()
            ]);

            if ($addResult->isSuccess()) {
                return [
                    'success' => true
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "Ошибка добавления новой записи: " . implode('; ', $addResult->getErrorMessages())
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => "Invalid type value: $type"
            ];
        }
    }

    /**
     * Возвращает записи HL-блока по фильтру ID лектора и дате за месяц и год.
     *
     * @param int $lecturerId ID лектора.
     * @param int $month Месяц (1-12).
     * @param int $year Год (например, 2024).
     * @return array
     */
    public function getEventsByLectorAndDate($lecturerId = '', $month, $year, $filters = [])
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return [
                'success' => false,
                'error' => "Entity class is not available."
            ];
        }

        if (!is_numeric($month) || !is_numeric($year)) {
            return [
                'success' => false,
                'error' => "Месяц и год должны быть числовыми значениями."
            ];
        }

        $formattedMonth = sprintf("%02d", $month);

        // Создание объектов Date Bitrix и их преобразование в строку
        $startDate = new \Bitrix\Main\Type\Date("$year-$formattedMonth-01", "Y-m-d");
        $endDate = new \Bitrix\Main\Type\Date("$year-$formattedMonth-" . date('t', strtotime("$year-$formattedMonth-01")), "Y-m-d");

        $filter = [
            '>=UF_DATE' => $startDate,
            '<=UF_DATE' => $endDate,
        ];

        if(!empty($lecturerId)){
            $filter["UF_LECTURER_ID"] = $lecturerId;
        }

        if (!empty($filters)) {//хардкод, надо заменить ключ при установке 
            if (isset($filters['UF_LECTOR'])) {
                $filters['UF_LECTURER_ID'] = $filters['UF_LECTOR'];
                unset($filters['UF_LECTOR']);
            }
            if (isset($filters['UF_CITY'])) {
                unset($filters['UF_CITY']);
            }
            $filter = array_merge($filter, $filters);
        }


        $parameters = [
            'select' => ['*'],
            'order' => ['UF_DATE' => 'ASC', 'UF_SORT' => 'ASC'],
            'filter' => $filter
        ];

        $result = $entityDataClass::getList($parameters);
        $events = [];
        while ($event = $result->fetch()) {
            $events[] = $event;
        }

        return [
            'success' => true,
            'data' => $events
        ];
    }

    /**
     * Возвращает расписание лектора после определенной даты.
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата в формате 'YYYY-MM-DD'.
     * @return array
     */
    public function getScheduleAfterDate($lecturerId, $date)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return [
                'success' => false,
                'message' => 'Entity class is not available.'
            ];
        }

        $formattedDate = DateTime::createFromPhp(new \DateTime($date))->format('d.m.Y');
        
        $filter = [
            '>=UF_DATE' => $formattedDate,
            'UF_LECTURER_ID' => $lecturerId
        ];

        $parameters = [
            'select' => ['*'],
            'order' => ['UF_DATE' => 'ASC'],
            'filter' => $filter
        ];

        $result = $entityDataClass::getList($parameters);
        $schedules = [];
        while ($schedule = $result->fetch()) {
            $schedules[] = $schedule;
        }

        return [
            'success' => true,
            'data' => $schedules
        ];
    }

    /**
     * Удаляет расписание лектора на определенный день.
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата в формате 'YYYY-MM-DD'.
     * @return array
     */
    public function deleteSchedule($lecturerId, $date)
    {
        $entityDataClass = $this->getEntity();
        if (!$entityDataClass) {
            return [
                'success' => false,
                'message' => 'Entity class is not available.'
            ];
        }

        $formattedDate = DateTime::createFromPhp(new \DateTime($date))->format('d.m.Y');
        
        $result = $entityDataClass::getList([
            'select' => ['ID'],
            'filter' => [
                'UF_LECTURER_ID' => $lecturerId,
                'UF_DATE' => $formattedDate
            ]
        ])->fetch();

        if ($result) {
            $deleteResult = $entityDataClass::delete($result['ID']);
            if ($deleteResult->isSuccess()) {
                return [
                    'success' => true
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка при удалении записи: ' . implode('; ', $deleteResult->getErrorMessages())
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Запись для удаления не найдена.'
        ];
    }
}