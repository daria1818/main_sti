<?php
// Подключение ядра Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключение класса CalendarUsers
use Bitrix\Main\Loader;
use SES\CalendarManager\CalendarUsers;
use Bitrix\Main\UserTable;
Loader::includeModule('ses.calendarmanager');
Loader::includeModule('main');

// Данные для пользователей
$contacts = [
    ["FirstName" => "Глеб", "LastName" => "Асеев", "Phone" => "8 913 747 03 70", "Email" => "dr.aseev@icloud.com", "City" => "Москва"],
    ["FirstName" => "Кристина", "LastName" => "Кошмина", "Phone" => "8 920 736 3955", "Email" => "kristinakoshmina@yandex.ru", "City" => "Москва"],
    ["FirstName" => "Мария", "LastName" => "Навражный", "Phone" => "8 909 221 3858", "Email" => "marianavrazhnykh@mail.ru", "City" => "Москва"],
    ["FirstName" => "Татьяна", "LastName" => "Петросян", "Phone" => "8 910 430 3205", "Email" => "tatianakovalenko@icloud.com", "City" => "Москва"],
    ["FirstName" => "Светлана", "LastName" => "Дудина", "Phone" => "8 903 300 3333", "Email" => "shvaykinaterstom@gmail.com", "City" => "Москва"],
    ["FirstName" => "Мария", "LastName" => "Мельничук", "Phone" => "8 926 135 3501", "Email" => "manusha999@inbox.ru", "City" => "Москва"],
    ["FirstName" => "Виктория", "LastName" => "Хазимова", "Phone" => "8 919 496 4220", "Email" => "khazimova1992@mail.ru", "City" => "Москва"],
    ["FirstName" => "Юлия", "LastName" => "Островская", "Phone" => "8 926 196 3791", "Email" => "mrs.yostrov@mail.ru", "City" => "Москва, ННовгород"],
    ["FirstName" => "Татьяна", "LastName" => "Минина", "Phone" => "8 905 265 02 44", "Email" => "medunizza@yandex.ru", "City" => "Спб"],
    ["FirstName" => "Юлия", "LastName" => "Волкова", "Phone" => "8 911 943 3293", "Email" => "drvolkovajulia@gmail.com", "City" => "Спб"],
    ["FirstName" => "Раиса", "LastName" => "Обухова", "Phone" => "8 921 782 9052", "Email" => "obukhovaraisa909@gmail.com", "City" => "Спб"],
    ["FirstName" => "Ксения", "LastName" => "Белоусова", "Phone" => "8 915 517 0099", "Email" => "kseniyabelousova98@mail.ru", "City" => "Курск"],
    ["FirstName" => "Вифания", "LastName" => "", "Phone" => "8 920 801 5810", "Email" => "inkin.viphania@yandex.ru", "City" => "Орел"],
    ["FirstName" => "Татьяна", "LastName" => "Большеротова", "Phone" => "8 927 618 4055", "Email" => "tatyana.bolsherotova@yandex.ru", "City" => "Самара"],
    ["FirstName" => "Рената", "LastName" => "Бициева", "Phone" => "8 918 837 2755", "Email" => "renata.dentist@mail.ru", "City" => "Владикавказ"],
    ["FirstName" => "Элина", "LastName" => "Меликсетян", "Phone" => "8 928 369 1369", "Email" => "elina.meliksetyan@yandex.ru", "City" => "Пятигорск"],
    ["FirstName" => "Ани", "LastName" => "Атанесян", "Phone" => "8 928 311 3883", "Email" => "atanesianani@gmail.com", "City" => "Ставрополь"],
    ["FirstName" => "Анастасия", "LastName" => "Сомова", "Phone" => "8 919 332 7115", "Email" => "anasomovaa@mail.ru", "City" => "Челябинск"],
    ["FirstName" => "Любовь", "LastName" => "Грибанова", "Phone" => "8 951 476 8646", "Email" => "gribanova_la@mail.ru", "City" => "Челябинск"],
    ["FirstName" => "Оксана", "LastName" => "Гуляева", "Phone" => "8 917 756 9993", "Email" => "oksgulyaeva@yandex.ru", "City" => "Уфа"],
    ["FirstName" => "Татьяна", "LastName" => "Братская", "Phone" => "8 919 947 8727", "Email" => "tatiana.bratskaya@mail.ru", "City" => "Тюмень"],
    ["FirstName" => "Татьяна", "LastName" => "Гатилова", "Phone" => "8 952 939 9919", "Email" => "tagatilova@gmail.com", "City" => "Новосибирск"],
    ["FirstName" => "Юлия", "LastName" => "Карманова", "Phone" => "8 923 606 1026", "Email" => "juliakic@mail.ru", "City" => "Кемерово"],
    ["FirstName" => "Ирина", "LastName" => "Цуканова", "Phone" => "8 950 289 7242", "Email" => "tsukanova.2021@mail.ru", "City" => "Владивосток"],
    ["FirstName" => "Денис", "LastName" => "Почекунин", "Phone" => "8 914 686 9412", "Email" => "d.pochekunin@gmail.com", "City" => "Южно Сахалинск"],
    ["FirstName" => "Евгений", "LastName" => "Буторин", "Phone" => "8 909 845 9548", "Email" => "e.butorin@list.ru", "City" => "Комсомольск"],
    ["FirstName" => "Виктория", "LastName" => "Жарченко", "Phone" => "8 999 686 8160", "Email" => "vika-1302@mail.ru", "City" => "Иркутск"]
];

