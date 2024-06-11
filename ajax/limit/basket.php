<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule("sale") || !\Bitrix\Main\Loader::includeModule("catalog") || !\Bitrix\Main\Loader::includeModule("iblock") || !\Bitrix\Main\Loader::includeModule("aspro.next"))
{
	echo "failure";
	return;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST')
	return;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$data = $request->getPostList()->toArray();

$sections = request_basket();
if($sections == 'empty'){
	echo 'approved';
	die();
}

if($data['limit'] == 'Y')
{
	echo (in_array($data['section_id'], $sections) ? 'approved' : 'forbidden');
}
else
{
	$res = CNextCache::CIBlockSection_GetList([], ['IBLOCK_ID' => 30, 'ID' => $sections, '!UF_LIMIT_BASKET' => false], false, ['ID', 'UF_LIMIT_BASKET'], false);
	echo (empty($res) ? 'approved' : 'forbidden');
}

function request_basket()
{
	$basketItems = \Bitrix\Sale\Basket::getList([
	    'select' => ['NAME', 'PRODUCT_ID', 'PRODUCT_XML_ID', 'CUSTOM_XML', 'CUSTOM_SECTION_ID' => 'PRODUCT_SECTION.IBLOCK_SECTION_ID'],
	    'filter' => [
			'=FUSER_ID' => \Bitrix\Sale\Fuser::getId(), 
			'=ORDER_ID' => null,
			'=LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
			'=CAN_BUY' => 'Y',
			'DELAY' => 'N'
		],
	    'runtime' => [
	    	new \Bitrix\Main\ORM\Fields\ExpressionField('CUSTOM_XML', 'SUBSTRING_INDEX(%s, "#", 1)', ["PRODUCT_XML_ID"]),
	        new \Bitrix\Main\Entity\ReferenceField(
	            'PRODUCT_SECTION', 
	            \Bitrix\Iblock\ElementTable::class,
	            \Bitrix\Main\ORM\Query\Join::on('this.CUSTOM_XML', 'ref.XML_ID')->whereNotNull('ref.IBLOCK_SECTION_ID')
	        ),
	    ]
	])->fetchAll();
	if(empty($basketItems))
		return 'empty';
	return array_column($basketItems, 'CUSTOM_SECTION_ID');	 
}