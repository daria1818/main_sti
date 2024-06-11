<?
use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Page\Asset;

global $APPLICATION;
$module_id = "rtop.kpi";
Loader::includeModule($module_id);
$RB_RIGHT = $APPLICATION->GetGroupRight($module_id);

$Update = !empty($_REQUEST['save']) ? 'Y' : '';
$Apply = !empty($_REQUEST['apply']) ? 'Y' : '';

if($RB_RIGHT < "W")
{
    CAdminMessage::ShowMessage(Loc::getMessage("RTT_ACCESS_ERROR"));
    return;
}

if($RB_RIGHT == "W")
{
    if($REQUEST_METHOD == "POST" && (isset($_POST['save']) || isset($_POST['apply'])) && check_bitrix_sessid())
    {
        if($Update. $Apply <> ''){
            ob_start();
            $Update = $Update. $Apply;
            require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
            ob_end_clean();
        }
    }

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")),
        array("DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    $tabControl->Begin();?>
    <form method="post" id="rtop_kpi_bonus" name="rtop_kpi_bonus" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
        <?$tabControl->BeginNextTab();?>
        <?$tabControl->BeginNextTab();
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
        <?$tabControl->Buttons(array('btnCancel' => false, 'btnSaveAndAdd' => false)); ?> 
        <?=bitrix_sessid_post();?>
        <?$tabControl->End();?>
    </form>
<?}?>