<?
namespace Rtop\KPI;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB;
use Rtop\KPI\EventsTable;
use Rtop\KPI\BalanceTable;
use Rtop\KPI\HistoryBalanceTable;
use Rtop\KPI\Logger as Log;

class Main
{
	var $module_id = "";
	protected static $clientsRegist = [];
	protected static $clientsComp = [];
	protected static $date3months;
	protected static $date6months;
	
	function __construct()
	{
		$this->module_id = "rtop.kpi";
	}

	public static function NumberWordEndings($num, $arEnds = false)
	{
		global $APPLICATION;
		$lang = LANGUAGE_ID;
		if ($arEnds === false)
		{
			$arEnds = array('баллов', 'баллов', 'балл', 'балла');
			if (SITE_CHARSET != 'UTF-8')
				$arEnds = $APPLICATION->ConvertCharsetArray($arEnds, 'UTF-8', 'windows-1251');
		}
		if ($lang == 'ru')
		{
			if (strlen($num)>1 && substr($num, strlen($num)-2, 1) == '1')
			{
				return $arEnds[0];
			}
			else
			{
				$c = IntVal(substr($num, strlen($num)-1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return $arEnds[1];
				elseif ($c==1)
					return $arEnds[2];
				else
					return $arEnds[3];
			}
		}
		else
			return '';
	}

	public static function addBonus($func, $userID, $client)
	{

		$user = BalanceTable::getList(['filter' => ['USERID' => $userID], 'select' => ['ID', 'DEPARTMENT', 'ROLE', 'BALANCE']])->fetch();

		if(empty($user))
			return;
		//Log::writeLog1("в адд роль - ".$user['ROLE'].'funt RTOP_KPI_EVENTS_EVENT_FUNCTION - '.$func);
		$filter = [
			'%ROLE' => $user['ROLE'],
			'RTOP_KPI_EVENTS_EVENT_FUNCTION' => $func
		];

		$filter[] = [
			'LOGIC' => 'OR',
			['%DEPARTMENT' => $user['DEPARTMENT']],
			['DEPARTMENT' => false]
		];
		//Log::writeLog1("Я НА МЕСТЕ");
		$result = EventsTable::getList([
			'filter' => $filter,
			'select' => ['VALUE', 'DEPARTMENT', 'ROLE', 'EVENT']
		])->fetch();
		//Log::writeLog1('RES RES - '.$result);
		if(empty($result))
			return;

		//Log::writeLog1("ПОЧТИ ВСЕ ГУД");
		$res = BalanceTable::update($user['ID'], array('BALANCE' => new DB\SqlExpression('?# + ?f', 'BALANCE', $result['VALUE'])));
		if(!$res->isSuccess())
			return Log::writeLog($res->getErrorMessages());

		
		$res = HistoryBalanceTable::add([
			'USERID' => $userID,
			'CODE' => $func,
			'CLIENT' => $client['ID'] ? $client['ID'] : "",
			'SUM' => $result['VALUE'],
			'OFFER' => $client['PRODUCT_ID'] ? $client['PRODUCT_ID'] : 0,
			'INITIATOR' => '1',
			'TYPE' => $client['TYPE'] ? $client['TYPE'] : ""
		]);

		Log::writeLog($res->getErrorMessages());

		if(!$res->isSuccess())
			return Log::writeLog($res->getErrorMessages());
	}

	public static function addSimpleBonus($userID, $user, $params, $client)
	{
		if(stripos(",",$params['VALUE'])){
			$params['VALUE'] = str_replace(",",".",$params['VALUE']);
		}
		if(isset($params['QUANTITY'])){
			$SUM = $params['VALUE'] * $params['QUANTITY'];
		}else{
			$SUM = $params['VALUE'];
		}
		$res = BalanceTable::update($user['ID'], array('BALANCE' => new DB\SqlExpression('?# + ?f', 'BALANCE', $SUM)));
		if(!$res->isSuccess())
			return Log::writeLog($res->getErrorMessages());
		
		$res = HistoryBalanceTable::add([
			'USERID' => $userID,
			'CODE' => $params['FUNCTION'],
			'CLIENT' => $client['ID'] ? $client['ID'] : "",
			'OFFER' => $client['PRODUCT_ID'] ? $client['PRODUCT_ID'] : 0,
			'SUM' => $SUM,
			'INITIATOR' => '1',
			'TYPE' => $client['TYPE'] ? $client['TYPE'] : ""
		]);

		if(!$res->isSuccess())
			return Log::writeLog($res->getErrorMessages());
	}

	public function getOrderList($dates, $users)
	{
		$users = self::getClients($users);

		$contactIdList = array_reduce(array_column($users, "CONTACTS"), "array_merge", []);
		$companyIdList = array_reduce(array_column($users, "COMPANIES"), "array_merge", []);

		$typeContact = \CCrmOwnerType::Contact;
		$typeCompany = \CCrmOwnerType::Company;

		$arOrderFilter = [
			'>=DATE_STATUS' => $dates['FROM'],
			'<=DATE_STATUS' => $dates['TO'],
			'STATUS_ID' => 'F',
			'CANCELED' => 'N'
		];
		$arOrderFilter[] = [
			'LOGIC' => 'OR',
			['CONTACT_COMPANY_ENTITY_TYPE_ID' => $typeContact, 'CONTACT_COMPANY_ENTITY_ID' => $contactIdList],
			['CONTACT_COMPANY_ENTITY_TYPE_ID' => $typeCompany, 'CONTACT_COMPANY_ENTITY_ID' => $companyIdList]
		];

		$res = \Bitrix\Crm\Order\Order::getList([
			'order' => array("ID" => "ASC"),
			'filter' => $arOrderFilter ?: [],
			'select' => ['ID', 'CONTACT_COMPANY_' => 'CONTACT_COMPANY', 'PRICE', 'STATUS_ID', 'DATE_STATUS', 'RESPONSIBLE_ID'],
			'runtime' => [
                'CONTACT_COMPANY' => [
                    'data_type' => '\Bitrix\Crm\Binding\OrderContactCompanyTable',
                    'reference' => [
                        '=this.ID' => 'ref.ORDER_ID',
                    ],
                ]
            ]
		]);
		$orders = [];
		$all = [];
		while($order = $res->Fetch())
		{
			$orders[$order['CONTACT_COMPANY_ENTITY_TYPE_ID']][$order['CONTACT_COMPANY_ENTITY_ID']] += $order['PRICE'];
		}
		//Log::writeLogFile($orders,'Main_Orders.log');

		$allOrderRespons = \Bitrix\Crm\Order\Order::getList([
			'order' => array("ID" => "ASC"),
			'filter' => [
				'>=DATE_STATUS' => $dates['FROM'],
				'<=DATE_STATUS' => $dates['TO'],
				'STATUS_ID' => 'F',
				'CANCELED' => 'N',
				'RESPONSIBLE_ID' => array_keys($users),
			],
			'select' => ['ID', 'PRICE', 'RESPONSIBLE_ID'],
		])->fetchAll();
		foreach($allOrderRespons as $order)
		{
			$all[$order['RESPONSIBLE_ID']]['PRICE'][$order['ID']] = $order['PRICE'];
		}
		//Log::writeLogFile($all,'Main_all.log');

		$specialProducts = self::getListSpecialProduct();		

		if(!empty($specialProducts))
		{
			$res = \Bitrix\Sale\Internals\BasketTable::getList([
				'filter' => [
					"PRODUCT_ID" => $specialProducts,
					"!ORDER_ID" => false,
					'>=BASKET_ORDER_DATE_STATUS' => $dates['FROM'],
					'<=BASKET_ORDER_DATE_STATUS' => $dates['TO'],
					'BASKET_ORDER_STATUS_ID' => 'F',
					'BASKET_ORDER_RESPONSIBLE_ID' => array_keys($users),
					'BASKET_ORDER_CANCELED' => 'N'
				],
				'select' => ['BASKET_ORDER_' => 'BASKET_ORDER', 'CONTACT_COMPANY_' => 'CONTACT_COMPANY', 'PRODUCT_ID', 'ORDER_ID'],
				'runtime' => [
					'BASKET_ORDER' =>  [
	                    'data_type' => '\Bitrix\Sale\Order',
	                    'reference' => [
	                        '=this.ORDER_ID' => 'ref.ID',
	                    ],
	                ],
	                'CONTACT_COMPANY' => [
	                    'data_type' => '\Bitrix\Crm\Binding\OrderContactCompanyTable',
	                    'reference' => [
	                        '=this.ORDER_ID' => 'ref.ORDER_ID',
	                    ],
	                ]
	            ]
			]);
			while($basket = $res->Fetch())
			{
				$client = [
					'TYPE' => $basket['CONTACT_COMPANY_ENTITY_TYPE_ID'],
					'ID' => $basket['CONTACT_COMPANY_ENTITY_ID'],
					'PRODUCT' => $basket['PRODUCT_ID']
				];
				$all[$basket['BASKET_ORDER_RESPONSIBLE_ID']]['PRODUCTS'][$basket['ORDER_ID']] = $client;
			}
		}

		$listSumCompanies = array_keys($orders[$typeCompany]);

		foreach ($users as $id => &$field)
		{
			if(isset($field['CONTACTS']) && isset($orders[$typeContact]))
			{
				foreach ($field['CONTACTS'] as $entity_id => &$value)
				{
					if(isset(self::$clientsComp[$entity_id]) && !empty(array_intersect(self::$clientsComp[$entity_id], $listSumCompanies)))
						continue;
					if(isset($orders[$typeContact][$entity_id]))
						$value = ['SUM' => $orders[$typeContact][$entity_id]];
				}
			}
			if(isset($field['COMPANIES']) && isset($orders[$typeCompany]))
			{
				foreach ($field['COMPANIES'] as $entity_id => &$value)
				{
					if(isset($orders[$typeCompany][$entity_id]))
						$value = ['SUM' => $orders[$typeCompany][$entity_id]];
				}
			}
			if(isset($all[$id])){
				$field['ALL_PRICE'] = array_sum($all[$id]['PRICE']);
				$field['ALL_PRODUCTS'] = $all[$id]['PRODUCTS'];
				$field['TMP_ORDERS'] = $all[$id]['PRICE'];
			}
		}
		//Log::writeLogFile($users,'Main_users_FINAL.log');
		return $users;
	}

	protected static function getClients($users)
	{
		$userIds = array_keys($users);
		$rsCompany = \CCrmCompany::GetListEx(['ID' => 'ASC'], ['ASSIGNED_BY_ID' => $userIds], false, false, ['ID', 'ASSIGNED_BY', 'DATE_CREATE']);
		while($result = $rsCompany->Fetch()){
			if(isset($users[$result['ASSIGNED_BY']])){
				$users[$result['ASSIGNED_BY']]['COMPANIES'][$result['ID']] = $result['ID'];
				$list['COMPANIES'][$result['ID']] = strtotime($result['DATE_CREATE']);
			}
		}

		$rsContact = \Bitrix\Crm\ContactTable::getList([
			'order' => ['ID' => 'ASC'],
			'filter' => [
				'ASSIGNED_BY_ID' => $userIds
			],
			'select' => ['ID', 'ASSIGNED_BY_ID', 'DATE_CREATE', 'COMPANIES_T_ID' => 'COMPANIES_T.COMPANY_ID'],
			'runtime' => [
				'COMPANIES_T' => [
					'data_type' => '\Bitrix\Crm\Binding\ContactCompanyTable',
					'reference' => [
	                    '=this.ID' => 'ref.CONTACT_ID'
	                ],
				]
			]
		])->fetchAll();

		foreach($rsContact ?: [] as $result)
		{
			if(isset($users[$result['ASSIGNED_BY_ID']]))
			{
				$users[$result['ASSIGNED_BY_ID']]['CONTACTS'][$result['ID']] = $result['ID'];
				$list['CONTACTS'][$result['ID']] = strtotime($result['DATE_CREATE']);
				if(!empty($result['COMPANIES_T_ID']))
					$companies[$result['ID']][] = $result['COMPANIES_T_ID'];
			}
		}

		self::$clientsComp = $companies;
		self::$clientsRegist = $list;
		//Log::writeLogFile($users,'Main_users.log');
		return $users;
	}

	protected static function getListSpecialProduct()
	{
		if(!Loader::IncludeModule("crm") || !Loader::IncludeModule("catalog"))
			return;

		$iblockId = Option::get("crm", "default_product_catalog_id");
		$catalog = \CCatalog::GetByID($iblockId);

		$filter = [
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => [$iblockId, $catalog['OFFERS_IBLOCK_ID']]
		];

		$filter[] = [
			'LOGIC' => 'OR',
			['PROPERTY_DOPOLNITELNAYA_MOTIVATSIYA' => 1341],
			['PROPERTY_TOVAR_MESYATSA' => 1346],
		];

		$res = \CIBlockElement::GetList([], $filter, false, false, ['ID']);
		while($element = $res->fetch()){
			$elements[$element['ID']] = $element['ID'];
		}

		return $elements ?: [];
	}

	protected static function getClientsNoOrders6($users)
	{		

		self::$date6months = new \Bitrix\Main\Type\DateTime(date('d.m.Y', strtotime("-6 months")) . "00:00:00"); //-3 months

		$filterContacts = array_filter(array_reduce(array_column($users, "CONTACTS"), "array_merge", []), function($value){
			return (!(isset($value['SUM'])) && self::$clientsRegist['CONTACTS'][$value] < self::$date6months->getTimestamp());
		});

		$filterCompanies = array_filter(array_reduce(array_column($users, "COMPANIES"), "array_merge", []), function($value){
			return (!(isset($value['SUM'])) && self::$clientsRegist['COMPANIES'][$value] < self::$date6months->getTimestamp());
		});

		return [
			'CONTACTS' => $filterContacts,
			'COMPANIES' => $filterCompanies,
		];
	}

	public function getNoOrder6List($users)
	{
		$list = self::getClientsNoOrders6($users);

		$arOrderFilter = [
			'>=DATE_INSERT' => self::$date6months,
			'CANCELED' => 'N',
			'!STATUS_ID' => 'L'
		];

		$typeContact = \CCrmOwnerType::Contact;
		$typeCompany = \CCrmOwnerType::Company;

		if(!empty($list['CONTACTS']) && !empty($list['COMPANIES']))
		{
			$arOrderFilter[] = [
				'LOGIC' => 'OR',
				['CONTACT_COMPANY_ENTITY_TYPE_ID' => $typeContact, 'CONTACT_COMPANY_ENTITY_ID' => $list['CONTACTS']],
				['CONTACT_COMPANY_ENTITY_TYPE_ID' => $typeCompany, 'CONTACT_COMPANY_ENTITY_ID' => $list['COMPANIES']]
			];
		}
		elseif(!empty($list['CONTACTS']))
		{
			$arOrderFilter['CONTACT_COMPANY_ENTITY_TYPE_ID'] = $typeContact;
			$arOrderFilter['CONTACT_COMPANY_ENTITY_ID'] = $list['CONTACTS'];
		}
		elseif(!empty($list['COMPANIES']))
		{
			$arOrderFilter['CONTACT_COMPANY_ENTITY_TYPE_ID'] = $typeCompany;
			$arOrderFilter['CONTACT_COMPANY_ENTITY_ID'] = $list['COMPANIES'];
		}
		else
		{
			return;
		}

		$res = \Bitrix\Crm\Order\Order::getList([
			'filter' => $arOrderFilter ?: [],
			'select' => ['CONTACT_COMPANY_ENTITY_ID' => 'CONTACT_COMPANY.ENTITY_ID', 'CONTACT_COMPANY_ENTITY_TYPE_ID' => 'CONTACT_COMPANY.ENTITY_TYPE_ID', 'ID'],
			'runtime' => [
                'CONTACT_COMPANY' => [
                    'data_type' => '\Bitrix\Crm\Binding\OrderContactCompanyTable',
                    'reference' => [
                        '=this.ID' => 'ref.ORDER_ID',
                    ],
                ]
            ]
		]);

		$cntList = [];
		
		while($result = $res->fetch()){
			$cntList[$result['CONTACT_COMPANY_ENTITY_TYPE_ID']][$result['CONTACT_COMPANY_ENTITY_ID']] += 1;
		}

		$diff1 = array_diff_key(array_flip($list['CONTACTS']), $cntList[$typeContact]);
		$diff2 = array_diff_key(array_flip($list['COMPANIES']), $cntList[$typeCompany]);

		//Дальше тут какая то подлянка, у Контакта и Компании может быть разные ответственные. У Компании мб заказ, у Контакта нет - тогда контакт не трогаем. Иначе - за Компанию снимаем баллы???
	}
}