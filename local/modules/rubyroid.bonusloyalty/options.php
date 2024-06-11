<?
use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Loader;

global $APPLICATION, $MESS;

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

$module_id = "rubyroid.bonusloyalty";
Loader::includeModule($module_id);
$RB_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($RB_RIGHT < "W")
{
	CAdminMessage::ShowMessage(Loc::getMessage("RB_ACCESS_ERROR"));
	return;
}

if($RB_RIGHT == "W")
{	
	if($REQUEST_METHOD == "POST" && (isset($_POST['save']) || isset($_POST['apply'])) && check_bitrix_sessid())
	{
		COption::SetOptionString($module_id, "login_system", $_REQUEST['login_system']);
		COption::SetOptionString($module_id, "password_system", $_REQUEST['password_system']);
		COption::SetOptionString($module_id, "persent_check", $_REQUEST['persent_check']);
		COption::SetOptionString($module_id, "url_rules", $_REQUEST['url_rules']);
		COption::SetOptionString($module_id, "logger", $_REQUEST['logger']);
		COption::SetOptionString($module_id, "points_exchange_rate", $_REQUEST['points_exchange_rate']);
		COption::SetOptionString($module_id, "ratio_like", $_REQUEST['ratio_like']);
		COption::SetOptionString($module_id, "ratio_coment", $_REQUEST['ratio_coment']);
		COption::SetOptionString($module_id, "ratio_repost", $_REQUEST['ratio_repost']);
	}
	$arAllOptions = array();
	$arAllOptions[] = Loc::getMessage("RB_MAIN_PARAMS");
	$arAllOptions[] = array("login_system", Loc::getMessage("opt_login_system_from"), "", array("text", 35));
	$arAllOptions[] = array("password_system", Loc::getMessage("opt_password_system_from"), "", array("text", 35));
	$arAllOptions[] = array("persent_check", Loc::getMessage("opt_persent_check_from"), "", array("text", 5));
	$arAllOptions[] = array("url_rules", Loc::getMessage("opt_url_rules_from"), "", array("text", 35));  
	$arAllOptions[] = array("logger", Loc::getMessage("opt_logger_from"), "", array("checkbox"));
	$arAllOptions[] = array("points_exchange_rate", Loc::getMessage("opt_points_exchange_rate_from"), "", array("text", 5));
	$arAllOptions[] = array("ratio_like", Loc::getMessage("opt_ratio_like_from"), "", array("text", 5));
	$arAllOptions[] = array("ratio_coment", Loc::getMessage("opt_ratio_coment_from"), "", array("text", 5));
	$arAllOptions[] = array("ratio_repost", Loc::getMessage("opt_ratio_repost_from"), "", array("text", 5));

	$aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")),
        array("DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")),
    );
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();?>
	<form method="POST" id="bonusloyalty" name="bonusloyalty" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&mid_menu=1&mid=<? echo $module_id?>" ENCTYPE="multipart/form-data">
		<?$tabControl->BeginNextTab();?>
		<? __AdmSettingsDrawList($module_id, $arAllOptions);?> 
		<?$tabControl->BeginNextTab();?>
		<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
		<?$tabControl->Buttons(array('btnCancel' => false, 'btnSaveAndAdd' => false)); ?> 
		<?=bitrix_sessid_post();?>
		<?$tabControl->End();?>
	</form>
<?
}
?>