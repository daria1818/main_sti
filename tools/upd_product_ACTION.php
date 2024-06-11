<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_BONUS_PRODUCT", "PROPERTY_HIT");//IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
$arFilter = Array("IBLOCK_ID"=>30, "!PROPERTY_BONUS_PRODUCT" => false);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
while($ob = $res->GetNextElement()){ 
	$arFields[$ob->GetFields()['ID']]= $ob->GetProperties();
}
echo count($arFields);
foreach ($arFields as $key => $prop) {
		echo "<br/>ID - " . $key;
	if(!in_array('694', $prop["HIT"]["VALUE_ENUM_ID"])){
		$NEW_PROP = Array();	
		
		if(!empty($prop["HIT"]["VALUE_ENUM_ID"])){
			$NEW_PROP["HIT"] = $prop["HIT"]["VALUE_ENUM_ID"];
		}
		
		$NEW_PROP["HIT"][] = '694';

		echo "<pre>";
		print_r($NEW_PROP);
		echo "</pre>";

		CIBlockElement::SetPropertyValuesEx($key, false, $NEW_PROP);
	}
}




// echo "<pre>";
// print_r($arFields);
// echo "</pre>";
?>