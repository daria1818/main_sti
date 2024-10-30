<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock'); 

function create_link(){
    global $USER;
    $el = new CIBlockElement;
    $PROP = array();
    $PROP[1497] = $_POST["qr_hash"];  // Присваиваем значение свойству с кодом 1497
    $PROP[1498] = date("d.m.Y", strtotime('+1 years'));

    if (isset($_POST["free_delivery"]) && $_POST["free_delivery"] === 'on') {
        // Если чекбокс отмечен, свойство 1504 оставляем пустым, а 1521 заполняем значением ID 3080
        $PROP[1504] = 0;
        $PROP[1521] = array("VALUE" => 3080);
    } else {
        // Иначе заполняем свойство 1504 значением из формы
        $PROP[1504] = $_POST["currency"];
    }

    $arLoadProductArray = Array(
        "MODIFIED_BY"    => $USER->GetID(),
        "IBLOCK_SECTION_ID" => 9055,
        "IBLOCK_ID"      => 98,
        "PROPERTY_VALUES"=> $PROP,
        "NAME"           => "Ссылка " . $_POST["qr_hash"],
    );

    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
        $res = "успешно - " . $PRODUCT_ID;
    } else {
        $res = "ошибка - " . $el->LAST_ERROR;
    }
    return $res;
}

function check_links($hash){
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM");
    $arFilter = Array("IBLOCK_ID" => 98, "ACTIVE_DATE" => "Y", "IBLOCK_SECTION_ID" => 9055, "ACTIVE" => "Y", "=PROPERTY_CODE_LINK" => $hash);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
    
    $arFields = array(); // Инициализируем как пустой массив

    while ($ob = $res->GetNextElement()) {
        $arFields[] = $ob->GetFields()['ID'];
    }

    return count($arFields);
}

if (isset($_POST["qr_hash"])) { 
    $check = check_links($_POST["qr_hash"]);
    if (!$check) {
        $res = create_link();
        $result = array(
            'qr_hash' => $_POST["qr_hash"],
            'qr_date' => date("d.m.Y"),
            'res' => $res,
        ); 
        echo json_encode($result); 
    } else {
        $result = array(
            'qr_hash' => $_POST["qr_hash"],
            'res' => 'Ссылка уже существует',
        ); 
        echo json_encode($result); 
    }
}
?>
