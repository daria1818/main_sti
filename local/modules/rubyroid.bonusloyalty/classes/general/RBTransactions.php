<?
namespace Rubyroid\Loyality;
use \Rubyroid\Loyality\RBTransactionsTable;

class RBTransactions
{
	protected static $type_operation = [];
	protected static $type_event = [];
	protected static $event = "";

	public static function debit($arFields = [])
	{
		if(!empty($arFields["TYPE_EVENT"]))
		{
			self::$event = self::getEvent($arFields["TYPE_EVENT"]);
		}
		else
		{
			self::$event = self::getEvent("AUTO");
		}
		if(empty($arFields["USER_ID"]))
		{
			return false;
		}
		if(empty($arFields["COIN"]))
		{
			return false;
		}
		return RBTransactionsTable::add(array(
			"USER_ID"			=> $arFields["USER_ID"],
			"ORDER_ID"			=> $arFields["ORDER_ID"],
			"TYPE_OPERATION"	=> self::getOperation(__FUNCTION__),
			"TYPE_EVENT"		=> self::$event,
			"COIN"				=> $arFields["COIN"],
			"BALANCE"			=> $arFields["BALANCE"],
			"AFTER_BALANCE"		=> $arFields["AFTER_BALANCE"],
		));
	}
	public static function bonus($arFields = [])
	{
		if(empty($arFields["TYPE_EVENT"]))
		{
			return false;
		}
		if(empty($arFields["USER_ID"]))
		{
			return false;
		}
		if(empty($arFields["COIN"]))
		{
			return false;
		}

		self::$event = self::getEvent($arFields["TYPE_EVENT"]);
		if(self::$event)
		{
			RBTransactionsTable::add(array(
				"USER_ID"			=> $arFields["USER_ID"],
				"TYPE_OPERATION"	=> self::getOperation(__FUNCTION__),
				"TYPE_EVENT"		=> self::$event,
				"COIN"				=>  $arFields["COIN"],
				"AFTER_BALANCE"		=> $arFields["AFTER_BALANCE"],
				"BALANCE"			=> $arFields["BALANCE"],
			));
		}
		else
		{
			return false;
		}
	}
	
	protected static function getOperation($type)
	{
		$operation = [
			"debit" => "Списание",
			"bonus" => "Начисление"
		];
		return $operation[$type];
	}
	protected static function getEvent($type)
	{
		$event = [
			"MANUAL"		=> "Вручную",
			"LIKE"			=> "Лайк",
			"COMMENT"		=> "Коммент",
			"REPOST"		=> "Репост",
			"AUTO"			=> "Заказ"
			//"MANUAL_DEBIT"	=> "Вручную/начисление",
			//"MANUAL_BONUS"	=> "Вручную/списание"
		];
		return $event[$type];
	}
}
?>