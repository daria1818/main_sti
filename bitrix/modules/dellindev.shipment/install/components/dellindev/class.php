<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\Crm\Order\Shipment;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Settings\LayoutSettings;


Loc::loadMessages(__FILE__);

class CCrmDellinRequest extends \CBitrixComponent
{
	protected $userId = 0;
	protected $userPermissions;
	protected $errors = array();
	protected $isInternal = false;

	public function onPrepareComponentParams($arParams)
	{

//		var_dump($arParams);
//		die();

		global  $APPLICATION;

		$arParams['PATH_TO_ORDER_DETAILS'] = CrmCheckPath('PATH_TO_ORDER_DETAILS', $arParams['PATH_TO_ORDER_DETAILS'], $APPLICATION->GetCurPage().'?order_id=#order_id#&details');
		$arParams['PATH_TO_ORDER_SHOW'] = CrmCheckPath('PATH_TO_ORDER_SHOW', $arParams['PATH_TO_ORDER_SHOW'], $APPLICATION->GetCurPage().'?order_id=#order_id#&show');
		$arParams['PATH_TO_ORDER_EDIT'] = CrmCheckPath('PATH_TO_ORDER_EDIT', $arParams['PATH_TO_ORDER_EDIT'], $APPLICATION->GetCurPage().'?order_id=#order_id#&edit');
		$arParams['PATH_TO_QUOTE_EDIT'] = CrmCheckPath('PATH_TO_QUOTE_EDIT', $arParams['PATH_TO_QUOTE_EDIT'], $APPLICATION->GetCurPage().'?quote_id=#quote_id#&edit');
		$arParams['PATH_TO_INVOICE_EDIT'] = CrmCheckPath('PATH_TO_INVOICE_EDIT', $arParams['PATH_TO_INVOICE_EDIT'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&edit');
		$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'?company_id=#company_id#&show');
		$arParams['PATH_TO_CONTACT_SHOW'] = CrmCheckPath('PATH_TO_CONTACT_SHOW', $arParams['PATH_TO_CONTACT_SHOW'], $APPLICATION->GetCurPage().'?contact_id=#contact_id#&show');
		$arParams['PATH_TO_USER_PROFILE'] = CrmCheckPath('PATH_TO_USER_PROFILE', $arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');
		$arParams['PATH_TO_BUYER_PROFILE'] = CrmCheckPath('PATH_TO_BUYER_PROFILE', $arParams['PATH_TO_BUYER_PROFILE'], '/shop/settings/sale_buyers_profile/?USER_ID=#user_id#&lang='.LANGUAGE_ID);
		$arParams['PATH_TO_USER_BP'] = CrmCheckPath('PATH_TO_USER_BP', $arParams['PATH_TO_USER_BP'], '/company/personal/bizproc/');
		$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

		if ($this->isExportMode())
		{
			$this->prepareExportParams($arParams);
		}

		$arParams['BUILDER_CONTEXT'] = isset($arParams['BUILDER_CONTEXT']) ? $arParams['BUILDER_CONTEXT'] : '';
		if (
			$arParams['BUILDER_CONTEXT'] != Url\ShopBuilder::TYPE_ID
			&& $arParams['BUILDER_CONTEXT'] != Url\ProductBuilder::TYPE_ID
		)
		{
			$arParams['BUILDER_CONTEXT'] = Url\ShopBuilder::TYPE_ID;
		}

		return $arParams;


//
//
//
//		return $arParams;
	}

	protected function init()
	{
		if(!CModule::IncludeModule('crm'))
		{
			$this->errors[] = Loc::getMessage('CRM_MODULE_NOT_INSTALLED');
			return false;
		}

		if(!CModule::IncludeModule('currency'))
		{
			$this->errors[] = Loc::getMessage('CRM_MODULE_NOT_INSTALLED_CURRENCY');
			return false;
		}

		if(!CModule::IncludeModule('catalog'))
		{
			$this->errors[] = Loc::getMessage('CRM_MODULE_NOT_INSTALLED_CATALOG');
			return false;
		}

		if (!CModule::IncludeModule('sale'))
		{
			$this->errors[] = Loc::getMessage('CRM_MODULE_NOT_INSTALLED_SALE');
			return false;
		}

		$this->userPermissions = CCrmPerms::GetCurrentUserPermissions();

		if (!\Bitrix\Crm\Order\Permissions\Order::checkReadPermission(0, $this->userPermissions))
		{
			$this->errors[] = new Main\Error(Loc::getMessage('CRM_PERMISSION_DENIED'));
			return false;
		}


		$this->userId = CCrmSecurityHelper::GetCurrentUserID();
		$this->isInternal = !empty($this->arParams['INTERNAL_FILTER']);
		CUtil::InitJSCore(array('ajax', 'tooltip'));
		return true;
	}



	protected function getHeaders()
	{
		$result = array(
			array('id' => 'SHIPMENT_SUMMARY', 'name' => Loc::getMessage('CRM_COLUMN_SHIPMENT_SUMMARY'), 'sort' => 'account_number', 'default' => true, 'editable' => false),
			array('id' => 'DELIVERY_SERVICE', 'name' => Loc::getMessage('CRM_COLUMN_DELIVERY_SERVICE'), 'sort' => 'delivery_name', 'default' => true),
			array('id' => 'PRICE_DELIVERY_CURRENCY', 'name' => Loc::getMessage('CRM_COLUMN_PRICE_DELIVERY_CURRENCY'), 'sort' => 'price_delivery', 'default' => true, 'editable' => false),
			array('id' => 'ALLOW_DELIVERY', 'name' => Loc::getMessage('CRM_COLUMN_ALLOW_DELIVERY'), 'sort' => 'allow_delivery', 'default' => true, 'editable' => true),
			array('id' => 'DEDUCTED', 'name' => Loc::getMessage('CRM_COLUMN_DEDUCTED'), 'sort' => 'deducted', 'default' => true, 'editable' => false),
			array('id' => 'STATUS_ID', 'name' => Loc::getMessage('CRM_COLUMN_STATUS_ID'), 'sort' => 'status_id', 'default' => true, 'editable' => false),
			array('id' => 'ID', 'name' => Loc::getMessage('CRM_COLUMN_ID'), 'sort' => 'id', 'editable' => false, 'type' => 'int'),
			array('id' => 'ORDER_ID', 'name' => Loc::getMessage('CRM_COLUMN_ORDER_ID'), 'sort' => 'order_id', 'default' => false, 'editable' => false),
			array('id' => 'DATE_INSERT', 'name' => Loc::getMessage('CRM_COLUMN_DATE_INSERT'), 'sort' => 'date_insert', 'editable' => false, 'type' => 'date', 'class' => 'date'),
			array('id' => 'CRM_COLUMN_DISCOUNT_PRICE', 'name' => Loc::getMessage('CRM_COLUMN_DISCOUNT_PRICE'), 'sort' => 'discount_price', 'editable' => false),
			array('id' => 'BASE_PRICE_DELIVERY', 'name' => Loc::getMessage('CRM_COLUMN_BASE_PRICE_DELIVERY'), 'sort' => 'base_price_delivery', 'editable' => false),
			array('id' => 'CUSTOM_PRICE_DELIVERY', 'name' => Loc::getMessage('CRM_COLUMN_CUSTOM_PRICE_DELIVERY'), 'sort' => false, 'editable' => false),
			array('id' => 'DATE_ALLOW_DELIVERY', 'name' => Loc::getMessage('CRM_COLUMN_DATE_ALLOW_DELIVERY'), 'sort' => false, 'editable' => false, 'type' => 'date', 'class' => 'date'),
			array('id' => 'EMP_ALLOW_DELIVERY', 'name' => Loc::getMessage('CRM_COLUMN_EMP_ALLOW_DELIVERY'), 'sort' => false, 'class' => 'username'),
			array('id' => 'DATE_DEDUCTED', 'name' => Loc::getMessage('CRM_COLUMN_DATE_DEDUCTED'), 'sort' => false, 'editable' => false, 'type' => 'date', 'class' => 'date'),
			array('id' => 'EMP_DEDUCTED', 'name' => Loc::getMessage('CRM_COLUMN_EMP_DEDUCTED'), 'sort' => false),
			array('id' => 'REASON_UNDO_DEDUCTED', 'name' => Loc::getMessage('CRM_COLUMN_REASON_UNDO_DEDUCTED'), 'sort' => false, 'editable' => false),
			array('id' => 'DELIVERY_DOC_NUM', 'name' => Loc::getMessage('CRM_COLUMN_DELIVERY_DOC_NUM'), 'sort' => false, 'editable' => false),
			array('id' => 'DELIVERY_DOC_DATE', 'name' => Loc::getMessage('CRM_COLUMN_DELIVERY_DOC_DATE'), 'sort' => 'false', 'editable' => false, 'type' => 'date', 'class' => 'date'),
			array('id' => 'TRACKING_NUMBER', 'name' => Loc::getMessage('CRM_COLUMN_TRACKING_NUMBER'),'sort' => false, 'editable' => false),
			array('id' => 'XML_ID', 'name' => 'XML_ID', 'sort' => false, 'editable' => false),
			array('id' => 'MARKED', 'name' => Loc::getMessage('CRM_COLUMN_MARKED'), 'sort' => 'marked', 'editable' => false),
			array('id' => 'DATE_MARKED', 'name' => Loc::getMessage('CRM_COLUMN_DATE_MARKED'), 'sort' => false, 'editable' => false),
			array('id' => 'EMP_MARKED', 'name' => Loc::getMessage('CRM_COLUMN_EMP_MARKED'), 'sort' => false, 'editable' => false),
			array('id' => 'REASON_MARKED', 'name' => Loc::getMessage('CRM_COLUMN_REASON_MARKED'), 'sort' => false, 'editable' => false),
			array('id' => 'CURRENCY', 'name' => Loc::getMessage('CRM_COLUMN_CURRENCY'), 'sort' => 'currency', 'editable' => false),
			array('id' => 'RESPONSIBLE', 'name' => Loc::getMessage('CRM_COLUMN_RESPONSIBLE'), 'sort' => 'responsible_id', 'editable' => false, 'class' => 'username'),
			array('id' => 'DATE_RESPONSIBLE_ID', 'name' => Loc::getMessage('CRM_COLUMN_DATE_RESPONSIBLE_ID'), 'sort' => 'date_responsible_id', 'editable' => false, 'type' => 'date', 'class' => 'date'),
			array('id' => 'COMMENTS', 'name' => Loc::getMessage('CRM_COLUMN_COMMENTS'), 'sort' => false, 'editable' => false),
			array('id' => 'TRACKING_STATUS', 'name' => Loc::getMessage('CRM_COLUMN_TRACKING_STATUS'), 'sort' => 'tracking_status', 'editable' => false),
			array('id' => 'TRACKING_DESCRIPTION', 'name' => Loc::getMessage('CRM_COLUMN_TRACKING_DESCRIPTION'), 'sort' => 'tracking_description', 'editable' => false),
		);

		return $result;
	}



	public function executeComponent()
	{
		global $APPLICATION, $USER;

		if(!$this->init())
		{
			$this->showErrors();
			return false;
		}


		$this->IncludeComponentTemplate();
		include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/crm.order/include/nav.php');
		return $this->arResult['ROWS_COUNT'];
	}
}
?>

