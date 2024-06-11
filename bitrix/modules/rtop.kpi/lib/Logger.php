<?
namespace Rtop\KPI;

class Logger
{
	public static function writeLog($data)
	{
		$DIR = (preg_match("/\/local\//", __DIR__) ? "local" : "bitrix");
		$filePatch = $_SERVER['DOCUMENT_ROOT'] . "/" . $DIR . "/modules/rtop.kpi/logs/MainOrders.log";

		if($file = fopen($filePatch, 'a'))
		{
			$data = self::getContents($data);
			fwrite($file, $data);
			fclose($file);
			return true;
		}
		else
		{
			return false;
		}
	}



	public static function writeLogFile($data, $filename = "123.log")
	{
		$DIR = (preg_match("/\/local\//", __DIR__) ? "local" : "bitrix");
		$filePatch = $_SERVER['DOCUMENT_ROOT'] . "/" . $DIR . "/modules/rtop.kpi/logs/" . $filename;
		if($file = fopen($filePatch, 'a'))
		{
			$data = self::getContents($data);
			fwrite($file, $data);
			fclose($file);
			return true;
		}
		else
		{
			return false;
		}
	}



	private static function getContents($data)
	{
		ob_start();
		echo '-------------------------' . date("F j, Y, g:i a") . '-------------------------', "\n", print_r($data), "\n";
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	public static function pre($array){
		echo "<pre>"; print_r($array); echo "</pre>";
	}
}
?>