<?
namespace Rubyroid\Loyality;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Web;
use \Bitrix\Sale;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Main\Application;
use \Bitrix\Sale\Internals;
use \Bitrix\Currency;
use \Bitrix\Main\Context;
use \Rubyroid\Loyality\RBRequests;
use \Rubyroid\Loyality\RBTransactions;

class RBprogramm
{
	protected static $order;
	protected static $module_id = "rubyroid.bonusloyalty";
	protected static $login_system;
	protected static $password_system;
	protected static $persent_check;
	protected static $arFields = [];

	public function __construct()
    {      
    }

	public static function init()
	{
		self::$login_system = trim(Option::get(self::$module_id, 'login_system'));
		self::$password_system = trim(Option::get(self::$module_id, 'password_system'));
		if(!empty(self::$login_system) && !empty(self::$password_system))
			return true;
		else
			return false;
	}

	private static function objectToArray($object, $out = array())
	{
		foreach($object as $index => $node)
		{
			$out[$index] = ( is_object ( $node ) ) ? self::objectToArray ( $node ) : $node;
		}

		return $out;
	}

	public static function sendRqs($method, $fields)
	{
		$request = RBRequests::init($method, $fields);

		if(isset($request))
		{
			if(is_object($request))
				$result = self::objectToArray($request);
			else
				$result = $request;		

			return $result;
		}
	}

	public static function getMaxPayBasketPage($input, $price, $ballance = false)
	{
		$result = array(
			'RB_MAX_PAY_DISPLAY' => 'Y',
			'RB_MAX_PAY' => $input
		);
		self::set_perschek_param();

		$check = (!empty(self::$arFields['persent_check']) && self::$arFields['persent_check'] != 0 && self::$arFields['persent_check'] < 100 ? self::$arFields['persent_check'] : 100);

		$max_pay = $price*$check/100;
		
		if($input != $max_pay)
			$result['RB_MAX_PAY'] = $max_pay;




		if(!!$ballance && $ballance < $result['RB_MAX_PAY'])
			$result['RB_MAX_PAY'] = $ballance;

		// AddMessage2Log(array($input, $price, $ballance, $result['RB_MAX_PAY']));
	
		// if(!$ballance && $input < $result['RB_MAX_PAY'])
		// 	$result['RB_MAX_PAY'] = $input;

		// if(!!$ballance && ($ballance < $input || $ballance < $result['RB_MAX_PAY']))
		// 	$result['RB_MAX_PAY'] = $ballance;

		// if(!!$ballance && $ballance >= $input && $ballance >= $price && $input < $price)
		// 	$result['RB_MAX_PAY'] = $price;

		// AddMessage2Log(array($input, $price, $ballance, $result['RB_MAX_PAY']));

		return $result;
	}

	public static function OnSaleComponentOrderResultPreparedHandler($order, &$arUserResult, $request, &$arParams, &$arResult)
	{
		if(Loader::includeModule("rubyroid.bonusloyalty"))
		{
			$RB_PAY_COUNT = $_SESSION['RB_PAY_COUNT'];
			$_SESSION['COUNT_PAY_COINS'] = $RB_PAY_COUNT;
			if($RB_PAY_COUNT != NULL)
			{					
				$price = $order->getPrice();
				$currency = Currency\CurrencyManager::getBaseCurrency();

				$delivery = $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE'];
				$sum_order = $price - $RB_PAY_COUNT - $delivery;

				if(!!self::negativePay($sum_order))
				{
					$arParams['RB_NEGATIVE'] = "Y";
					return $arResult['JS_DATA']['TOTAL']['NEGATIVE_RB_BONUS'] = "Y";
				}

				if(!empty($arResult['USER_ACCOUNT']['CURRENT_BUDGET']) && $arResult['JS_DATA']['PAY_CURRENT_ACCOUNT'] == 'Y')
				{
					$price = $price - $arResult['USER_ACCOUNT']['CURRENT_BUDGET'];
					if(!!self::negativePay($price - $RB_PAY_COUNT - $delivery))
					{
						$arParams['RB_NEGATIVE'] = "Y";
						return $arResult['JS_DATA']['TOTAL']['NEGATIVE_RB_BONUS'] = "Y";
					}
					$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_LEFT_TO_PAY'] = $price - $RB_PAY_COUNT;
					$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_LEFT_TO_PAY_FORMATED'] = SaleFormatCurrency($price - $RB_PAY_COUNT, $currency);
					$arResult['JS_DATA']['TOTAL']['PAYED_FROM_ACCOUNT_FORMATED'] = SaleFormatCurrency($arResult['USER_ACCOUNT']['CURRENT_BUDGET'] + $RB_PAY_COUNT, $currency);
				}

				$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_PRICE'] = $price - $RB_PAY_COUNT;
				$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_PRICE_FORMATED'] = SaleFormatCurrency($price - $RB_PAY_COUNT, $currency);
				$arResult['JS_DATA']['TOTAL']['BONUS_PAY'] = $RB_PAY_COUNT;				

				$arResult['JS_DATA']['TOTAL']['BONUS_PAY_FORMATED'] = $RB_PAY_COUNT . " " . self::NumberWordEndings($RB_PAY_COUNT);
				$arResult['JS_DATA']['TOTAL']['NEGATIVE_RB_BONUS'] = $arResult['JS_DATA']['TOTAL'];
				$arResult['JS_DATA']['TOTAL']['tablename']= Internals\PaySystemActionTable::getTableName();
				$arParams['RB_NEGATIVE'] = "N";
			}
		}
	}

