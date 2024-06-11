<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule("iblock");
// function findCatalog(){
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

// return $arr;
// }

echo '<pre>';
print_r($arr);
echo '</pre>';



// if (file_put_contents("catalog.txt", serialize($arr['catalog'])) === false) {
//     echo 'Error';
// } else {
//     echo 'Write';
// }
?>