<?php
// Подключаем пролог Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $DB, $USER;

// Устанавливаем временные границы для фильтрации
$startDate = '2024-06-02 03:00:00';
$endDate = '2024-06-02 03:30:00';

// SQL-запрос для получения пользователей
$sql = "
    SELECT ID, LOGIN, EMAIL
    FROM b_user
    WHERE DATE_REGISTER >= '{$startDate}' AND DATE_REGISTER <= '{$endDate}'
";

// Выполняем запрос
$res = $DB->Query($sql);

// Функция для генерации случайного пароля
function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// Проверка и изменение паролей пользователей
while ($arUser = $res->Fetch()) {
    $userId = $arUser['ID'];
    $email = $arUser['EMAIL'];
    $login = $arUser['LOGIN'];
    $newPassword = generateRandomPassword();

    $user = new CUser;
    $fields = array(
        "PASSWORD" => $newPassword,
        "CONFIRM_PASSWORD" => $newPassword,
    );

    if ($user->Update($userId, $fields)) {
        echo "User ID: {$userId} - {$email} ({$login}) - Password: {$newPassword}<br>";
    } else {
        echo "Error updating user ID: {$userId} - {$email} ({$login}): " . $user->LAST_ERROR . "<br>";
    }
}

// Подключаем эпилог Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