	public static function OnSaleComponentOrderOneStepCompleteHandler($ID,$arOrder,$arParams)
	{
		if(Loader::includeModule("rubyroid.bonusloyalty"))
		{
			global $USER;

			$arOrder = \CSaleOrder::GetByID($arOrder['ID']);

	        $RB_PAY_COUNT = $_SESSION['RB_PAY_COUNT'];
	        $RB_BALANCE = $_SESSION['RB_MAX_PAY_COUNT'];

	        $innerbonus = self::GetBonusPaySystem();

	        if($RB_PAY_COUNT != NULL && !!$innerbonus && $arParams['RB_NEGATIVE'] != "Y")
	        {
	        	$pf = $RB_PAY_COUNT + $arOrder['SUM_PAID'];
				self::InnerBonusPay($arOrder['ID'], $RB_PAY_COUNT, $pf, $innerbonus, $RB_BALANCE);
	        }
	        self::unsetSessionParams();
	    }
	}

	public static function unsetSessionParams()
	{
		unset($_SESSION['RB_PAY_COUNT']);
		unset($_SESSION['RB_BALANCE']);
		unset($_SESSION['RB_INPUT']);
	}


	public static function InnerBonusPay($orderID, $summPaid, $pf, $innerbonus, $balance)
	{
        if($summPaid > 0)
        {
        	global $USER;

            $order = Sale\Order::load(intval($orderID));
            $bouspayID = $innerbonus['PAY_SYSTEM_ID'];
            $paymentCollection = $order->getPaymentCollection();
			$currentPay = $paymentCollection->current();
			$pr = $order->getField('PRICE');





			self::set_perschek_param();
			//$result = self::sendRqs("get_user_balance", array("email" => $USER->GetEmail()));

			$check = (!empty(self::$arFields['persent_check']) && self::$arFields['persent_check'] != 0 && self::$arFields['persent_check'] < 100 ? self::$arFields['persent_check'] : 100);

			$max_pay = floatval($pr)*$check/100;
			$result['id'] = $USER->GetID();
			if($result['id'] > 0 && $summPaid <= $max_pay) $result['operation_id'] = "1111111111111111111";
				//$result = self::sendRqs("create_transaction", array("user_id" => $result['id'], "amount" => $summPaid));


			
			if(!empty($result['operation_id']) && strlen($result['operation_id']) > 0)
			{
				$sum = floatval($pr)-$pf;
				$currentPay->setField("SUM", $sum);
				$payment = $paymentCollection->createItem(PaySystem\Manager::getObjectById($bouspayID));
	            $payment->setField("SUM", $summPaid);
	            $payment->setField("CURRENCY", $order->getField("CURRENCY"));
	            $payment->setField("PAID", "Y");
			}
			
			$points_exchange_rate = Option::get("rubyroid.bonusloyalty", 'points_exchange_rate');
			$exchange_rate = intval($points_exchange_rate) > 0 ? $points_exchange_rate : 1;

			$rb_pay_points = $RB_PAY_COUNT / $exchange_rate;

	        $dbUser = \Bitrix\main\UserTable::getList([
    			'filter' => [
        			'=ID' => $USER->GetId(),
    			],
    			'select' => [
    				'ID',
    				'UF_LOYALTY_COIN'
    			]
			])->fetch();            


			RBTransactions::debit([
				"ORDER_ID" => $orderID,
				"COIN" =>  $rb_pay_points,
				"BALANCE" => $dbUser["UF_LOYALTY_COIN"],
				"AFTER_BALANCE" => $dbUser["UF_LOYALTY_COIN"] + $rb_pay_points,
				"USER_ID" => $USER->GetID()
			]);

			/*RBTransactionsTable::add(array(
					"ORDER_ID" => $orderID,
  					"COIN" =>  $rb_pay_points,
  					"BALANCE" => $dbUser["UF_LOYALTY_COIN"],
  				)
			);*/

            $order->save();
        }
        
    }

	public static function OnSaleComponentOrderOneStepPaySystemHandler(&$arResult,&$arUserResult,$arParams)
	{    
		$inner = array();
		$arResult['JS_DATA']['RB_PAY_HIDDEN'] = "N";
		$arPayIds = array_column($arResult['PAY_SYSTEM'], "PSA_ACTION_FILE");
		$key = array_search("innerbonus", $arPayIds);

		if($key !== false){
			$inner = $arResult['PAY_SYSTEM'][$key];
			unset($arResult['PAY_SYSTEM'][$key]);
			array_multisort(array_column($arResult['PAY_SYSTEM'], "SORT"), SORT_ASC, $arResult['PAY_SYSTEM']);
		}else{
			$arResult['JS_DATA']['RB_PAY_HIDDEN'] = "Y";
		}
	}

	public static function GetBonusPaySystem()
	{
        $data = Internals\PaySystemActionTable::getRow(
            array(
                'select' => array('*'),
                'filter' => array('ACTION_FILE' => 'innerbonus')
            )
        );
        if(is_array($data) && !empty($data))
        	return $data;
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

	private static function set_balance_param()
	{
		global $USER;
		$result = self::sendRqs("get_user_balance", array("email" => $USER->GetEmail()));
		self::$arFields['ballance'] = $result['ballance'];
	}

	private static function set_perschek_param()
	{
		if(self::$arFields['persent_check'] == null)
			self::$arFields['persent_check'] = trim(Option::get(self::$module_id, 'persent_check'));
	}

	private static function convertToBaseCur($value, $currency)
	{
		if(Loader::includeModule('currency'))
		{
			$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
			if($currency != $baseCurrency)
				$value = \CCurrencyRates::ConvertCurrency($value, $currency, $baseCurrency);
			return $value;
		}
	}

	private static function negativePay($sum)
	{
		if($sum < 0)
			return true;

		return false;
	}
}
?>