// Функция для проверки существования пользователя по email и получения его ID
function getUserByEmail($email) {
    $filter = ['=EMAIL' => $email];
    return UserTable::getList(['filter' => $filter])->fetch();
}

// Текущая дата в формате без точек
$date = date("dmY");

foreach ($contacts as $contact) {
    $userDetails = getUserByEmail($contact['Email']);
    
    if ($userDetails) {
        // Пользователь существует
        $userId = $userDetails['ID'];
        echo "Пользователь с email " . $contact['Email'] . " уже существует. ID: " . $userId . "<br>";

        // Добавление в группу 86, если не состоит в ней
        $userGroups = CUser::GetUserGroup($userId);
        if (!in_array(86, $userGroups)) {
            $userGroups[] = 86;
            CUser::SetUserGroup($userId, $userGroups);
            echo "Пользователь добавлен в группу 86.<br>";
        }

        // Обновление поля UF_DEPARTMENT
        $user = new CUser;
        $user->Update($userId, ["UF_DEPARTMENT" => [9084]]);
        echo "Пользователь добавлен в департамент 9084.<br>";

        // Добавление пользователя в Highload-блок
        $hlFields = array(
            'UF_USER_ID' => $userId,
            'UF_ROLE' => 243,
            'UF_LAST_NAME' => $contact['LastName'],
            'UF_FIRST_NAME' => $contact['FirstName'],
            'UF_SORT' => 500, // Сортировка по умолчанию
        );
        $calendar = new CalendarUsers();
        $result = $calendar->addLectorToHL($hlFields);
        if ($result->isSuccess()) {
            echo "Пользователь " . $contact['FirstName'] . " " . $contact['LastName'] . " успешно добавлен в Highload-блок.<br>";
        } else {
            echo "Ошибка при добавлении пользователя " . $contact['FirstName'] . " " . $contact['LastName'] . " в Highload-блок: " . implode(", ", $result->getErrorMessages()) . "<br>";
        }
    } else {
        // Пользователя не существует, создаем нового
        $password = $contact['LastName'] . $date;

        $user = new CUser;
        $arFields = array(
            "NAME"             => $contact['FirstName'],
            "LAST_NAME"        => $contact['LastName'],
            "EMAIL"            => $contact['Email'],
            "LOGIN"            => $contact['Email'],
            "LID"              => "ru",
            "ACTIVE"           => "Y",
            "GROUP_ID"         => array(3,6,86), // Группа пользователей
            "PASSWORD"         => $password,
            "CONFIRM_PASSWORD" => $password,
            "PERSONAL_PHONE"   => $contact['Phone'],
            "PERSONAL_CITY"    => $contact['City'],
            "UF_DEPARTMENT"    => array(9084), // Добавление в департамент
        );

        $ID = $user->Add($arFields);
        if (intval($ID) > 0) {
            echo "Пользователь " . $contact['FirstName'] . " " . $contact['LastName'] . " успешно создан.<br>";

            // Добавление пользователя в Highload-блок
            $hlFields = array(
                'UF_USER_ID' => $ID,
                'UF_ROLE' => 243,
                'UF_LAST_NAME' => $contact['LastName'],
                'UF_FIRST_NAME' => $contact['FirstName'],
                'UF_SORT' => 500, // Сортировка по умолчанию
            );
            $calendar = new CalendarUsers();
            $result = $calendar->addLectorToHL($hlFields);
            if ($result->isSuccess()) {
                echo "Пользователь " . $contact['FirstName'] . " " . $contact['LastName'] . " успешно добавлен в Highload-блок.<br>";
            } else {
                echo "Ошибка при добавлении пользователя " . $contact['FirstName'] . " " . $contact['LastName'] . " в Highload-блок: " . implode(", ", $result->getErrorMessages()) . "<br>";
            }
        } else {
            echo "Ошибка при создании пользователя " . $contact['FirstName'] . " " . $contact['LastName'] . ": " . $user->LAST_ERROR . "<br>";
        }
    }
}
?>
