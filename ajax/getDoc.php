<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
if( $_REQUEST['ORDER_ID'] ){
	$ID = $_REQUEST['ORDER_ID'];

	CModule::IncludeModule('sale');
	$arOrder = CSaleOrder::GetByID($ID);
	if( $arOrder['COMMENTS'] ){
		echo $arOrder['COMMENTS'];
	}else{
		echo "Счет еще не сформирован!";
	}
	
}
?>
