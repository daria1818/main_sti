<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CKpiComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		if (!Main\Loader::includeModule('rtop.kpi'))
		{
			ShowError(Loc::getMessage('KPI_MODULE_NOT_INSTALLED'));
			die();
		}

		$this->arParams['SEF_FOLDER'] = $this->arParams['SEF_FOLDER'] ?? "/shop/settings/kpi/";

		$this->arParams['SEF_URL_TEMPLATES'] = $this->arParams['SEF_URL_TEMPLATES'] ?? [];

		$componentPage = '';

		$variables = array();

		$defaultUrlTemplates = array(
			'home' => 'history/',
			'ctm_events' => 'shtrafy-i-pooshchreniya/',
			'users' => 'plany-i-otdely/',
			'events_list' => 'events/',
			'events_view' => 'events/#id#/',
			'plans' => 'plans/'
		);

		$urlTemplates  = \CComponentEngine::makeComponentUrlTemplates($defaultUrlTemplates, $this->arParams['SEF_URL_TEMPLATES']);

		$componentPage = \CComponentEngine::parseComponentPath($this->arParams['SEF_FOLDER'], $urlTemplates, $variables);

		foreach ($urlTemplates as $page => $path)
		{
			$this->arResult['PATH_TO_KPI_'.mb_strtoupper($page)] = $this->arParams['SEF_FOLDER'] . $path;
		}

		if (empty($componentPage) || !array_key_exists($componentPage, $defaultUrlTemplates))
			$componentPage = 'home';

		$this->arResult = array_merge(
			array(
				'COMPONENT_PAGE' => $componentPage,
				'VARIABLES' => $variables,
				'ALIASES' => [],
				'ID' => isset($variables['id']) ? strval($variables['id']) : ''
			),
			$this->arResult
		);

		$this->includeComponentTemplate($componentPage);
	}

	public function includePageComponent($name, $template, &$params)
	{
		global $APPLICATION;
		$APPLICATION->includeComponent($name, $template, $params, $this);
	}
}
?>