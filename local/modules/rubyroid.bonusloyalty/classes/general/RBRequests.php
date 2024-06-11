<?
namespace Rubyroid\Loyality;
use \Bitrix\Main\Config\Option;
use \Rubyroid\Loyality\RBLogger;

class RBRequests
{
	protected static $FIELDS = array();
	protected static $DATA;
	protected static $pass = "";
	protected static $login = "";
	protected static $module_id = "rubyroid.bonusloyalty";
	protected static $logger = "N";
	
	public function __construct()
    {    	
		
    }
	public function init($method, $fields)
	{
		self::$FIELDS = $fields;
		if(empty(self::$login))
			self::$login = trim(Option::get(self::$module_id, 'login_system'));
		if(empty(self::$pass))
			self::$pass = trim(Option::get(self::$module_id, 'password_system'));

		self::$logger = trim(Option::get(self::$module_id, 'logger'));

		$answer = self::$method();
		return $answer;
	}

	private function get_user_balance()
	{
		return self::send_curl('GET', '/users/?email=' . self::$FIELDS['email']);
	}

	private function create_user_wallet()
	{
		return self::send_curl('POST', '/users/');
	}

	private function send_user_points()
	{
		return self::send_curl('POST', '/users/remittance/');
	}

	private function create_transaction()
	{
		return self::send_curl('POST', '/users/'.self::$FIELDS['user_id'].'/charge/');
	}

	private function get_history()
	{
		return self::send_curl('GET', '/core_api/user_history?email=' . self::$FIELDS['email']);
	}

	private function send_curl($cr, $url)
	{
		$curl = curl_init();
		$auth = self::$login . ":" . self::$pass;

		if(self::$logger == "Y")
			RBLogger::writeLog(array($cr, $url, self::$FIELDS));

		curl_setopt_array($curl, array(
			//CURLOPT_URL => "http://142.93.100.228:8585" . $url,
			CURLOPT_URL => "https://sticoin.ru:8585" . $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERPWD => $auth,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $cr
		));

		if($cr == 'POST')
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(self::$FIELDS));

		//$response = curl_exec($curl);

		if(self::$logger == "Y")
			RBLogger::writeLog($response);

		self::$DATA = json_decode($response);
		curl_close($curl);
		
		return self::$DATA;
	}
}
?>