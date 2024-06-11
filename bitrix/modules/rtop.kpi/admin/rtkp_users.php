<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main,
	Bitrix\Currency,
	Rtop\KPI\BalanceTable,
    Rtop\KPI\RolesTable,
    Rtop\KPI\Premission,
    Rtop\KPI\Logger as Log;

$module_id = "rtop.kpi";
Loader::includeModule($module_id);

$kpiPermissions = $APPLICATION->GetGroupRight($module_id);
if ($kpiPermissions < "W" || Premission::get() != "ADMIN")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loc::loadMessages(__FILE__);


$sTableID = "tbl_rtkp_balance";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = [];

$lAdmin->bMultipart = true;

$arFilterFields = Array(
    "find_id_1","find_id_2",
    "find_userid",
    "find_role",
    "find_department",
    "fins_balance",
);

$lAdmin->InitFilter($arFilterFields);

if($find_ID1)
    $arFilter['>=ID']=$find_ID1;
if($find_ID2)
    $arFilter['<=ID']=$find_ID2;
if($find_userid)
    $arFilter['USERID']=$find_userid;
if($find_role)
    $arFilter['ROLE']=$find_role;
if($find_department)
    $arFilter['DEPARTMENT']=$find_department;
if($fins_balance)
    $arFilter['BALANCE']=$fins_balance;

if(($arID = $lAdmin->GroupAction())){
    if($_REQUEST['action_target']=='selected')
    {
        $arID = Array();
        $rsData = BalanceTable::getList(
            array(
                'filter'=>$arFilter,
            )
        );
        while($arRes = $rsData->fetch())
            $arID[] = $arRes['ID'];
    }
    foreach ($arID as $ID)
    {
        $ID = intval($ID);
        if($ID<=0)
            continue;
        switch ($_REQUEST['action'])
        {
            case "delete":
                @set_time_limit(0);
                if(!BalanceTable::delete($ID))
                    $lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
                break;
            case "activate":
                BalanceTable::update($ID, array("ACTIVE" => "Y"));
                break;
            case "deactivate":
                BalanceTable::update($ID, array("ACTIVE" => "N"));
            case "copy":
                $dat = BalanceTable::getById($ID)->fetch();
                unset($dat['ID'],$dat['BALANCE']);
                BalanceTable::add($dat);
                unset($dat);
                break;
        }
    }
    if ($lAdmin->hasGroupErrors())
    {
        $adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
    }
    else
    {
        $adminSidePanelHelper->sendSuccessResponse();
    }
}
$arHeader = array();
$arHeader[] = array(
    "id" => "ID",
    "content" => "ID",
    "sort" => "ID",
);
$arHeader[] = array(
    "id" => "USERID",
    "content" => Loc::getMessage("RTKP_USERS_HEADER_USERID"),
    "sort" => "USERID",
);
$arHeader[] = array(
    "id" => "ROLE",
    "content" => "Роль",
    "sort" => "ROLE",
);
$arHeader[] = array(
    "id" => "DEPARTMENT",
    "content" => "Отдел",
    "sort" => "DEPARTMENT",
);
$arHeader[] = array(
    "id" => "BALANCE",
    "content" => "Баланс",
    "sort" => "BALANCE",
);

if($by == '' || $by == 'id') $by = 'ID';

$rsData = BalanceTable::getList(
    array(
        'filter' => $arFilter,
		'order' => array($by => $order),
    )
);



$rsData = new CAdminUiResult($rsData, $sTableID);

$rsData->NavStart();

$lAdmin->SetNavigationParams($rsData, array("BASE_LINK" => "/bitrix/admin/rtkp_users.php"));

$lAdmin->AddHeaders($arHeader);
$lAdmin->AddVisibleHeaderColumn('ID');
$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();

$arDepartments = [];
$IBLOCK_ID = COption::GetOptionInt('intranet', 'iblock_structure', 0);
$tree = CIBlockSection::GetTreeList(['IBLOCK_ID' => $IBLOCK_ID], []);
while($section = $tree->GetNext()){
    $arDepartments[$section['ID']] = $section['NAME'];
}

$arUsers = [];
$dbUser = CUser::GetList(($by=""), ($order=""), ["ACITVE" => "Y", "!UF_DEPARTMENT" => false], ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'LOGIN'], 'SELECT' => ['UF_DEPARTMENT']]);
while($user = $dbUser->GetNext()){
    $arUsers[$user['ID']] = "[<a title=\"Профайл пользователя\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$user['ID']."\">".$user['ID']."</a>] (".$user['LOGIN'].") ".$user['NAME']." ".$user['LAST_NAME'];
}

$arRoles = [];
$dbRoles = RolesTable::getList(['select' => ['CODE', 'ROLE']]);
while($role = $dbRoles->fetch()){
    $arRoles[$role['CODE']] = $role['ROLE'];
}

while ($arRecurring = $rsData->NavNext(false))
{

    $row =& $lAdmin->AddRow($arRecurring["ID"], $arRecurring);

    $row->AddField("ID", $arRecurring["ID"]);

    $row->AddField("USERID", $arUsers[$arRecurring["USERID"]]);

    $row->AddField("ROLE", $arRoles[$arRecurring["ROLE"]]);

    $row->AddField("DEPARTMENT", $arDepartments[$arRecurring["DEPARTMENT"]]);

    $row->AddField("BALANCE", $arRecurring["BALANCE"]);

    $arActions = [];

    $arActions[] = array(
        "ICON" => "edit",
        "TEXT" => "Редактировать пользователя",
        "ACTION" => $lAdmin->ActionRedirect("rtkp_users_edit.php?ID=".$arRecurring["ID"]."&lang=".LANGUAGE_ID.""),
        "DEFAULT" => true
    );

    if ($kpiPermissions >= "W")
    {
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => "Удалить пользователя",
            "ACTION" => "if(confirm('Вы уверены, что хотите удалить пользователя?')) ".
                $lAdmin->ActionDoGroup($arRecurring["ID"], "delete")
        );
    }

    $row->AddActions($arActions);
}

// Action bar
$lAdmin->AddGroupActionTable(
    array(
        "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
        // "cancel" => GetMessage("MAIN_ADMIN_LIST_CANCEL"),
        // "uncancel" => GetMessage("MAIN_ADMIN_LIST_UNCANCEL")
    )
);


if ($kpiPermissions >= "W")
{
    $aContext = array(
        array(
            "TEXT" => "Добавить пользователя",
            "LINK" => "rtkp_users_edit.php?lang=".LANGUAGE_ID,
            "ICON" => "btn_new",
            "TITLE" => "Нажмите для добавления нового пользователя"
        ),
    );
    $lAdmin->setContextSettings(array("pagePath" => "/bitrix/admin/rtkp_users_admin.php"));
    $lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();



// List footer
// $lAdmin->AddFooter(
//     array(
//         array("title"=>Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
//         array("counter"=>true, "title"=>Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
//     )
// );


$APPLICATION->SetTitle("Пользователи системы KPI");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//$lAdmin->DisplayFilter($arFilterFields);

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>