<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$filename = 'articles.php';

$articles = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!$articles) {
    echo "Нет артикулов в файле или файл не может быть прочитан.";
    die;
}


if (!CModule::IncludeModule("iblock")) {
    die('Модуль "Информационные блоки" не установлен');
}

$iblockId = 30;

$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CML2_ARTICLE");
$arFilter = Array("IBLOCK_ID" => $iblockId, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $article = $arFields["PROPERTY_CML2_ARTICLE_VALUE"];
    if (in_array($article, $articles)) {
        $NeedSearch[] = $arFields['ID'];
    }
}

$fiendProductWithArticles = CCatalogSKU::getOffersList(
    $NeedSearch, 
    $iblockID = 30, 
);


foreach ($fiendProductWithArticles as $ID_Product) {
    foreach ($ID_Product as $SKU_ID => $SKU_PROP) {
        // echo "<pre>";
        // print_r($SKU_ID);
        // echo "</pre>";
        if("26210121" == $SKU_ID){
            if (CIBlockElement::Delete($SKU_ID)){
                echo "Удален - " . $SKU_ID . "<br/>";
            }else{
                echo "Ошибка при удалении";
            }
        }
    }
}

?>