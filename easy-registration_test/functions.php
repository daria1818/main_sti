<?
function check_code($code){
	$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_*");
	$arFilter = Array("IBLOCK_ID"=>100,"IBLOCK_SECTION_ID" => 9082, "ACTIVE"=>"Y", "=PROPERTY_CODE_LINK"=>$code);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
	while($ob = $res->GetNextElement())
	{
	 $arFields = $ob->GetProperties();
     $arFields["ID"] = $ob->GetFields()["ID"]; 
	}
	$res = $arFields;
	if($res){
		return $arFields;
	}else{
		return false;
	}
}
?>