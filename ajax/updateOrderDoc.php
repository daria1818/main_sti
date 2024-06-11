<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?

// if( $_REQUEST['doc'] && $_REQUEST['id'] ){
	$ID = $_REQUEST['id'];
	$DOC = $_REQUEST['doc'];

	CModile::IncludeModule('sale');
	$arOrder = CSaleOrder::GetByID($ID);

	echo "<pre>".print_r($arOrder, true)."</pre>";die();

// }
?>