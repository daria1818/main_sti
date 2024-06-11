<?
use \Bitrix\Main\Loader,
	\Bitrix\Catalog,
	\Bitrix\Iblock\ElementPropertyTable,
	\Bitrix\Sale;


class RtopUpdateCustomPrice
{
	protected static $customPrice = [];
	const AUTOPRICE_PROP_ID = 1410;
	const AUTOPRICE_PRICE_ID = 11;
	const BASE_PRICE_ID = 3;

	public static function onBeforeUpdatePriceHandler(\Bitrix\Main\Entity\Event $event)
	{
		// $primary = $event->getParameter('primary');
		// $fields = $event->getParameter('fields');

		// \LoggerRtop::writeLog($_SERVER['DOCUMENT_ROOT'] . '/1updateprice.log', [$primary, $fields]);

		// if($fields['CATALOG_GROUP_ID'] == self::BASE_PRICE_ID)
		// {
		// 	if(!isset($fields['PRODUCT_ID']) || empty($fields['PRODUCT_ID']))
		// 	{
		// 		$fields['PRODUCT_ID'] = self::getProductID($primary['ID']);
		// 	}

		// 	if(!empty($fields['PRODUCT_ID']))
		// 	{
		// 		$autoPrice = ElementPropertyTable::getList([
		// 			'filter' => [
		// 				'IBLOCK_ELEMENT_ID' => $fields['PRODUCT_ID'], 
		// 				'IBLOCK_PROPERTY_ID' => self::AUTOPRICE_PROP_ID
		// 			],
		// 			'select' => ['ID']
		// 		])->fetch();

		// 		if(empty($autoPrice))
		// 			self::$customPrice[$fields['PRODUCT_ID']] = $fields['PRICE'];
		// 	}
		// }

		// if($fields['CATALOG_GROUP_ID'] == self::AUTOPRICE_PRICE_ID)
		// {
		// 	if(!isset($fields['PRODUCT_ID']) || empty($fields['PRODUCT_ID']))
		// 	{
		// 		$fields['PRODUCT_ID'] = self::getProductID($primary['ID']);
		// 	}

		// 	if(isset(self::$customPrice[$fields['PRODUCT_ID']]))
		// 	{
		// 		$result = new \Bitrix\Main\Entity\EventResult();
		// 		$fields['PRICE'] = self::$customPrice[$fields['PRODUCT_ID']];
		// 		$result->modifyFields($fields);
		// 		unset(self::$customPrice[$fields['PRODUCT_ID']]);
		// 		return $result;
		// 	}
		// }
	}

	private function getProductID($ID)
	{
		$arPrice = Catalog\Model\Price::getList([
			'filter' => [
				'ID' => $ID
			],
			'select' => ['ID', 'PRODUCT_ID']
		])->fetch();

		return $arPrice['PRODUCT_ID'];
	}
}