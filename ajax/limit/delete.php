<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule("sale") || !\Bitrix\Main\Loader::includeModule("catalog") || !\Bitrix\Main\Loader::includeModule("iblock") || !\Bitrix\Main\Loader::includeModule("aspro.next"))
{
	echo "failure";
	return;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST')
	return;

$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
$items = $basket->getBasketItems();
if(!empty($items))
{
	foreach ($items as &$item)
	{
		$item->delete();
	}
}
$basket->save();