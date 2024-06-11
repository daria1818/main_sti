<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;

class CDellinShippingChooseComponent extends CBitrixComponent
{
//	const MAP_TYPE_YANDEX = 'yandex';
//	const MAP_TYPE_GOOGLE = 'google';
//	const MAP_TYPE_NONE = 'none';

	/**
	 * @param array $params
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 */
	public function checkParams($params)
	{


	//    if()
//		if(!isset($params["INDEX"]))
//			throw new ArgumentNullException('params["INDEX"]');
//
//		if(!isset($params["STORES_LIST"]) || !is_array($params["STORES_LIST"]) || count($params["STORES_LIST"]) <= 0 )
//			throw new ArgumentNullException('params["STORES_LIST"]');
//
//		if(isset($params["MAP_TYPE"])
//			&& !in_array($params["MAP_TYPE"], array(self::MAP_TYPE_GOOGLE, self::MAP_TYPE_YANDEX, self::MAP_TYPE_NONE)
//		))
//		{
//			$params["MAP_TYPE"] = self::MAP_TYPE_YANDEX;
//		}

		return true;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		$userType = $params['USER_RESULT']['PERSON_TYPE_ID'];

		$terminalList = \Sale\Handlers\Delivery\Dellin\AjaxService::getTerminalsForAjaxOfSession();

		$currentTerminalField = $this->getFieldIdOnCODE($userType, 'TERMINAL_ID');
        $currentDeliveryTimeStartField  = $this->getFieldIdOnCODE($userType, 'DELLIN_DELIVERYTIME_START');
        $currentDeliveryTimeEndField = $this->getFieldIdOnCODE($userType, 'DELLIN_DELIVERYTIME_END');


        $params['dellin']['terminalList'] = $terminalList['terminals'];
        $params['dellin']['currentTerminalField'] = $currentTerminalField;
        $params['dellin']['currentDeliveryTimeStartField'] = $currentDeliveryTimeStartField;
        $params['dellin']['currentDeliveryTimeEndField'] = $currentDeliveryTimeEndField;
        $params['dellin']['terminalsMethod'] = $terminalList['terminals_method_id'];


		return $params;
	}

	private function getFieldIdOnCODE($userType, $code){

        $propsListToID = \DellinShipping\Kernel::getTerminalProps($userType);
        foreach ($propsListToID as $prop){

            if($prop['CODE'] == $code){
                $idField = $prop['ID'];
            }

        }
        return $idField;
    }

	/**
	 * void
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}


		$this->includeComponentTemplate();
	}


}