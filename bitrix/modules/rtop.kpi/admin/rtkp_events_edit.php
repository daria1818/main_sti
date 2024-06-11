<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Rtop\KPI\HandlersTable,
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
		if($NAME == "")
			$errorMessage .= "Укажите название события.<br>";

		if($CODE == "")
			$errorMessage .= "Укажите код события.<br>";

		if($TYPE == "")
			$errorMessage .= "Укажите тип события.<br>";

		$AUTO = ($AUTO == "on" ? "Y" : "N");

		if($AUTO == "Y" && empty($PERIOD))
			$errorMessage .= "Укажите период события.<br>";
	}

	if($errorMessage == '')
	{
		$RESULT["NAME"] = $NAME;
		$RESULT["CODE"] = $CODE;
		$RESULT["TYPE"] = $TYPE;
		$RESULT["FUNCTION"] = $FUNCTION;
		$RESULT["AUTO"] = $AUTO;
		$RESULT["PERIOD"] = $PERIOD;

		if ($ID <= 0)
	    {
	        $res = HandlersTable::add($RESULT);
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
	        $res = HandlersTable::update($ID, $RESULT);
	        if(!$res->isSuccess())
	            $errorMessage = implode("<br/>", $res->getErrorMessages());
	    }

	}


	if (strlen($errorMessage) <= 0)
    {
        if($res)
        {
            if(strlen($save) > 0)
                LocalRedirect("/bitrix/admin/rtkp_events.php?lang=".LANG);
            elseif(strlen($apply) > 0)
                LocalRedirect("/bitrix/admin/rtkp_events_edit.php?&ID=".$ID."&lang=".LANG);
        }
    }
    else
    {
        $bVarsFromForm = true;
        //LocalRedirect("/bitrix/admin/rtkp_events_edit.php?&ID=".$ID."&lang=".LANG);
    }
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

ClearVars("str_");

$rsData = HandlersTable::getList(array('filter' => ['ID' => $ID]))->fetch();
if (!$rsData)
{
	$ID = 0;
}

$arTypes = HandlersTable::getTypes();
$arPeriods = HandlersTable::getPeriods();

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("rtop_kpi_handlers", "", "str_");

$aMenu = array(
	array(
		"TEXT" => "Список событий",
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/rtkp_events.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errorMessage <> '')
	CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="rtkp_events_edit">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<? echo bitrix_sessid_post();

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => "Событие", "ICON" => "", "TITLE" => "Событие системы KPI")
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
		<td width="40%" valign="top">Название</td>
		<td width="60%" valign="top">
			<input type="text" name="NAME" size="40" value="<?=$rsData['NAME']?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top">Код</td>
		<td valign="top">
			<input type="text" name="CODE" size="40" value="<?=$rsData['CODE']?>">
		</td>
	</tr>
	<tr>
		<td valign="top">Функция</td>
		<td valign="top">
			<input type="text" name="FUNCTION" size="40" value="<?=$rsData['FUNCTION']?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top">Тип</td>
		<td class="adm-detail-content-cell-r">
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%">
				<tbody>
					<tr>
						<td>
							<select name="TYPE">
								<?foreach($arTypes as $type){?>
									<option value="<?=$type['CODE']?>"<?=($rsData['TYPE'] == $type['CODE'] ? ' selected' : '')?>><?=$type['NAME']?></option>
								<?}?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top">Крон/агент</td>
		<td valign="top">
			<input type="checkbox" name="AUTO"<?=($rsData['AUTO'] == 'Y' ? ' checked' : '')?>>
		</td>
	</tr>
	<tr>
		<td valign="top">Период</td>
		<td class="adm-detail-content-cell-r">
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%">
				<tbody>
					<tr>
						<td>
							<select name="PERIOD">
								<option value="">Нет</option>
								<?foreach($arPeriods as $period){?>
									<option value="<?=$period['CODE']?>"<?=($rsData['PERIOD'] == $period['CODE'] ? ' selected' : '')?>><?=$period['NAME']?></option>
								<?}?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"disabled" => ($kpiPermissions < "U"),
		"back_url" => "/bitrix/admin/rtkp_events.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>