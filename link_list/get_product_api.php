<?php
header("Content-Type: application/json");
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

function getProductsLinks() {
    if(CModule::IncludeModule("iblock")){
        $arSelect = array("ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_CML2_ARTICLE");
        $arFilter = array("IBLOCK_ID" => 30, "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        $i=0;
        while($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();
            foreach($arProps["CML2_ARTICLE"]["VALUE"] as $art){
                $artical = $art;
            }
            $arr[$i]['link'] = 'https://stionline.ru'.$arFields["DETAIL_PAGE_URL"];
            $arr[$i]['art'] =  $artical;
            $i++;
        }
    return $arr;
    }
}

if (isset($_POST['method']) && $_POST['method'] == 'getProductsLinks') {
    $response = getProductsLinks();
    echo json_encode($response);
} else {
    echo json_encode(["error" => "Invalid or missing method"]);
}
?>