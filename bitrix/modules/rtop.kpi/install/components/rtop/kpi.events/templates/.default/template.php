<?
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

Extension::load(['ui.buttons', 'ui.buttons.icons']);

$APPLICATION->SetTitle(Loc::getMessage("C_KPI_EVENT_BROWSER_TITLE"));
// Prepare toolbar
$bodyClass = $APPLICATION->GetPageProperty('BodyClass', '');
$arBodyClass = explode(' ', $bodyClass);
$arBodyClass[] = 'pagetitle-toolbar-field-view';
$APPLICATION->SetPageProperty('BodyClass', implode(' ', $arBodyClass));

$arResult['BUTTONS']= [
	[
		'TEXT' => Loc::getMessage("C_KPI_EVENT_BTN_ADD"),
		'ONCLICK' => 'BX.SidePanel.Instance.open("' . CUtil::JSescape('/shop/settings/kpi/events/0/') . '");'
	],
	[
		'TITLE' => Loc::getMessage("C_KPI_EVENT_BTN_EXPORT"),
		'TEXT' => Loc::getMessage("C_KPI_EVENT_BTN_EXPORT"),
		'ONCLICK' => "exportExcel()",
		'ICON' => 'btn-export'
	]
];

// Show filter
if ($arParams['SHOW_FILTER'] === 'Y') {
    $sFilterViewTarget = 'inside_pagetitle';
    $this->SetViewTarget($sFilterViewTarget, 100);
    $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $arResult['FILTER_ID'],
        'GRID_ID' => $arResult['GRID_ID'],
        'FILTER' => $arResult['UI_FILTER'],
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true,
        'THEME' => 'ROUNDED',
    ], $component, ['HIDE_ICONS' => 'Y']);

    $APPLICATION->IncludeComponent(
        'bitrix:crm.interface.toolbar',
        'title',
        array(
            'TOOLBAR_ID' => $arResult['FILTER_ID'] . '_btn',
            'BUTTONS' => $arResult['BUTTONS']
        ),
        $component,
        array('HIDE_ICONS' => 'Y')
    );
    $this->EndViewTarget();
}?>
<div class="table">
<?
$snippet = new \Bitrix\Main\Grid\Panel\Snippet();

$controlPanel['GROUPS'][0]['ITEMS'] = [
    $snippet->getRemoveButton(),
    $snippet->getForAllCheckbox()
];

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $arResult['GRID_ID'],
    'COLUMNS' => $arResult['COLUMNS'],
    'ROWS' => $arResult['ROWS'],
    'SHOW_ROW_CHECKBOXES' => $arParams['GRID_SHOW_ROW_CHECKBOXES'] ?? false,
    'NAV_OBJECT' => $arResult['NAV_OBJECT'],
    'AJAX_MODE' => $arParams['GRID_AJAX_MODE'] ?? 'Y',
    'AJAX_ID' => CAjax::GetComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [],
    'AJAX_OPTION_JUMP' => $arParams['GRID_AJAX_OPTION_JUMP'] ?? 'Y',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => $arParams['GRID_SHOW_GRID_SETTINGS_MENU'] ?? true,
    'SHOW_NAVIGATION_PANEL' => $arParams['GRID_SHOW_NAVIGATION_PANEL'] ?? true,
    'SHOW_PAGINATION' => $arParams['GRID_SHOW_PAGINATION'] ?? true,
    'SHOW_SELECTED_COUNTER' => $arParams['GRID_SHOW_SELECTED_COUNTER'] ?? true,
    'SHOW_TOTAL_COUNTER' => $arParams['GRID_SHOW_TOTAL_COUNTER'] ?? true,
    'SHOW_PAGESIZE' => $arParams['GRID_SHOW_PAGESIZE'] ?? true,
    'ACTION_PANEL' => $controlPanel,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => $arParams['GRID_ALLOW_COLUMNS_SORT'] ?? true,
    'ALLOW_COLUMNS_RESIZE' => $arParams['GRID_ALLOW_COLUMNS_RESIZE'] ?? true,
    'ALLOW_HORIZONTAL_SCROLL' => $arParams['GRID_ALLOW_HORIZONTAL_SCROLL'] ?? true,
    'ALLOW_SORT' => $arParams['GRID_ALLOW_SORT'] ?? true,
    'ALLOW_PIN_HEADER' => $arParams['GRID_ALLOW_PIN_HEADER'] ?? true,
    'AJAX_OPTION_HISTORY' => $arParams['GRID_AJAX_OPTION_HISTORY'] ?? 'N',
    'TOTAL_ROWS_COUNT' => $arResult['NAV_OBJECT']->getRecordCount(),
], false, ['HIDE_ICONS' => 'Y']);
?>
</div>
<script>
    function exportExcel(){
        var tableToExcel = (function() {
            var uri = 'data:application/vnd.ms-excel;base64,', template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table border="1">{table}</table></body></html>'
            , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
            , format = function(s, c) {              
                return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) 
            }
            , downloadURI = function(uri, name) {
                var link = document.createElement("a");
                link.download = name;
                link.href = uri;
                link.click();
            }
            return function(table, name, fileName) {
                if (!table.nodeType) table = document.getElementById(table)
                    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
                var resuri = uri + base64(format(template, ctx))
                downloadURI(resuri, fileName);
            }
        })();

        tableToExcel('<?=$arResult['GRID_ID']?>_table', '<?=$APPLICATION->GetPageProperty('title')?>', 'report.xls');
    }

    BX.ready(function() {
        BX.SidePanel.Instance.bindAnchors({
            rules: [{
                condition: [
                    "/shop/settings/kpi/events/(\\d+)/"
                ],
                options: {
                    width: 700
                }
            }]
        });

        BX.addCustomEvent('SidePanel.Slider:onMessage', function(event){
            if (event.getEventId() === 'KpiEventView::onSave')
            {
                var gridObject = top.BX.Main.gridManager.getById('<?=$arResult['GRID_ID']?>');

                if (gridObject && gridObject.hasOwnProperty('instance'))
                {
                    gridObject.instance.reloadTable('POST');
                }
            }
        });
    });
</script>