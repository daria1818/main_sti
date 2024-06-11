<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Rtop\KPI\Premission;
use Rtop\KPI\RolesTable;
use Rtop\KPI\HandlersTable;
use Rtop\KPI\EventsTable;
use Rtop\KPI\Logger as Log;

class CKpiEventViewComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	protected $errorCollection = null;
	protected $eventFields = [];
	protected static $IBLOCK_ID;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
		self::$IBLOCK_ID = Option::get('intranet', 'iblock_structure', 0);
	}

	public function onPrepareComponentParams($params)
	{
		return $params;
	}

	protected function listKeysSignedParameters()
	{
		return ['ID', 'PATH_TO_KPI_EVENTS_LIST', 'PATH_TO_KPI_EVENTS_VIEW'];
	}

	protected function initialLoadAction()
	{
		global $APPLICATION;

		$this->arResult = $this->arParams;

		$this->eventFields = $this->getFields();

		if($this->arResult['ID'] > 0)
		{
			$this->getData($this->arResult['ID']);
		}

		if ($this->errorCollection->isEmpty())
		{
			$this->arResult['FIELDS'] = $this->eventFields;
		}		

		if($this->arResult['ID'] > 0){
			$title = Loc::getMessage('KPI_EVENT_VIEW_EDIT_TITLE');
		}else{
			$title = Loc::getMessage('KPI_EVENT_VIEW_CREATE_TITLE');
		}

		$APPLICATION->SetTitle($title);

		$this->arResult['ERRORS'] = $this->errorCollection->toArray();

		$this->includeComponentTemplate();
	}

	public function saveFormAjaxAction()
	{
		$response = [];

		if (!$this->loaderModules() || !$this->initPermissions())
		{
			return $response;
		}

		$data = $this->request->get('data') ?: [];
		$eventId = $this->saveEvent($data);

		if(!empty($eventId)){
			$response['redirectUrl'] = $this->arParams['PATH_TO_KPI_EVENTS_LIST'];
		}

		return $response;
	}

	protected function saveEvent($event)
	{
		$id = "";

		foreach($this->getFields() as $field)
		{
			if($field['REQUIRED'] && $event[$field['ID']] == ""){
				$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('KPI_EVENT_VIEW_ERROR_EMPTY', ['#FIELD#' => $field['NAME']]));
			}
			if($field['MULTY'] == "Y" && is_array($event[$field['ID']]) && !empty($event[$field['ID']])){
				$event[$field['ID']] = serialize($event[$field['ID']]);
			}
		}
		if ($this->errorCollection->isEmpty())
		{
			if($event['ID'] > 0)
				$id = $this->updateEvent($event);
			else
				$id = $this->createEvent($event);
		}

		return $id;
	}

	protected function updateEvent($event)
	{
		$res = EventsTable::update($event['ID'], $event);
		if(!$res->isSuccess())
			$this->errorCollection[] = new \Bitrix\Main\Error(implode("<br/>", $res->getErrorMessages()));
		else
			return $event['ID'];
	}

	protected function createEvent($event)
	{
		$res = EventsTable::add($event);
		if($res->isSuccess())
		{
			return $res->getId();
		}else{
			$this->errorCollection[] = new \Bitrix\Main\Error(implode("<br/>", $res->getErrorMessages()));
			return false;
		}
	}

	protected function getData($ID)
	{
		$res = EventsTable::getList(['filter' => ['ID' => $ID]])->fetch();
		if(!$res){
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('KPI_EVENT_VIEW_NOT_EXIST'));
			return;
		}

		foreach($this->eventFields as &$field){
			$value = $res[$field['ID']];
			$field['VALUE'] = is_numeric($value) ? $value : unserialize($value);
		}
	}

	protected function getFields()
	{
		return [
			[
				'ID' => 'HANDLER',
				'NAME' => Loc::getMessage('KPI_EVENT_VIEW_COLUMN_HANDLER'),
				'TYPE' => 'select',
				'REQUIRED' => true,
				'LIST' => $this->getHandlerList()
			],
			[
				'ID' => 'VALUE',
				'NAME' => Loc::getMessage('KPI_EVENT_VIEW_COLUMN_VALUE'),
				'TYPE' => 'number',
				'REQUIRED' => false
			],
			[
				'ID' => 'ROLE',
				'NAME' => Loc::getMessage('KPI_EVENT_VIEW_COLUMN_ROLE'),
				'TYPE' => 'select',
				'REQUIRED' => false,
				'MULTY' => 'Y',
				'LIST' => $this->getRoleList()
			],
			[
				'ID' => 'DEPARTMENT',
				'NAME' => Loc::getMessage('KPI_EVENT_VIEW_COLUMN_DEPARTMENT'),
				'TYPE' => 'select',
				'REQUIRED' => false,
				'MULTY' => 'Y',
				'LIST' => $this->getDepartmentList()
			],
		];
	}

	protected function getDepartmentList()
    {
        $sections = SectionTable::getList([
            'filter' => [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => self::$IBLOCK_ID,
            ],
            'select' => ['NAME', 'ID', 'DEPTH_LEVEL'],
            'order' => [
                'LEFT_MARGIN' => 'ASC',
            ],
        ]);
        while ($one = $sections->fetch()) {
            $sectionList[$one['ID']] = substr('----', 0, $one['DEPTH_LEVEL'] - 1) . ' ' . $one['NAME'];
        }
        return $sectionList;
    }

    protected function getRoleList()
    {
        $dbRoles = RolesTable::getList(['select' => ['CODE', 'ROLE']]);
        while($role = $dbRoles->fetch()){
            $roleList[$role['CODE']] = $role['ROLE'];
        }
        return $roleList;
    }

    protected function getHandlerList()
    {
    	$dbHandlers = HandlersTable::getList(['select' => ['ID', 'NAME']]);
        while($handler = $dbHandlers->fetch()){
            $handlerList[$handler['ID']] = $handler['NAME'];
        }
        return $handlerList;
    }

	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	public function configureActions()
	{
		return [];
	}

	protected function showErrors()
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	protected function loaderModules()
    {
        $arModules = ['rtop.kpi', 'crm'];
        foreach ($arModules as $module) {
            if (!Loader::includeModule($module)) {
                return false;
            }
        }
        return true;
    }

    protected function initPermissions()
    {
    	return Premission::get() === 'ADMIN';
    }

	public function executeComponent()
	{
		if (!$this->loaderModules() || !$this->initPermissions())
		{
			$this->showErrors();
			return;
		}

		$this->initialLoadAction();
	}
}