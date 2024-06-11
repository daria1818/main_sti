<?php

// // URL сервера REST API Bitrix24
// $url = 'https://crm.stionline.ru/rest/event.offline.get.json';

// // Данные для авторизации
// $authData = array(
//     "auth" => "0c5e70650053859400437c9200001586000007491a5d6b4944bdc876c66352fd52e1e3",
//     "auth_connector" => "588"
// );

// // Данные для отправки в запросе
// $requestData = array(
//     "FirstParam" => "first",
//     "clear" => "1",
//     "LastParam" => "last"
// );

// // Формирование данных для отправки
// $data = array(
//     "auth" => serialize($authData),
//     "data" => serialize($requestData)
// );

// // Инициализация сеанса cURL
// $ch = curl_init();

// // Настройка параметров cURL
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// // Выполнение запроса
// $response = curl_exec($ch);

// // Закрытие сеанса cURL
// curl_close($ch);

// // Вывод результата
// echo $response;

?>
