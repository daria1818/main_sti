<?php
// Включаем пролог Bitrix
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use SES\CalendarManager\CalendarCourse;
use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\ContactTable;
use Bitrix\Crm\FieldMultiTable;
use Bitrix\Main\LoaderException;

global $APPLICATION;
header('Content-Type: application/json');

// Функция для завершения выполнения скрипта и возврата JSON
function returnJson($data) {
    echo json_encode($data);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");
    exit;
}

// Функция для логирования ошибок в файл
function logError($message) {
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/error_log.txt", $message . PHP_EOL, FILE_APPEND);
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    returnJson(['success' => false, 'message' => 'Invalid request']);
}

// Подключаем необходимые модули
try {
    Loader::includeModule('ses.calendarmanager');
    Loader::includeModule('crm');
} catch (LoaderException $e) {
    logError('Ошибка подключения модуля: ' . $e->getMessage());
    returnJson(['success' => false, 'message' => 'Ошибка подключения модуля: ' . $e->getMessage()]);
}

// Получаем данные из POST-запроса
$request = Application::getInstance()->getContext()->getRequest();
$surname = htmlspecialchars($request->getPost('surname'));
$name = htmlspecialchars($request->getPost('name'));
$phone = htmlspecialchars($request->getPost('phone'));
$email = htmlspecialchars($request->getPost('email'));
$clinic = htmlspecialchars($request->getPost('clinic'));

// Валидация данных
if (empty($surname) || empty($name) || empty($phone) || empty($email) || empty($clinic)) {
    logError('Validation failed: Some fields are empty');
    returnJson(['success' => false, 'message' => 'Все поля должны быть заполнены']);
}

// Проверка параметра form_hash и поиск соответствующего курса
$formHash = htmlspecialchars($request->getPost('form_hash'));
if (empty($formHash)) {
    logError('Validation failed: form_hash is empty');
    returnJson(['success' => false, 'message' => 'Нужен код формы.']);
}

$calendarCourse = new CalendarCourse();
$course = $calendarCourse->getCourseByLink($formHash,'external');

if (!$course) {
    logError('Course not found for form_hash: ' . $formHash);
    returnJson(['success' => false, 'message' => 'Курс не найден.']);
}

// Проверка наличия билетов
$remainingTickets = (int)$course['UF_TICKETS'];
if ($remainingTickets <= 0) {
    logError('No tickets available for course ID: ' . $course['ID']);
    returnJson(['success' => false, 'message' => 'Нет доступных билетов.']);
}

// Уменьшаем количество билетов на 1
$updateResult = $calendarCourse->updateCourseTickets($course['ID'], $remainingTickets - 1);

if (!$updateResult['success']) {
    logError('Error updating course tickets for course ID: ' . $course['ID'] . ' - ' . $updateResult['error']);
    returnJson(['success' => false, 'message' => 'Ошибка обновления курса: ' . $updateResult['error']]);
}

// Проверка существования контакта
$contactExists = ContactTable::getList([
    'filter' => ['=EMAIL' => $email],
    'select' => ['ID', 'COMPANY_ID']
])->fetch();

$contactId = null;
$companyId = null;

if ($contactExists) {
    $contactId = $contactExists['ID'];
    $companyId = $contactExists['COMPANY_ID'];
} else {
    // Проверка существования компании
    $companyExists = CompanyTable::getList([
        'filter' => ['=EMAIL' => $email],
        'select' => ['ID']
    ])->fetch();

    if ($companyExists) {
        $companyId = $companyExists['ID'];
    } else {
        // Создание компании в CRM
        $formattedDate = new DateTime();
        $companyFields = [
            'TITLE' => $clinic,
            'ASSIGNED_BY_ID' => 5754,
            'CREATED_BY_ID' => 1,
            'DATE_CREATE' => $formattedDate,
        ];
        $companyResult = CompanyTable::add($companyFields);

        if (!$companyResult->isSuccess()) {
            logError('Error creating company: ' . implode(', ', $companyResult->getErrorMessages()));
            returnJson(['success' => false, 'message' => 'Ошибка создания компании: ' . implode(', ', $companyResult->getErrorMessages())]);
        }

        $companyId = $companyResult->getId();

        // Добавление телефона и email для компании
        $multiFields = [
            [
                'ENTITY_ID' => 'COMPANY',
                'ELEMENT_ID' => $companyId,
                'TYPE_ID' => 'PHONE',
                'VALUE' => $phone,
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
            $result = FieldMultiTable::add($field);
            if (!$result->isSuccess()) {
                logError('Error adding multi-field: ' . implode(', ', $result->getErrorMessages()));
                returnJson(['success' => false, 'message' => 'Ошибка добавления многозначного поля: ' . implode(', ', $result->getErrorMessages())]);
            }
        }
    }

    // Создание контакта в CRM
    $contactFields = [
        'NAME' => $name,
        'LAST_NAME' => $surname,
        'COMPANY_ID' => $companyId,
        'ASSIGNED_BY_ID' => 5754,
        'CREATED_BY_ID' => 1,
        'DATE_CREATE' => new DateTime(),
    ];
    $contactResult = ContactTable::add($contactFields);

    if (!$contactResult->isSuccess()) {
        logError('Error creating contact: ' . implode(', ', $contactResult->getErrorMessages()));
        returnJson(['success' => false, 'message' => 'Ошибка создания контакта: ' . implode(', ', $contactResult->getErrorMessages())]);
    }

    $contactId = $contactResult->getId();

    // Добавление телефона и email для контакта
    $multiFields = [
        [
            'ENTITY_ID' => 'CONTACT',
            'ELEMENT_ID' => $contactId,
            'TYPE_ID' => 'PHONE',
            'VALUE' => $phone,
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
        $result = FieldMultiTable::add($field);
        if (!$result->isSuccess()) {
            logError('Error adding multi-field: ' . implode(', ', $result->getErrorMessages()));
            returnJson(['success' => false, 'message' => 'Ошибка добавления многозначного поля: ' . implode(', ', $result->getErrorMessages())]);
        }
    }
}

returnJson(['success' => true, 'message' => 'Курс успешно обновлен, билет забронирован.', 'companyId' => $companyId, 'contactId' => $contactId]);

// Завершаем эпилог Bitrix
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");
?>
