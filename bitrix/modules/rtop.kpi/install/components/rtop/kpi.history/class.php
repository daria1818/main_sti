<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Rtop\KPI\HandlersTable,
    Rtop\KPI\EventsTable,
    Rtop\KPI\HistoryBalanceTable,
	Rtop\KPI\Premission,
	Rtop\KPI\Logger as Log;

class CKpiHistoryComponent extends CBitrixComponent
{
    protected static $IBLOCK_ID;

	private $templatePage = '';
	private $sListId = '';
    // private $roleList = [];
    // private $departmentList = [];

	/** @var FilterOptions */
    private $oFilterOptions;

    /** @var PageNavigation */
    private $oPageNavigation;

    /** @var GridOptions */
    private $oGridOptions;


	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
        self::$IBLOCK_ID = Option::get('intranet', 'iblock_structure', 0);
	}

	public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

	public function onPrepareComponentParams($params)
	{	
        $classHash = explode('\\', __CLASS__);

		$arParams['GRID_ID'] = $arParams['GRID_ID'] ?? array_pop($classHash) . $arParams['TYPE_REPORTS'];

		$arParams['SHOW_FILTER'] = $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';
        $arParams['GRID_SHOW_ROW_CHECKBOXES'] = "N";

		$arParams['DEFAULT_FIELDS'] = [
			'TIMESTAMP_X',
			'EVENT',
			'CLIENT',			
			'SUM',
            'INITIATOR'
		];

		// $arParams['FILTER_FIELDS'] = [
  //           'ROLE',
		// 	'DEPARTMENT'
		// ];

		$arParams['ALLOWED_FIELDS'] = $arParams['DEFAULT_FIELDS'];

		return $arParams;
	}

	private function loaderModules()
    {
        $arModules = ['rtop.kpi', 'crm'];

        foreach ($arModules as $module) {
            if (!Loader::includeModule($module)) {
                throw new Exception('Could not load ' . $module . ' module');
            }
        }
    }

    private function initPermissions()
    {
    	if (Premission::get() === '') {
            throw new Exception('No access page');
        }
    }

    public function executeComponent()
	{
		CJSCore::Init(['jquery2', 'fx', 'admin', 'filter']);
        Asset::getInstance()->addJs('/bitrix/js/main/core/core_admin_interface.js');
        if (!$this->startResultCache()) {
            return;
        }

        try {
            $this->loaderModules();
            $this->initPermissions();
            $this->initGrid();
            $this->loadData();
            $this->includeComponentTemplate($this->templatePage);
        } catch (Throwable $throwable) {
            ShowError($throwable->getMessage());
            $this->abortResultCache();
        }
	}

	private function initGrid()
    {
        $this->sListId = $this->arParams['GRID_ID'];
        $this->oGridOptions = new GridOptions($this->sListId);
        $this->oPageNavigation = new PageNavigation($this->sListId);
        $this->oFilterOptions = new FilterOptions($this->sListId);

        if (isset($this->arParams['GRID_COLUMNS'])) {
            $this->oGridOptions->SetVisibleColumns($this->arParams['GRID_COLUMNS']);
        }
        if (empty($this->oGridOptions->GetVisibleColumns())) {
            $this->oGridOptions->SetVisibleColumns($this->arParams['DEFAULT_FIELDS']);
        }
    }

    private function loadData()
    {
        $this->arResult['UI_FILTER'] = $this->initFilter();
        $this->arResult['COLUMNS'] = $this->getTHead();
        

        $arNavParams = $this->getNavParams();
        $arFilter = $this->getFilter();  
        $arSort = $this->getSort();
        $limit = $this->arParams['SHOW_ALL_RECORDS'] == 'Y' ? 0 : $this->oPageNavigation->getLimit();

        $arRows = $this->getItems([
            'filter' => $arFilter,
            'order' => $arSort['sort'],
            'limit' => $limit,
            'offset' => $arNavParams['offset'],
        ]);

        $this->arResult['GRID_ID'] = $this->sListId;
        $this->arResult['FILTER_ID'] = $this->sListId;
        $this->arResult['FILTER_OPTIONS'] = $this->oFilterOptions;  
        $this->arResult['ROWS'] = $arRows;
        $this->arResult['GRID_OPTIONS'] = $this->oGridOptions;
        $this->arResult['NAV_OBJECT'] = $this->oPageNavigation;
    }

    private function initFilter()
    {
        // $filterField = [
        //     [
        //         'id' => 'DEPARTMENT',
        //         'name' => 'Отдел',
        //         'type' => 'list',
        //         'items' => $this->getDepartmentList()
        //     ],
        //     [
        //         'id' => 'ROLE',
        //         'name' => 'Роль',
        //         'type' => 'list',
        //         'items' => $this->getRoleList()
        //     ]
        // ];

        return [];//$filterField;
    }

    private function getTHead()
    {
        $head = [];

        foreach($this->arParams['DEFAULT_FIELDS'] ?: [] as $field){
            $head[] = [
                'id' => $field,
                'name' => Loc::getMessage("C_KPI_".$field."_FIELD"),
                'sort' => $field,
                'default' => true
            ];
        }

        return $head;
    }

    private function getNavParams()
    {
        $arNavParams = $this->oGridOptions->GetNavParams();

        $this->oPageNavigation
            ->allowAllRecords(true)
            ->setPageSize($arNavParams['nPageSize'])
            ->initFromUri();

        $arNavParams['iNumPage'] = (int)$this->oPageNavigation->getCurrentPage();
        $arNavParams['limit'] = $this->oPageNavigation->getLimit();
        $arNavParams['offset'] = $this->oPageNavigation->getOffset();

        return $arNavParams;
    }

    private function getFilter(){

        $filters = $this->oFilterOptions->getFilter();

        $setFilter = [];

        // foreach($filters as $code => $value){
        //     if(empty($value))
        //         continue;
        //     switch($code){
        //         case 'DEPARTMENT':
        //         case 'ROLE':
        //             $setFilter[$code] = $value;
        //             break;
        //     }
        // }

        return $setFilter;
    }

    private function getSort()
    {
        return $this->oGridOptions->getSorting([
            'sort' => [
                'TIMESTAMP_X' => 'ASC',
            ],
            'vars' => [
                'by' => 'by',
                'order' => 'order',
            ],
        ]);
    }

    private function getItems(array $parameters = [])
    {
    	global $USER;
    	
        $arRows = [];
        $dbEvent = HistoryBalanceTable::getList([
            'order' => [
                'ID' => 'DESC'
            ],
            'filter' => array_merge(['USERID' => $USER->GetId()], $parameters['filter'] ?: []),
            'select' => ['EVENT', 'TIMESTAMP_X', 'CODE', 'CLIENT', 'SUM', 'INITIATOR', 'TYPE'],
            'runtime' => [
            	'EVENT' => [
            		'data_type' => '\Rtop\KPI\HandlersTable',
            		'reference' => [
                        '=this.CODE' => 'ref.FUNCTION',
                    ],
            	],
            ],
        ]);

        $itemsEvent = $dbEvent->fetchAll();
        if ($parameters['count_total']) {
            $this->oPageNavigation->setRecordCount($dbEvent->getCount());
        }
        
        foreach($itemsEvent ?: [] as $item){
            $arRows[$item['ID']] = [
                'data' => [
                    'TIMESTAMP_X' => $item['TIMESTAMP_X']->format("d.m.Y H:i:s"),
                    'EVENT' => $item['RTOP_KPI_HISTORY_BALANCE_EVENT_NAME'],
                    'CLIENT' => $item['CLIENT'],
                    'SUM' => $item['SUM'],
                    'INITIATOR' => Loc::getMessage("C_KPI_INITIATOR_" . $item['INITIATOR'] . "_FIELD")
                ]
            ];
        }

        return $this->sortView($arRows, $parameters['order']);
    }

    private function sortView($arRow, $order)
    {
        usort($arRow, function ($a, $b) use ($order) {

            $field = array_key_first($order);
            $order = current($order);

            $valueA = strip_tags($a['data'][$field]);
            $valueB = strip_tags($b['data'][$field]);
            if ($valueA == $valueB) {
                return 0;
            }
            if ($order == 'desc') {
                return ($valueA > $valueB) ? -1 : 1;
            } else {
                return ($valueA < $valueB) ? -1 : 1;
            }
        });
        return $arRow;
    }
}