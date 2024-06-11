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
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Данные были переданы в формате JSON
            $action = $data['action'];
            $requestData = $data;
        } else {
            // Данные были переданы в формате application/x-www-form-urlencoded
            $action = $_POST['action'];
            $requestData = $_POST;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($action) || !check_bitrix_sessid()) {
            // $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid request']);
            // return;
        }

        if (!Loader::includeModule('ses.calendarmanager')) {
            $this->sendJsonResponse(['status' => 'error', 'message' => Loc::getMessage('CALENDAR_MODULE_NOT_INSTALLED')]);
            return;
        }

        switch ($action) {
            case 'findCityByPhrase':
                $this->findCityByPhrase($requestData);
                break;
            case 'findCityByID':
                $this->findCityByID($requestData);
                break;
            case 'addSchedule':
                $this->addOrUpdateSchedule($requestData);
                break;
            case 'saveCourse':
                $this->saveCourse($requestData['data']);
                break;
            case 'regOnCourse':
                $this->regOnCourse($requestData['data']);
                break;
            default:
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unknown action']);
        }
    }

    private function sendJsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    private function findCityByPhrase($requestData)
    {
        $phrase = htmlspecialcharsbx($requestData['phrase']);
        if (strlen($phrase) < 1) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Слишком короткая фраза для поиска']);
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
                $this->sendJsonResponse(['status' => 'success', 'data' => $cities]);
            } else {
                $this->sendJsonResponse(['status' => 'failure']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => 'Ошибка при поиске города: ' . $e->getMessage(),
            ]);
        }
    }

    private function findCityByID($requestData)
    {
        $cityId = htmlspecialcharsbx($requestData['city_id']);
        if (!is_numeric($cityId) || $cityId <= 0) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Некорректный ID города']);
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
                $this->sendJsonResponse(['status' => 'success', 'data' => $cityData]);
            } else {
                $this->sendJsonResponse(['status' => 'failure', 'message' => 'Город не найден']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => 'Ошибка при поиске города: ' . $e->getMessage(),
            ]);
        }
    }

    private function addOrUpdateSchedule($requestData)
    {
        if (!isset($requestData['DATE'], $requestData['PATTERN'])) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Недостаточно данных для выполнения запроса', 'data' => $requestData]);
            return;
        }

        $dates = $requestData['DATE'];
        $pattern = $requestData['PATTERN'];
        $calendarUsers = new CalendarUsers();
        $CalendarSchedule = new CalendarSchedule();

        if (!is_array($dates) || empty($dates)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Неправильный формат даты', 'data' => $dates]);
            return;
        }

        $date = $dates[0];
        $lecturerId = $calendarUsers->getCurUserModuleID();

        switch ($pattern) {
            case 'solo':
                $this->handleSoloPattern($lecturerId, $date, $requestData['TYPE'] ?? null, $CalendarSchedule);
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
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Неизвестный шаблон расписания']);
                break;
        }
    }

    private function handleSoloPattern($lecturerId, $date, $type, $CalendarSchedule)
    {
        if ($type) {
            if ($CalendarSchedule->checkScheduleForDay($lecturerId, $date)) {
                $result = $CalendarSchedule->updateSchedule($lecturerId, $date, $type);
            } else {
                $result = $CalendarSchedule->createSchedule($lecturerId, $date, $type);
            }

            if ($result['success']) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Расписание успешно обновлено']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => $result['error']]);
            }
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Недостаточно данных для выполнения запроса (TYPE)']);
        }
    }

    private function handleComplexPattern($lecturerId, $date, $CalendarSchedule, $interval)
    {
        $dayAvailableDId = $CalendarSchedule->getListItemId('UF_DAY_AVAILABLE', 'D');

        if (!$dayAvailableDId) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Не удалось получить ID для значения D']);
            return;
        }

        $existingScheduleResult = $CalendarSchedule->getScheduleAfterDate($lecturerId, $date);
        if (!$existingScheduleResult['success']) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $existingScheduleResult['message']]);
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
                    $this->sendJsonResponse(['status' => 'error', 'message' => $result['message'], 'date' => $schedule['UF_DATE']]);
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

        $this->sendJsonResponse(['status' => 'success', 'message' => "Расписание по шаблону $interval через $interval успешно создано"]);
    }

    private function handleDaysWeekPattern($lecturerId, $dates, $CalendarSchedule)
    {
        $dayAvailableDId = $CalendarSchedule->getListItemId('UF_DAY_AVAILABLE', 'D');

        if (!$dayAvailableDId) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Не удалось получить ID для значения D']);
            return;
        }

        $existingScheduleResult = $CalendarSchedule->getScheduleAfterDate($lecturerId, $dates[0]);
        if (!$existingScheduleResult['success']) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $existingScheduleResult['message']]);
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
                    $this->sendJsonResponse(['status' => 'error', 'message' => $result['message'], 'date' => $schedule['UF_DATE']]);
                    return;
                }
            }
        }

        $weekDays = array_map(function($date) {
            return date('N', strtotime($date));
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

        $this->sendJsonResponse(['status' => 'success', 'message' => 'Расписание по шаблону days_week успешно создано']);
    }

    private function createOrUpdateSchedule($lecturerId, $dateToAdd, $type, $daysToKeep, $CalendarSchedule)
    {
        if (!in_array($dateToAdd, $daysToKeep)) {
            $result = $CalendarSchedule->createSchedule($lecturerId, $dateToAdd, $type);
            if (!$result['success']) {
                $this->sendJsonResponse(['status' => 'error', 'message' => $result['error'], 'date' => $dateToAdd]);
                exit;
            }
        }
    }

    private function saveCourse($postData)
    {
        $calendarCourse = new CalendarCourse();
        $result = $calendarCourse->saveCourse($postData);
        $this->sendJsonResponse($result);
    }

    private function regOnCourse($postData)
    {
        $calendarCourse = new CalendarCourse();
        $result = $calendarCourse->buyTicketOnCourse($postData);
        $this->sendJsonResponse($result);
        // $this->sendJsonResponse(['status' => 'success', 'message' => 'Вы успешно зарегестрированы']);
        return;
    }
}

$handler = new AjaxRequestHandler();
$handler->handleRequest();
?>
