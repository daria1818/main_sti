<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	$search = mb_strtolower($request->get('q'));
	if(!empty($search))
	{
		Loader::includeModule('highloadblock');
		$hlbl = 30;
		$hlblock = HighloadBlockTable::getById($hlbl)->fetch();
		$entity = HighloadBlockTable::compileEntity($hlblock); 
		$entity_data_class = $entity->getDataClass();
		$result = $entity_data_class::getList(['filter' => ['=UF_REQUEST' => $search], 'select' => ['UF_URL']])->fetch();
		// if(!empty($result))
		// {
		// 	echo "/catalog/" . $result['UF_URL'] . "/";
		// 	die();
		// }

		$start = new CustomSearch;
		$cyrillic = CustomSearch::isCyrillic($search);
		if($cyrillic)
		{
			$url = CustomSearch::translit($search);
			$request = $entity_data_class::getList(['filter' => ['=UF_URL' => $url], 'select' => ['UF_REQUEST']])->fetch();
			if(empty($request))
				$entity_data_class::add(['UF_REQUEST' => $search, 'UF_URL' => $url]);
		}
		else
		{
			$url = CustomSearch::replaceSpec($search);
		}	

		//echo "/catalog/" . $url . "/"; Перестало отрабатывать поиск в каталоге по таким запросам. Разобраться, в чем дело
		
		// echo "/catalog/?q=" . $search;
		echo "/search/?q=" . $search;
	}
	else
	{
		// echo "/catalog/?q=";
		echo "/search/?q=";
	}
}
class CustomSearch
{
	protected static $rus;
	
	public static function __construct()
	{
		self::$rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
	}

	public static function translit($str)
	{
	    $rus = self::$rus;
	    $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
	    $str = str_replace($rus, $lat, $str);
	    return self::replaceSpec($str);
	}

	public static function isCyrillic($str)
	{
		mb_regex_encoding('UTF-8');
		mb_internal_encoding("UTF-8");
		$charlist = preg_split('/(?<!^)(?!$)/u', $str);
		return !empty(array_intersect($charlist, self::$rus));
	}

	public static function replaceSpec($str)
	{
		$str = str_replace(' ', '_', $str);
		$str = preg_replace('/[^A-Za-zА-Яа-я0-9-_]/', '', $str);
		return $str;
	}
}