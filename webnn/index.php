<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("webnn");


$el = new CIBlockElement;
$arSelect = array("ID", "ACTIVE");
$arOrder = array('SORT'=>'ASC');
$arFilter = array(
    'SECTION_ID'=>8374, // Id категории
    'IBLOCK_ID' => 30,
);

$res = CIBlockElement::GetList($arOrder, $arFilter, $arSelect);

while ($aItem = $res->GetNext())
{
    if($aItem['ACTIVE'] === 'N') {
        $el->Update(
            $aItem['ID'], // айди элемента
            ['ACTIVE' => 'Y'],
            true
        );
	}
}









?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
