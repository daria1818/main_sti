<?
function check_code($code){
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CODE_LINK","PROPERTY_FREE_DELIVERY");
    $arFilter = Array("IBLOCK_ID" => 98, "ACTIVE_DATE" => "Y", "IBLOCK_SECTION_ID" => 9055, "ACTIVE" => "Y", "=PROPERTY_CODE_LINK" => $code);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
    
    $arFields = array(); // Инициализируем как пустой массив

    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetProperties();
    }

    if (!empty($arFields)) {
        return $arFields;
    } else {
        return false;
    }
}

?>