<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	Rubyroid\Loyality\RBprogramm as RB;

if(Loader::includeModule("rubyroid.bonusloyalty") && !isset($_GET['ORDER_ID']))
{
	$email = $USER->GetEmail();
	$init = RB::init();

	if(strlen($email) > 0 && !!$init)
	{
		$result = RB::sendRqs("get_user_balance", array('email' => $email));

		if(!!$result['id'] && isset($result['ballance']))
		{
			$arResult['RB'] = $result;
			if($arResult['RB']['ballance'] > 0)
			{
				$arResult['RB']['balance_formated'] = trim($arResult['RB']['ballance'] . " " . RB::NumberWordEndings($arResult['RB']['ballance']));
			}
		}

		$this->IncludeComponentTemplate();

	}
}
?>