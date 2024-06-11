<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');?>
<?
use Bitrix\Main\Application, 
    Bitrix\Main\Context, 
    Bitrix\Main\Request, 
    Bitrix\Main\Server;

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$artnumber = $request->get('artnumber'); 

if(!empty($artnumber)):
	$res = CIBlockElement::GetList(
		[],
		['IBLOCK_ID' => 30, 'PROPERTY_CML2_ARTICLE' => $artnumber],
		false,
		false,
		['IBLOCK_ID', 'ID', 'NAME', 'DETAIL_PAGE_URL']
	);
	while($ob = $res->GetNextElement())
	{
		$arField = $ob->GetFields();
	}

	if(!empty($arField['DETAIL_PAGE_URL']))
	{
		LocalRedirect($arField['DETAIL_PAGE_URL']);
	}
endif;
LocalRedirect('/');
?>

<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');?>