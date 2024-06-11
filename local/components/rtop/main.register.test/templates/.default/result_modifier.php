<?
$arResult["arHL"] = getHlElementsList(31);
foreach ($arResult["arHL"] as $el) {
     $arResult['EVENT_CITY'][$el["UF_CITY"]] = $el["UF_CITY"];
}
sort($arResult['EVENT_CITY']);
// $arResult['EVENT_CITY'] = array_unique($arResult['EVENT_CITY']);
?>