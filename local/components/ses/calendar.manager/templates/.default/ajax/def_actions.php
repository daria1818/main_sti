<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use SES\CalendarManager\Geo\CityTable;
use SES\CalendarManager\CalendarCourse;
use SES\CalendarManager\CalendarUsers;
use SES\CalendarManager\CalendarSchedule;

class AjaxRequestHandler {
    /**
     * Обрабатывает входящий AJAX-запрос.
     *
     * @return void
     */
    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || !check_bitrix_sessid()) {
            echo json_encode(['status' => 'error', 'message' => 'Неверный запрос или сессия']);
            return;
        }

        if (!Loader::includeModule('ses.calendarmanager')) {
            ShowError(Loc::getMessage('CALENDAR_MODULE_NOT_INSTALLED'));
            return;
        }

        $action = $_POST['action'];
        switch ($action) {
            case 'findCityByPhrase':
                $this->findCityByPhrase();
                break;
            case 'findCityByID':
                $this->findCityByID();
                break;
            case 'addSchedule':
                $this->addOrUpdateSchedule();
                break;
            case 'saveCourse':
                $this->saveCourse();
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Неизвестное действие']);
        }
    }

    /**
     * Добавляет или обновляет расписание.
     *
     * @return void
     */
    private function addOrUpdateSchedule()
    {
        if (!isset($_POST['DATE'], $_POST['PATTERN'])) {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно данных для выполнения запроса', 'data' => $_POST]);
            return;
        }

        $dates = $_POST['DATE'];
        $pattern = $_POST['PATTERN'];
        $calendarUsers = new CalendarUsers();
        $CalendarSchedule = new CalendarSchedule();

        if (!is_array($dates) || empty($dates)) {
            echo json_encode(['status' => 'error', 'message' => 'Неправильный формат даты', 'data' => $dates]);
            return;
        }

        $date = $dates[0];
        $lecturerId = $calendarUsers->getCurUserModuleID();

        switch ($pattern) {
            case 'solo':
                $this->handleSoloPattern($lecturerId, $date, $_POST['TYPE'] ?? null, $CalendarSchedule);
                break;

            case 'two_in_two':
                $this->handleComplexPattern($lecturerId, $date, $CalendarSchedule, 2);
                break;

            case 'three_in_three':
                $this->handleComplexPattern($lecturerId, $date, $CalendarSchedule, 3);
                break;

            case 'days_week':
                $this->handleDaysWeekPattern($lecturerId, $dates, $CalendarSchedule);
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Неизвестный шаблон расписания']);
                break;
        }
    }

    /**
     * Обрабатывает шаблон "solo".
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата.
     * @param string|null $type Тип расписания.
     * @param CalendarSchedule $CalendarSchedule Объект расписания.
     * @return void
     */
    private function handleSoloPattern($lecturerId, $date, $type, $CalendarSchedule)
    {
        if ($type) {
            if ($CalendarSchedule->checkScheduleForDay($lecturerId, $date)) {
                $result = $CalendarSchedule->updateSchedule($lecturerId, $date, $type);
            } else {
                $result = $CalendarSchedule->createSchedule($lecturerId, $date, $type);
            }

            if ($result['success']) {
                echo json_encode(['status' => 'success', 'message' => 'Расписание успешно обновлено']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $result['error']]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно данных для выполнения запроса (TYPE)']);
        }
    }

    /**
     * Обрабатывает сложные шаблоны ("two_in_two", "three_in_three").
     *
     * @param int $lecturerId ID лектора.
     * @param string $date Дата.
     * @param CalendarSchedule $CalendarSchedule Объект расписания.
     * @param int $interval Интервал (2 или 3 дня).
     * @return void
     */
    private function handleComplexPattern($lecturerId, $date, $CalendarSchedule, $interval)
    {
        $dayAvailableDId = $CalendarSchedule->getListItemId('UF_DAY_AVAILABLE', 'D');

        if (!$dayAvailableDId) {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось получить ID для значения D']);
            return;
        }

        $existingScheduleResult = $CalendarSchedule->getScheduleAfterDate($lecturerId, $date);
        if (!$existingScheduleResult['success']) {
            echo json_encode(['status' => 'error', 'message' => $existingScheduleResult['message']]);
            return;
        }
        $existingSchedule = $existingScheduleResult['data'];
        $daysToKeep = [];

        foreach ($existingSchedule as $schedule) {
            if ($schedule['UF_DAY_AVAILABLE'] == $dayAvailableDId) {
                $daysToKeep[] = $schedule['UF_DATE'];
            } else {
                $result = $CalendarSchedule->deleteSchedule($lecturerId, $schedule['UF_DATE']);
                if (!$result['success']) {
                    echo json_encode(['status' => 'error', 'message' => $result['message'], 'date' => $schedule['UF_DATE']]);
                    return;
                }
            }
        }

        $currentDate = strtotime($date);
        $endDate = strtotime('+6 months', $currentDate);

        while ($currentDate <= $endDate) {
            for ($i = 0; $i < $interval; $i++) {
                $this->createOrUpdateSchedule($lecturerId, date('d.m.Y', $currentDate), 'Y', $daysToKeep, $CalendarSchedule);
                $currentDate = strtotime('+1 day', $currentDate);
            }
            for ($i = 0; $i < $interval; $i++) {
                $this->createOrUpdateSchedule($lecturerId, date('d.m.Y', $currentDate), 'N', $daysToKeep, $CalendarSchedule);
                $currentDate = strtotime('+1 day', $currentDate);
            }
        }

        echo json_encode(['status' => 'success', 'message' => "Расписание по шаблону $interval через $interval успешно создано"]);
    }

    /**
     * Обрабатывает шаблон "days_week".
     *
     * @param int $lecturerId ID лектора.
     * @param array $dates Массив дат.
     * @param CalendarSchedule $CalendarSchedule Объект расписания.
     * @return void
     */
    private function handleDaysWeekPattern($lecturerId, $dates, $CalendarSchedule)
    {
        $dayAvailableDId = $CalendarSchedule->getListItemId('UF_DAY_AVAILABLE', 'D');

        if (!$dayAvailableDId) {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось получить ID для значения D']);
            return;
        }

        $existingScheduleResult = $CalendarSchedule->getScheduleAfterDate($lecturerId, $dates[0]);
        if (!$existingScheduleResult['success']) {
            echo json_encode(['status' => 'error', 'message' => $existingScheduleResult['message']]);
            return;
        }
        $existingSchedule = $existingScheduleResult['data'];
        $daysToKeep = [];

        foreach ($existingSchedule as $schedule) {
            if ($schedule['UF_DAY_AVAILABLE'] == $dayAvailableDId) {
                $daysToKeep[] = $schedule['UF_DATE'];
            } else {
                $result = $CalendarSchedule->deleteSchedule($lecturerId, $schedule['UF_DATE']);
                if (!$result['success']) {
                    echo json_encode(['status' => 'error', 'message' => $result['message'], 'date' => $schedule['UF_DATE']]);
                    return;
                }
            }
        }

        // Получаем дни недели из переданных дат
        $weekDays = array_map(function($date) {
            return date('N', strtotime($date)); // 'N' формата возвращает день недели (1 для понедельника, 7 для воскресенья)
        }, $dates);

        $currentDate = strtotime($dates[0]);
        $endDate = strtotime('+6 months', $currentDate);

        while ($currentDate <= $endDate) {
            $dayOfWeek = date('N', $currentDate);
            $dateToAdd = date('d.m.Y', $currentDate);

            if (in_array($dayOfWeek, $weekDays)) {
                $this->createOrUpdateSchedule($lecturerId, $dateToAdd, 'Y', $daysToKeep, $CalendarSchedule);
            } else {
                $this->createOrUpdateSchedule($lecturerId, $dateToAdd, 'N', $daysToKeep, $CalendarSchedule);
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }

        echo json_encode(['status' => 'success', 'message' => 'Расписание по шаблону days_week успешно создано']);
    }

    /**
     * Создает или обновляет расписание на определенную дату.
     *
     * @param int $lecturerId ID лектора.
     * @param string $dateToAdd Дата для добавления.
     * @param string $type Тип расписания.
     * @param array $daysToKeep Массив дат для сохранения.
     * @param CalendarSchedule $CalendarSchedule Объект расписания.
     * @return void
     */
    private function createOrUpdateSchedule($lecturerId, $dateToAdd, $type, $daysToKeep, $CalendarSchedule)
    {
        if (!in_array($dateToAdd, $daysToKeep)) {
            $result = $CalendarSchedule->createSchedule($lecturerId, $dateToAdd, $type);
            if (!$result['success']) {
                echo json_encode(['status' => 'error', 'message' => $result['error'], 'date' => $dateToAdd]);
                exit;
            }
        }
    }

    /**
     * Ищет город по фразе.
     *
     * @return void
     */
    private function findCityByPhrase()
    {
        $phrase = htmlspecialcharsbx($_POST['phrase']);
        if (strlen($phrase) < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Слишком короткая фраза для поиска']);
            return;
        }

        try {
            $cityQuery = CityTable::getList([
                'filter' => ['NAME' => $phrase . '%'],
                'select' => ['ID', 'NAME', 'REGION_ID', 'REGION_NAME' => 'REGION.NAME'],
            ]);

            $cities = [];
            while ($city = $cityQuery->fetch()) {
                $cities[] = [
                    'ID' => $city['ID'],
                    'NAME' => $city['NAME'],
                    'REGION_ID' => $city['REGION_ID'],
                    'REGION_NAME' => $city['REGION_NAME'],
                ];
            }
            if (!empty($cities)) {
                echo json_encode(['status' => 'success', 'data' => $cities]);
            } else {
                echo json_encode(['status' => 'failure']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ошибка при поиске города: ' . $e->getMessage(),
            ]);
        }
    }

        /**
     * Ищет город по фразе.
     *
     * @return void
     */
    private function findCityByID()
    {
        $cityId = htmlspecialcharsbx($_POST['city_id']);
        if (!is_numeric($cityId) || $cityId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID города']);
            return;
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
                echo json_encode(['status' => 'success', 'data' => $cityData]);
            } else {
                echo json_encode(['status' => 'failure', 'message' => 'Город не найден']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ошибка при поиске города: ' . $e->getMessage(),
            ]);
        }
    }

    private function saveCourse()
    {
        $calendarCourse = new CalendarCourse();
        $result = $calendarCourse->saveCourse($data);
        echo json_encode($result);
    }

}
$handler = new AjaxRequestHandler();
$handler->handleRequest();
?>
