<?
declare(strict_types=1);
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$file_mail_name = 'mail_list.txt'; // имя файла с списком email
$coins_rate = null; // Курс монет по отношению к ₽
$coinsValue = 19000; //Сумма начисления в рублях

//считывание email из файла
function readEmailsFromFile(string $filename): array {
    $emails = [];
    $file = fopen($filename, "r");

    if ($file) {
        while (($line = fgets($file)) !== false) {
            $emails[] = trim($line);
        }
        fclose($file);
    } else {
        throw new Exception("Не удалось открыть файл: $filename");
    }

    return $emails;
}

//получение курса STI COINS
function getModuleCoinsRate(): int {
    return (int) COption::GetOptionString("rubyroid.bonusloyalty", "points_exchange_rate", "25");
}

//Обработка полученных email адресов
function validateAndDeduplicateEmails(array $emails): array {
    $validEmails = [];
    $seen = [];

    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (!isset($seen[$email])) {
                $validEmails[] = $email;
                $seen[$email] = true;
            }
        }
    }

    return $validEmails;
}

//получение зарегистрированных юзеров по полученному списку
function getUsersByEmails(array $preparedEmailList): array {
    if (!Loader::includeModule('main')) {
        throw new \Exception("Модуль 'main' не загружен.");
    }

    $filter = ['=EMAIL' => $preparedEmailList];

    $userQuery = UserTable::getList([
        'select' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL','UF_LOYALTY_COIN'],
        'filter' => $filter
    ]);

    $users = [];
    while ($user = $userQuery->fetch()) {
        $users[] = $user;
    }

    return $users;
}

//начисление STI Coins
function updateLoyaltyCoins(int $userId, int $coinsToAdd): array {
    Loader::includeModule('main');

    $user = UserTable::getById($userId)->fetch();
    $currentCoins = (int) $user['UF_LOYALTY_COIN'];
    $newCoins = $currentCoins + $coinsToAdd;

    $userUpdate = new \CUser;
    $result = $userUpdate->Update($userId, ['UF_LOYALTY_COIN' => $newCoins]);

    if ($result) {
        return ['ID' => $userId, 'old_coins' => $currentCoins, 'new_coins' => $newCoins];
    } else {
        return ['error' => "Ошибка обновления пользователя с ID: $userId"];
    }
}

//регистрация новых юзеров, если их нет и начисление STI Coins
function registerUserWithEmailAndCoins(string $email, int $initialCoins): array {
    Loader::includeModule('main');

    $password = rand(10000000, 99999999);
    $defaultGroups = [3,6];
    $customGroupId = 33;
    array_push($defaultGroups, $customGroupId);

    $user = new \CUser;
    $userId = $user->Add([
        'LOGIN' => $email,
        'EMAIL' => $email,
        'PASSWORD' => $password,
        'CONFIRM_PASSWORD' => $password,
        'GROUP_ID' => $defaultGroups,
        'UF_LOYALTY_COIN' => $initialCoins,
        'ACTIVE' => 'Y'
    ]);

    if ($userId) {
        return ['ID' => $userId, 'password' => $password, 'login' => $email, 'coins' => $initialCoins];
    } else {
        return ['error' => $user->LAST_ERROR];
    }
}

//основной запуск функционала по начислению
function processEmailsAndCoinsAdd(array $preparedEmailList, array $preparedUsersList, int $coinsValue, int $coinsRate): array {
    $coinsPerEmail = (int) ($coinsValue / $coinsRate);
    $result = [
        'new_users' => [],
        'updated_users' => [],
        'errors' => []
    ];

    $existingUsers = [];
    foreach ($preparedUsersList as $user) {
        $lowerCaseEmail = strtolower($user['EMAIL']);
        $existingUsers[$lowerCaseEmail] = $user;
    }

    $preparedEmailList = array_map('strtolower', $preparedEmailList);

    foreach ($preparedEmailList as $email) {
        if (array_key_exists($email, $existingUsers)) {
            $updateResult = updateLoyaltyCoins((int) $existingUsers[$email]['ID'], $coinsPerEmail);
            if (isset($updateResult['error'])) {
                $result['errors'][] = $updateResult['error'];
            } else {
                $result['updated_users'][] = $updateResult;
            }
        } else {
            $registerResult = registerUserWithEmailAndCoins($email, $coinsPerEmail);
            if (isset($registerResult['error'])) {
                $result['errors'][] = $registerResult['error'];
            } else {
                $result['new_users'][] = $registerResult;
            }
        }
    }

    return $result;
}

//основной запуск функционала по списанию
function processEmailsAndCoinsDelete(array $preparedEmailList, array $preparedUsersList, int $coinsValue, int $coinsRate): array {
    $coinsPerEmail = (int) ($coinsValue / $coinsRate);
    $result = [
        'new_users' => [],
        'updated_users' => [],
        'errors' => []
    ];

    $existingUsers = [];
    foreach ($preparedUsersList as $user) {
        $lowerCaseEmail = strtolower($user['EMAIL']);
        $existingUsers[$lowerCaseEmail] = $user;
    }

    $preparedEmailList = array_map('strtolower', $preparedEmailList);

    foreach ($preparedEmailList as $email) {
        if (array_key_exists($email, $existingUsers)) {
            echo "Юзер найден - {$existingUsers['ID']} \n";
        } else {
             echo "Юзер НЕ найден - {$email} \n";
        }
    }

    return $result;
}


$emailList = readEmailsFromFile("{$file_mail_name}");  
$coinsRate = getModuleCoinsRate();
$preparedEmailList = validateAndDeduplicateEmails($emailList);
$preparedUsersList = getUsersByEmails($preparedEmailList);

// $resultLoyaltyCoins = processEmailsAndCoinsAdd($preparedEmailList, $preparedUsersList, $coinsValue, $coinsRate); // закоментил, что бы при открытии файла, ничего не начислялось
// $resultLoyaltyCoins = processEmailsAndCoinsDelete($preparedEmailList, $preparedUsersList, $coinsValue, $coinsRate); // закоментил, что бы при открытии файла, ничего не начислялось
// результат обработки
echo '<pre>';
print_r($resultLoyaltyCoins);
echo "</pre>";