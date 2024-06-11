<?
namespace Rtop\KPI;

use Bitrix\Crm\ContactTable;
use Bitrix\Crm\CompanyTable;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\EventsTable;
use Rtop\KPI\Main as RtopMain;
use Rtop\KPI\Premission;
use Rtop\KPI\Logger as Log;

class EventManager
{	
	protected static $siteId = 'pn';
	protected static $user1C = 1;

	function OnAfterCrmContactAddHandler($arFields)
	{
		if(Premission::get() === '')
			return;
		$client = ['ID' => $arFields['ID'],	'TYPE' => 'Contact'];
		RtopMain::addBonus(__FUNCTION__, $arFields["CREATED_BY_ID"], $client);
	}

	function OnAfterCrmCompanyAddHandler($arFields)
	{
		if(Premission::get() === '')
			return;
		$client = ['ID' => $arFields['ID'],	'TYPE' => 'Company'];
		RtopMain::addBonus(__FUNCTION__, $arFields["CREATED_BY_ID"], $client);
	}

	function OnBeforeCrmContactUpdateHandler($arFields)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != $userId)

		$diff = [];

		$itemsContact = ContactTable::getById($arFields['ID'])->fetch();

		if(!empty($arFields['FM']))
		{
			$upgradeFM = [];
			foreach ($arFields['FM'] as $key => $value)
			{
				$column = array_column($value, 'VALUE');
				if(!empty($column))
					$upgradeFM[$key] = implode(";", $column);
			}

			$dbResMultiFields = \CCrmFieldMulti::GetList(['ID' => 'ASC'], ['ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $arFields['ID']]);
			while($arMultiField = $dbResMultiFields->fetch())
			{
				$itemsContact['FM'][$arMultiField['TYPE_ID']][] = $arMultiField['VALUE'];
			}

			foreach($itemsContact['FM'] ?: [] as $code => $field)
			{
				$itemsContact['FM'][$code] = implode(";", $field);
			}

			$diff = array_diff($upgradeFM, $itemsContact['FM']);
		}

		if(empty($diff))
		{
			unset($arFields['FM']);
			unset($arFields['~DATE_MODIFY']);
			unset($itemsContact['FM']);

			$diff = array_diff($arFields, $itemsContact);
		}

		if(!empty($diff))
		{
			RtopMain::addBonus(__FUNCTION__, $arFields['MODIFY_BY_ID'], ['ID' => $arFields['ID'], 'TYPE' => 'Contact']);
		}
	}

	function OnBeforeCrmCompanyUpdateHandler($arFields)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != $userId)
			return;
		//Что делать с адресом доставки?

		$diff = [];

		$itemsCompany = CompanyTable::getById($arFields['ID'])->fetch();

		if(!empty($arFields['FM']))
		{
			$upgradeFM = [];
			foreach ($arFields['FM'] as $key => $value)
			{
				$column = array_column($value, 'VALUE');
				$implode = implode(";", $column);
				if($implode <> "")
					$upgradeFM[$key] = $implode;
			}

			$dbResMultiFields = \CCrmFieldMulti::GetList(['ID' => 'ASC'], ['ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $arFields['ID']]);
			while($arMultiField = $dbResMultiFields->fetch())
			{
				$itemsCompany['FM'][$arMultiField['TYPE_ID']][] = $arMultiField['VALUE'];
			}

			foreach($itemsCompany['FM'] ?: [] as $code => $field)
			{
				$itemsCompany['FM'][$code] = implode(";", $field);
			}

			$diff = array_diff($upgradeFM, $itemsCompany['FM']);
		}

		if(empty($diff))
		{
			unset($arFields['FM']);
			unset($arFields['~DATE_MODIFY']);
			unset($itemsCompany['FM']);

			if(!empty($arFields["CONTACT_ID"])){
				$itemsCompany['CONTACT_ID'] = implode(";", \Bitrix\Crm\Binding\ContactCompanyTable::getCompanyContactIDs($arFields['ID']));
				$arFields["CONTACT_ID"] = implode(";", $arFields["CONTACT_ID"]);
			}
			$diff = array_diff($arFields, $itemsCompany);
		}

		if(!empty($diff))
		{
			RtopMain::addBonus(__FUNCTION__, $arFields['MODIFY_BY_ID'], ['ID' => $arFields['ID'],	'TYPE' => 'Company']);
		}
	}

	function OnSaleOrderSavedHandler(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $userId == self::$user1C)
			return;

		$isNew = $event->getParameter("IS_NEW");
		if($isNew){
			$order = $event->getParameter("ENTITY");

			if($order->getSiteId() != self::$siteId)
				return;

			$fields = [
				'ID' => $order->getId(),
				'RESPONSIBLE_ID' => $order->getField("RESPONSIBLE_ID"),
				'CREATED_BY' => $order->getField("CREATED_BY")
			];

			RtopMain::addBonus(__FUNCTION__, $fields["CREATED_BY"], self::getBuyerForBonus($order));
		}
	}

	function OnSaleOrderBeforeSavedHandler(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $userId == self::$user1C)
			return;

		$values = $event->getParameter("VALUES");
		$exitKeys = array_flip(['DATE_ALLOW_DELIVERY', 'STATUS_ID', 'ALLOW_DELIVERY']); //'PRICE'

		if(empty($values) || !empty(array_intersect_key($values, $exitKeys)))
			return;

		$order = $event->getParameter("ENTITY");

		if($order->getSiteId() != self::$siteId || $userId < 1 || $order->isNew())
			return;

		RtopMain::addBonus(__FUNCTION__, $userId, self::getBuyerForBonus($order));
	}

	function OnSaleStatusOrderChangeHandler(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());

		// $tstAr = array(
		// 	"1" => $userId,
		// 	"2" => self::$user1C,
		// );
		// $tstaar = print_r($tstAr, true);
		// Log::writeLog1('1 - '.$tstaar);
		if(Premission::get() === '' || $userId == self::$user1C)
			return;
		$order = $event->getParameter("ENTITY");
		// $tstAr1 = array(
		// 	"1" => $order->getSiteId(),
		// 	"2" => self::$siteId,
		// 	"3" => $userId,
		// 	"4" => $order->getField('STATUS_ID'),
		// );
		// $tstaar1 = print_r($tstAr1, true);
		// Log::writeLog1('2 - '.$tstaar1);
		if($order->getSiteId() != self::$siteId || $userId < 1 || $order->getField('STATUS_ID') != 'P')
			return;
		$res = RtopMain::addBonus(__FUNCTION__, $userId, self::getBuyerForBonus($order));
		// Log::writeLog1("3 - ЗАНЕС".$res);	
	}

	function OnSaleStatusOrderChangeDealSuccess(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());
		$source_order = '';

		/*if(Premission::get() === '' || $userId == self::$user1C)
			return;*/

		$order = $event->getParameter("ENTITY");
		$orderUserId = $order->getField('USER_ID');
		$orderStatusId = $order->getField('STATUS_ID');

		if($order->getSiteId() != self::$siteId || $userId < 1 || $orderStatusId != 'W')
			return;

		$propertyCollection = $order->getPropertyCollection();
		foreach($propertyCollection as $popertyObj)
		{
			if($popertyObj->getField('CODE') == "SOURCE_ORDER") $source_order = $popertyObj->getValue();
			if($popertyObj->getField('CODE') == "BMUID") $BMUID = $popertyObj->getValue();
		}
		if($source_order != 'DEAL_CRM')
			return;

		RtopMain::addBonus(__FUNCTION__, $BMUID, self::getBuyerForBonus($order));
	}
	function setBonusCreateOrderOfDeal(BitrixEvent $event)
	{
		
	}
	function OnSaleOrderCanceledHandler(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $userId == self::$user1C)
			return;

		$order = $event->getParameter("ENTITY");

		if($order->getSiteId() != self::$siteId || $userId < 1 || $order->getField('CANCELED') != 'Y' || empty($order->getField("REASON_CANCELED")))
			return;
		
		RtopMain::addBonus(__FUNCTION__, $userId, self::getBuyerForBonus($order));
	}

	function OnBeforeCrmContactUpdateStaging($arFields)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != $userId)
			return;

		$itemsContact = ContactTable::getList(['filter' => ['ID' => $arFields['ID'], 'ASSIGNED_BY_ID' => $arFields['ASSIGNED_BY_ID']], 'select' => ['ID']])->fetch();

		if(empty($itemsContact))
		{
			$client = ['ID' => $arFields['ID'],	'TYPE' => 'Contact'];
			RtopMain::addBonus(__FUNCTION__, $arFields["ASSIGNED_BY_ID"], $client);
		}
	}

	function OnBeforeCrmCompanyUpdateStaging($arFields)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != $userId)
			return;

		$itemsCompany = CompanyTable::getList(['filter' => ['ID' => $arFields['ID'], 'ASSIGNED_BY_ID' => $arFields['ASSIGNED_BY_ID']], 'select' => ['ID']])->fetch();
		if(empty($itemsCompany))
		{
			$client = ['ID' => $arFields['ID'],	'TYPE' => 'Company'];
			RtopMain::addBonus(__FUNCTION__, $arFields["ASSIGNED_BY_ID"], $client);
		}
	}

	function OnBeforeCrmContactUpdateWithdrawal($arFields)
	{
		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != '1')
			return;

		$client = ['ID' => $arFields['ID'],	'TYPE' => 'Contact'];
		RtopMain::addBonus(__FUNCTION__, $arFields["MODIFY_BY_ID"], $client);
	}

	function OnBeforeCrmCompanyUpdateWithdrawal($arFields)
	{
		if(Premission::get() === '' || $arFields['ASSIGNED_BY_ID'] != '1')
			return;
		
		$client = ['ID' => $arFields['ID'],	'TYPE' => 'Company'];
		RtopMain::addBonus(__FUNCTION__, $arFields["MODIFY_BY_ID"], $client);
	}

	public function setBonusVolumeOrders5000($userList, $params)
	{
		//self::checkTheAmout(0, 5000, $userList, $params);
		self::checkTheAmout(0, 7000, $userList, $params);
	}

	public function setBonusVolumeOrders15000($userList, $params)
	{
		//self::checkTheAmout(5000, 15000, $userList, $params);
		self::checkTheAmout(7000, 20000, $userList, $params);
	}

	public function setBonusVolumeOrders21000($userList, $params)
	{
		//self::checkTheAmout(15000, 21000, $userList, $params);
		self::checkTheAmout(20000, 35000, $userList, $params);
	}

	public function setBonusVolumeOrders28000($userList, $params)
	{
		//self::checkTheAmout(21000, 28000, $userList, $params);
	}

	public function setBonusVolumeOrders35000($userList, $params)
	{
		//self::checkTheAmout(28000, 35000, $userList, $params);
	}

	public function setBonusVolumeOrders75000($userList, $params)
	{
		//self::checkTheAmout(35000, 75000, $userList, $params);
		self::checkTheAmout(35000, 100000, $userList, $params);
	}

	public function setBonusVolumeOrders75001($userList, $params)
	{
		//self::checkTheAmout(75000, 100000000, $userList, $params);
		self::checkTheAmout(100000, 100000000, $userList, $params);
	}



	//upd начисления за заказы для менеджеров
	public function setBonusVolumeOrdersManagers5000($userList, $params)
	{
		self::checkTheAmoutManagers(0, 7000, $userList, $params);
	}

	public function setBonusVolumeOrdersManagers15000($userList, $params)
	{
		self::checkTheAmoutManagers(7000, 20000, $userList, $params);
	}

	public function setBonusVolumeOrdersManagers21000($userList, $params)
	{
		self::checkTheAmoutManagers(20000, 35000, $userList, $params);
	}

	public function setBonusVolumeOrdersManagers75000($userList, $params)
	{
		self::checkTheAmoutManagers(35000, 100000, $userList, $params);
	}

	public function setBonusVolumeOrdersManagers75001($userList, $params)
	{
		self::checkTheAmoutManagers(100000, 100000000, $userList, $params);
	}
	//end начисления за заказы для менеджеров



	public function setBonusVolumeOrdersMonth($userList, $params)
	{
		foreach($userList as $userid => $user)
		{
			if(isset($user['ALL_PRICE']) && $user['ALL_PRICE'] > 1500000)
			{
				RtopMain::addSimpleBonus($userid, ['ID' => $user['ID']], $params, []);
			}
		}
	}

	public function setBonusVolumeOrdersQuarter($userList, $params)
	{
		//???
	}

	public function absenceOrders3months($users, $params)
	{

	}

	public function absenceOrders6months($userList, $params)
	{

	}

	public function setBonusSpecialProduct($userList, $params)
	{
		$typeCompany = \CCrmOwnerType::Company;

		foreach($userList as $userid => $user)
		{
			foreach($user['ALL_PRODUCTS'] ?:[] as $client)
			{
				RtopMain::addSimpleBonus(
					$userid, 
					['ID' => $user['ID']], 
					$params, 
					['ID' => $client['ID'], 'TYPE' => ($typeCompany == $client['TYPE'] ? 'Company' : 'Contact'), 'OFFER' => $client['PRODUCT']]
				);
			}
		}
	}

	protected static function checkTheAmout($from, $to, $userList, $params)
	{
		if(is_numeric($params['MIN_COST']))
		{
			//$from = $params['MIN_COST'];
		}
		if(is_numeric($params['MAX_COST']))
		{
			//$to = $params['MAX_COST'];
		}

		foreach($userList as $userid => $user)
		{
			foreach($user['COMPANIES'] ?:[] as $id => $value)
			{
				if(!isset($value['SUM']))
					continue;
				if($value['SUM'] > $from && $value['SUM'] <= $to){
					RtopMain::addSimpleBonus($userid, ['ID' => $user['ID']], $params, ['ID' => $id, 'TYPE' => 'Company']);
					Log::writeLogFile($params['FUNCTION'] .  "\nНачисляю " . $userid . " за компанию " . $id . " , баллов " . $params['VALUE'] . "\n",'Event_addBONUS.log');
				}
			}
			foreach($user['CONTACTS'] ?:[] as $id => $value)
			{
				if(!isset($value['SUM']))
					continue;
				if($value['SUM'] > $from && $value['SUM'] <= $to){
					RtopMain::addSimpleBonus($userid, ['ID' => $user['ID']], $params, ['ID' => $id, 'TYPE' => 'Contact']);
					Log::writeLogFile($params['FUNCTION'] .  "\nНачисляю " . $userid . " за контакт "  . $id . " , баллов " . $params['VALUE'] . "\n",'Event_addBONUS.log');
				}
			}
		}
	}
	//new func for add bonus managers order
	protected static function checkTheAmoutManagers($from, $to, $userList, $params)
	{
		if(is_numeric($params['MIN_COST']))
		{
			//$from = $params['MIN_COST'];
		}
		if(is_numeric($params['MAX_COST']))
		{
			//$to = $params['MAX_COST'];
		}

		foreach($userList as $userid => $user)
		{
			foreach($user['TMP_ORDERS'] ?:[] as $id => $value)
			{
				if(!isset($value))
					continue;
				if($value > $from && $value <= $to){
					RtopMain::addSimpleBonus($userid, ['ID' => $user['ID']], $params, ['ID' => $id, 'TYPE' => 'Order']);
					Log::writeLogFile($params['FUNCTION'] .  "\nНачисляю " . $userid . " за заказ "  . $id . " , баллов " . $params['VALUE'] . "\n",'Event_addBONUS.log');
				}
			}

		}
	}
	protected static function getBuyerForBonus($order)
	{
		$client = [];
		$communications = $order->getContactCompanyCollection();
		$companies = $communications->getCompanies();

		foreach ($companies ?:[] as $value)
		{
			$client = ['ID' => $value->getField('ENTITY_ID'), 'TYPE' => 'Company'];
			break;
		}

		if(empty($client))
		{			
			$contacts = $communications->getContacts();
			foreach ($contacts ?:[] as $value)
			{
				$client = ['ID' => $value->getField('ENTITY_ID'), 'TYPE' => 'Contact'];
				break;
			}
		}
		return $client;
	}

	function OnSaleStatusOrderChangeOffers(BitrixEvent $event)
	{
		global $USER;
		$userId = intval($USER->GetId());
		$tstAr = array(
			"1" => $userId,
			"2" => self::$user1C,
		);
		$tstaar = print_r($tstAr, true);
		Log::writeLog('1 - '.$tstaar);
		if(Premission::get() === '' || $userId == self::$user1C)
			return;

		$order = $event->getParameter("ENTITY");
		$tstAr1 = array(
			"1" => $order->getSiteId(),
			"2" => self::$siteId,
			"3" => $userId,
			"4" => $order->getField('STATUS_ID'),
		);

		$userId = $order->getField('CREATED_BY');
		$tstaar1 = print_r($order, true);
		Log::writeLog('2 - '.$order->getField('CREATED_BY'));
		if($order->getSiteId() != self::$siteId || $userId < 1 || $order->getField('STATUS_ID') != 'F')
			return;

		if(!Loader::IncludeModule("crm") || !Loader::IncludeModule("catalog"))
			return;

		$IDS = [];
		$products = [];
		$date = $order->getField('DATE_INSERT')->format('Y-m-d');

		$basket = $order->getBasket();
		$basketItems = $basket->getBasketItems();

		foreach ($basketItems as $basketItem)
		{
			$IDS[] = $basketItem->getProductId();
			$QUANTITY_PRUDUCT[$basketItem->getProductId()] = $basketItem->getQuantity();

		}
		$tstaar2 = print_r($IDS, true);


		if(empty($IDS))
			return;

		$filterElement = [
			'ID' => $IDS,
			'!PROPERTY_KPI_BALLS' => false,
			'<=PROPERTY_KPI_PERIOD_FROM' => $date,
			'>=PROPERTY_KPI_PERIOD_TO' => $date,
		];

		$res = \CIBlockElement::GetList([], $filterElement, false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_KPI_BALLS']);
		while($element = $res->fetch()){
			$element["QUANTITY"] = $QUANTITY_PRUDUCT[$element["ID"]];
			$products[] = $element;
		}
		$tstaar3 = print_r($products, true);
		Log::writeLog('4 - '.$tstaar3);
		if(empty($products))
			return;

		$client = self::getBuyerForBonus($order);

		$user = BalanceTable::getList(['filter' => ['USERID' => $userId], 'select' => ['ID', 'DEPARTMENT', 'ROLE', 'BALANCE']])->fetch();
		$id_responsible = $order->getField('RESPONSIBLE_ID');
		$responsible_user = BalanceTable::getList(['filter' => ['USERID' => $id_responsible], 'select' => ['ID', 'DEPARTMENT', 'ROLE', 'BALANCE']])->fetch();
		$tstaar4 = print_r($user, true);
		Log::writeLog('5 - '.$tstaar4);
		if(empty($user))
			return;

		$filterEvent = [
			'%ROLE' => $user['ROLE'],
			'RTOP_KPI_EVENTS_EVENT_FUNCTION' => __FUNCTION__
		];

		$filterEvent[] = [
			'LOGIC' => 'OR',
			['%DEPARTMENT' => $user['DEPARTMENT']],
			['DEPARTMENT' => false]
		];

		$result = EventsTable::getList([
			'filter' => $filterEvent,
			'select' => ['VALUE', 'DEPARTMENT', 'ROLE', 'EVENT']
		])->fetch();
		$tstaar5 = print_r($result, true);
		Log::writeLog('6 - '.empty($result));
		
		if(empty($result)){
			return;
		}


		foreach($products as $product)
		{
			$tstaar6 = print_r($product, true);
			Log::writeLog('Начисляю за продукт- <br/>'.$tstaar6);
			$client['PRODUCT_ID'] = $product['ID'];
			RtopMain::addSimpleBonus($userId, $user, ['VALUE' => $product['PROPERTY_KPI_BALLS_VALUE'], 'QUANTITY' => $product['QUANTITY'],'FUNCTION' => __FUNCTION__], $client);
			RtopMain::addSimpleBonus($id_responsible, $responsible_user, ['VALUE' => $product['PROPERTY_KPI_BALLS_VALUE'], 'QUANTITY' => $product['QUANTITY'],'FUNCTION' => __FUNCTION__], $client);
		}
		Log::writeLog('all true');
	}

	function onCrmDealUpdateProductKO($arFields)
	{
		
	}

	function onCrmDealUpdateProductIM($arFields)
	{
		
	}

	function onCrmDealUpdateProductAll($arFields, $deal)
	{
		global $USER;
		$userId = intval($USER->GetId());

		\CModule::IncludeModule('crm');

		$stageId = $arFields['ID'];

		if ($deal['UF_CRM_1615879524047'] == '131') {
			$function = 'onCrmDealUpdateProductKO';
		} else {
			$function = 'onCrmDealUpdateProductIM';
		}

		$products = [];

		$res = \CCrmProductRow::GetList(['ID'=>'DESC'], ['OWNER_ID' => $stageId], false, false, []);   

		while($arProduct = $res->fetch()) {
			$products[$arProduct['PRODUCT_ID']] = $arProduct['QUANTITY'];
		}

		if (!empty($products)) {
			\CModule::IncludeModule('iblock');

			if ($deal['COMPANY_ID']) {
				$contact = ['ID' => $deal['COMPANY_ID'], 'TYPE' => 'Company'];
			} elseif ($deal['CONTACT_ID']) {
				$contact = ['ID' => $deal['CONTACT_ID'], 'TYPE' => 'Contact'];
			}

			$rsProducts = \CIBlockElement::GetList([], ['ID' => array_keys($products)], false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_KPI_BALLS', 'PROPERTY_CML2_LINK']);

			$offers = [];

			while($arProduct = $rsProducts->fetch()) {
				if ($arProduct['PROPERTY_CML2_LINK_VALUE']) {
					$offers[$arProduct['PROPERTY_CML2_LINK_VALUE']][] = $arProduct['ID'];
				} else {

					$contact['PRODUCT_ID'] = $arProduct['ID'];

					if ($arProduct['PROPERTY_KPI_BALLS_VALUE']) {
						RtopMain::addSimpleBonus($deal["ASSIGNED_BY_ID"], ['ID' => $deal["ASSIGNED_BY_ID"]], ['VALUE' => floatval($arProduct['PROPERTY_KPI_BALLS_VALUE']), 'QUANTITY' => intval($products[$arProduct['ID']]),'FUNCTION' => $function], $contact);
					}
				}
			}

			if (!empty($offers)) {
				$rsProducts = \CIBlockElement::GetList([], ['ID' => array_keys($offers)], false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_KPI_BALLS']);

				while($arProduct = $rsProducts->fetch()) {
					if ($arProduct['PROPERTY_KPI_BALLS_VALUE']) {
						foreach($offers[$arProduct['ID']] as $key => $offer) {
							$contact['PRODUCT_ID'] = $offer;

							RtopMain::addSimpleBonus($deal["ASSIGNED_BY_ID"], ['ID' => $deal["ASSIGNED_BY_ID"]], ['VALUE' => floatval($arProduct['PROPERTY_KPI_BALLS_VALUE']), 'QUANTITY' => intval($products[$offer]),'FUNCTION' => $function], $contact);
						}
					}
				}
			}
		}
	}

	function onCrmDealUpdateKO($arFields)
	{
		global $USER;
		$userId = intval($USER->GetId());

		if ($arFields['STAGE_ID'] == 'WON') {
			\CModule::IncludeModule('crm');

			$stageId = $arFields['ID'];

			$categoryId = \CCrmDeal::GetCategoryID($stageId);

			if ($categoryId == 0) {
				$deal = \CCrmDeal::GetList([], ['ID' => $stageId], ['ID', 'ASSIGNED_BY_ID', 'UF_CRM_1615879524047', 'COMPANY_ID', 'CONTACT_ID', 'UF_CRM_1668508785033'])->fetch();

				if ($deal['UF_CRM_1668508785033'] != 1) {
					if ($deal['COMPANY_ID']) {
						$contact = ['ID' => $deal['COMPANY_ID'], 'TYPE' => 'Company'];
					} elseif ($deal['CONTACT_ID']) {
						$contact = ['ID' => $deal['CONTACT_ID'], 'TYPE' => 'Contact'];
					}

					if ($deal['UF_CRM_1615879524047'] == '131') {
						RtopMain::addBonus(__FUNCTION__, $deal["ASSIGNED_BY_ID"], $contact);
					}

					self::onCrmDealUpdateProductAll($arFields, $deal);

					$deal = new \CCrmDeal(false);
					$fields = ['UF_CRM_1668508785033' => 1];
					$deal->Update($stageId, $fields);
				}
			}
		}
	}
}
?>