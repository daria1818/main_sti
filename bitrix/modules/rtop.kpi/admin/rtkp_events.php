<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main,
    Bitrix\Currency,
    Rtop\KPI\HandlersTable,
    Rtop\KPI\Premission,
    Rtop\KPI\Logger as Log;

$module_id = "rtop.kpi";
Loader::includeModule($module_id);

$kpiPermissions = $APPLICATION->GetGroupRight($module_id);
if ($kpiPermissions < "W" || Premission::get() != "ADMIN")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loc::loadMessages(__FILE__);


$sTableID = "tbl_rtkp_handlers";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = [];

$lAdmin->bMultipart = true;

if(($arID = $lAdmin->GroupAction())){
    if($_REQUEST['action_target']=='selected')
    {
        $arID = Array();
        $rsData = HandlersTable::getList(
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
                if(!HandlersTable::delete($ID))
                    $lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
                break;
            case "copy":
                $dat = HandlersTable::getById($ID)->fetch();
                unset($dat['ID']);
                HandlersTable::add($dat);
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
    "id" => "NAME",
    "content" => "Название",
    "sort" => "NAME",
);
$arHeader[] = array(
    "id" => "CODE",
    "content" => "Код",
    "sort" => "CODE",
);
$arHeader[] = array(
    "id" => "TYPE",
    "content" => "Тип",
    "sort" => "TYPE",
);
$arHeader[] = array(
    "id" => "FUNCTION",
    "content" => "Функция",
    "sort" => "FUNCTION",
);
$arHeader[] = array(
    "id" => "AUTO",
    "content" => "Крон/агент",
    "sort" => "AUTO",
);
$arHeader[] = array(
    "id" => "PERIOD",
    "content" => "Период",
    "sort" => "PERIOD",
);

if($by == '' || $by == 'id') $by = 'ID';

$rsData = HandlersTable::getList(
    array(
        'filter' => $arFilter,
        'order' => array($by => $order),
    )
);



$rsData = new CAdminUiResult($rsData, $sTableID);

$rsData->NavStart();

$lAdmin->SetNavigationParams($rsData, array("BASE_LINK" => "/bitrix/admin/rtkp_events.php"));

$lAdmin->AddHeaders($arHeader);
$lAdmin->AddVisibleHeaderColumn('ID');
$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();

$arDepartments = [];
$IBLOCK_ID = COption::GetOptionInt('intranet', 'iblock_structure', 0);
$tree = CIBlockSection::GetTreeList(['IBLOCK_ID' => $IBLOCK_ID], []);
while($section = $tree->GetNext()){
    $arDepartments[$section['ID']] = $section['NAME'];
}

while ($arRecurring = $rsData->NavNext(false))
{

    $row =& $lAdmin->AddRow($arRecurring["ID"], $arRecurring);

    $row->AddField("ID", $arRecurring["ID"]);

    $row->AddField("NAME", $arRecurring["NAME"]);

    $row->AddField("CODE", $arRecurring["CODE"]);

    $row->AddField("TYPE", $arRecurring["TYPE"]);

    $row->AddField("FUNCTION", $arRecurring["FUNCTION"]);

    $row->AddField("AUTO", $arRecurring["AUTO"]);

    $row->AddField("PERIOD", $arRecurring["PERIOD"]);

    $arActions = [];

    $arActions[] = array(
        "ICON" => "edit",
        "TEXT" => "Редактировать событие",
        "ACTION" => $lAdmin->ActionRedirect("rtkp_events_edit.php?ID=".$arRecurring["ID"]."&lang=".LANGUAGE_ID.""),
        "DEFAULT" => true
    );

    if ($kpiPermissions >= "W")
    {
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => "Удалить событие",
            "ACTION" => "if(confirm('Вы уверены, что хотите удалить событие?')) ".
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
            "TEXT" => "Добавить событие",
            "LINK" => "rtkp_events_edit.php?lang=".LANGUAGE_ID,
            "ICON" => "btn_new",
            "TITLE" => "Нажмите для добавления нового события"
        ),
    );
    $lAdmin->setContextSettings(array("pagePath" => "/bitrix/admin/rtkp_events.php"));
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


$APPLICATION->SetTitle("События системы KPI");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//$lAdmin->DisplayFilter($arFilterFields);

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>