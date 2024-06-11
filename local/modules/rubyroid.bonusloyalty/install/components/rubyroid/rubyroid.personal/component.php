<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Rubyroid\Loyality\RBprogramm as RB;

$module_id = "rubyroid.bonusloyalty";
global $USER;


if(Loader::includeModule($module_id) && $USER->IsAuthorized())
{
	$email = $USER->GetEmail();
	$init = RB::init();

	if(strlen($email) > 0 && !!$init)
	{
		if (strlen($_REQUEST['rb_add_user']) > 0)
		{
			$result = RB::sendRqs("create_user_wallet", array('email' => $email));
			if(!!$result['id'])
				$arResult["RB_SUCCESS"] = Loc::getMessage("RB_PERSONAL_SUCCESS");
		}

		if (strlen($_REQUEST['rb_send_balls']) > 0)
		{
			$fields = array(
				'from' => $email,
				'to' => $_POST['rb_email_send'],
				'amount' => $_POST['rb_amount_send']
			);		
			$result = RB::sendRqs("send_user_points", $fields);
			if(!!$result['operation_id'])
				$arResult["RB_SUCCESS"] = Loc::getMessage("RB_SEND_TO_USER_SUCCESS");
			else
				$arResult["RB_FATAL"] = Loc::getMessage("RB_SEND_TO_USER_FATAL");
		}

		$result = RB::sendRqs("get_user_balance", array('email' => $email));

		$arResult["RB_JS_BLOCK"]["FORM_ID"] = "RB_FORM";
		$arResult["RB_JS_BLOCK"]["BUTTON"] = "RB_BUTTON";

		if(!!$result['id'] && isset($result['ballance']))
		{
			$arResult["RB_JS_BLOCK"]['BALANCE'] = $result['ballance'];
			if($result['ballance'] > 0)
				$arResult['RB_BALANCE_FORMATED'] = trim($result['ballance'] . " " . RB::NumberWordEndings($result['ballance']));
		}
		else
		{
			$arResult['RB_NO_REGIST'] = Loc::getMessage("RB_NO_REGIST");
			$arResult["RB_JS_BLOCK"]["RULES"] = Loc::getMessage("RB_TEXT_RULES", array("#HREF#" => COption::GetOptionString($module_id, "url_rules")));
		}

		$this->IncludeComponentTemplate();
	}
}
?>