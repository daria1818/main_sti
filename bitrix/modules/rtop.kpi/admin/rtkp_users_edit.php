<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Rtop\KPI\BalanceTable,
	Rtop\KPI\RolesTable,
    Rtop\KPI\Logger as Log;

$module_id = "rtop.kpi";
Loader::includeModule($module_id);

$kpiPermissions = $APPLICATION->GetGroupRight($module_id);

if ($kpiPermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$errorMessage = "";
$bVarsFromForm = false;

ClearVars();

$ID = intval($ID);

if ($REQUEST_METHOD=="POST" && $Update <> '' && $kpiPermissions >= "U" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();
	if ($kpiPermissions >= "W")
	{
		$USERID = intval($USERID);
		if ($USERID <= 0)
			$errorMessage .= "Не выбран пользователь.<br>";
		elseif($ID <= 0){	
			$balance = BalanceTable::getList(array('filter' => ['USERID' => $USERID]))->fetch();
			if(!empty($balance))
				$errorMessage .= "Пользователь уже есть в системе.<br>";
		}

		if(empty($BALANCE) && $BALANCE != 0)
			$errorMessage .= "Укажите баланс.<br>";
	}

	if($errorMessage == '')
	{
		$RESULT["USERID"] = $USERID;
		$RESULT["BALANCE"] = $BALANCE;
		$RESULT["ROLE"] = $ROLE;

		$user = CUser::GetList(($by=""), ($order=""), ["ID" => $USERID], ['SELECT' => ['ID', 'UF_DEPARTMENT']])->fetch();

		$IBLOCK_ID = Option::get('intranet', 'iblock_structure', 0);

		$mainRes = CIBlockSection::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, 'UF_HEAD' => $user['ID']], false, ["ID"]);

		if($mainRes->SelectedRowsCount() > 0)
			$RESULT["DEPARTMENT"] = $mainRes->fetch()['ID'];
		else
			$RESULT["DEPARTMENT"] = $user["UF_DEPARTMENT"][array_key_first($user["UF_DEPARTMENT"])];

		if ($ID <= 0)
	    {
	        $res = BalanceTable::add($RESULT);
	        if($res->isSuccess())
	        {
	            $ID = $res->getId();
	            $res = ($ID > 0);
	        }else{
	            $errorMessage = implode("<br/>", $res->getErrorMessages());
	            $res = false;
	        }
	    }
	    else
	    {
	        $res = BalanceTable::update($ID, $RESULT);
	        if(!$res->isSuccess())
	            $errorMessage = implode("<br/>", $res->getErrorMessages());
	    }

	}


	if (strlen($errorMessage) <= 0)
    {
        if($res)
        {
            if(strlen($save) > 0)
                LocalRedirect("/bitrix/admin/rtkp_users.php?lang=".LANG);
            elseif(strlen($apply) > 0)
                LocalRedirect("/bitrix/admin/rtkp_users_edit.php?&ID=".$ID."&lang=".LANG);
        }
    }
    else
    {
        $bVarsFromForm = true;
        //LocalRedirect("/bitrix/admin/rtkp_users_edit.php?&ID=".$ID."&lang=".LANG);
    }
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

ClearVars("str_");

$rsData = BalanceTable::getList(array('filter' => ['ID' => $ID], 'select' => ['ID', 'USERID', 'ROLE', 'BALANCE']))->fetch();
if (!$rsData)
{
	$ID = 0;
}
else
{
	$user = CUser::GetList(($by=""), ($order=""), ["ID" => $rsData['USERID']], ['FIELDS' => ['ID', 'NAME', 'LOGIN', 'LAST_NAME'], 'SELECT' => ['UF_DEPARTMENT']])->fetch();
}

$arRoles = [];
$dbRoles = RolesTable::getList(['select' => ['CODE', 'ROLE']]);
while($role = $dbRoles->fetch()){
    $arRoles[$role['CODE']] = $role['ROLE'];
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("rtop_kpi_balance", "", "str_");

$aMenu = array(
	array(
		"TEXT" => "Список пользователей",
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/rtkp_users.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errorMessage <> '')
	CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="rtkp_users_edit">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<? echo bitrix_sessid_post();

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => "Пользователь", "ICON" => "", "TITLE" => "Пользователь системы KPI")
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%">Пользователь в CRM</td>
		<td width="60%"><?
			$user_name = "";
			if ($ID>0 && $rsData['USERID']>0)
				$user_name = "[<a title=\"Профайл пользователя\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$rsData['USERID']."\">".$rsData['USERID']."</a>] (".$user['LOGIN'].") ".$user['NAME']." ".$user['LAST_NAME'];

			if ($kpiPermissions>="W"):
				echo FindUserID("USERID", $rsData['USERID'], $user_name, "rtkp_users_edit");
			else:
				echo $user_name;
			endif;
			?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top">Роль</td>
		<td class="adm-detail-content-cell-r">
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%">
				<tbody>
					<tr>
						<td>
							<select name="ROLE">
								<option>- не выбрано -</option>
								<?foreach($arRoles as $code => $role){?>
									<option value="<?=$code?>"<?=($rsData['ROLE'] == $code ? ' selected' : '')?>><?=$role?></option>
								<?}?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top">Баланс</td>
		<td valign="top">
			<input type="number" name="BALANCE" step="0.001" min="0" value="<?=($rsData['BALANCE'] > 0 ? $rsData['BALANCE'] : 0)?>">
		</td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"disabled" => ($kpiPermissions < "U"),
		"back_url" => "/bitrix/admin/rtkp_users.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>