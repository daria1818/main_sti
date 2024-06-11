<? 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$res = executeREST('crm.lead.add', array(
                            'TITLE'=>"Заявка на звонок с лендинга от " . date('d.m.Y H:i:s'),
                            'PHONE' => array(array('VALUE' => $_POST['phone'], 'VALUE_TYPE' => 'WORK' )),
                            'NAME' => $_POST['full-name'],
                        ));
// $arEventFields = array(
//     'TITLE'=>"Заявка с сайта: Коллективная заявка с сайт",
//     'PHONE' => $_POST['phone'],
//     'SEND_EMAIL' => $_POST['name'],
//     'APPONENT' => $apponent,
//     'COMPANY' => $_POST['company'],
//     'TICKETS_COUNT' => $_POST['peoples'],
//     'DATE' => $finish_date,
//     'CITY' => $state[1], 
//     'ADM_EMAIL' => "tickets@hctorpedo.ru",
//     );
//CEvent::Send("FEEDBACK_WITHOUT_COMPONENT", "s2", $arEventFields,"Y",112);
// if($res != NULL){
//     $name = 'Отправка формы от - '.date('d.m.Y H:i:s')."\n"; 
//     $message .= $name . json_encode($_POST)."\n".$res."\n\n"; 
//     file_put_contents("log.txt", $message, FILE_APPEND); 
// }else{
//     $name = 'Отправка формы от - '.date('d.m.Y H:i:s')."\n"; 
//     $message .= "Ошибка при отправке"."\n\n"; 
//     $utf8string = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $message), ENT_NOQUOTES, 'UTF-8');
//     file_put_contents("log.txt", json_encode($utf8string), FILE_APPEND); 
// }

function executeREST($method, $params) {
    $queryUrl = 'https://crm.stionline.ru/rest/5510/vgnhjvfu1cykoqq4/'.$method.'.json';
    $queryData = http_build_query(array(
     'fields' => $params,
     'params' => array("REGISTER_SONET_EVENT" => "Y")
    ));

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $queryUrl,
      CURLOPT_POSTFIELDS => $queryData,
    ));

    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
?>