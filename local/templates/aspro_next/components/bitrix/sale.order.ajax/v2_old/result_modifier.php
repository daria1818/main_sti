<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);


////$arResult['ORDER_DATA']['WEIGHT'] = 2000;

//$arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT'] = 2;
//$arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT_FORMATED'] = "до 2 кг";

//$arResult['ORDER_DATA']['ORDER_WEIGHT'] = 2;
////$arResult['ORDER_WEIGHT_FORMATED'] => 0.02 кг

//$arResult['ORDER_WEIGHT'] = 2;
//$arResult['ORDER_WEIGHT_FORMATED'] = "до 2 кг";

// $test = Bitrix\Sale\Order::getList(array(
//   'select' => array("*"),
//   'filter' => array('USER_ID' => 3077),
//   'limit' => 1
// ))->fetchAll();
if(!empty($arResult['BASKET_ITEMS'])){
    $rsElements = \CIBlockElement::GetList([], ['IBLOCK_ID' => CATALOG_IBLOCK, 'ID' => array_column($arResult['BASKET_ITEMS'], 'PRODUCT_ID')], false, false, ['ID', 'PROPERTY_PREKURSOR']);
    while($arElement = $rsElements->GetNext()){
        if($arElement['PROPERTY_PREKURSOR_VALUE'] == 'Да'){
            $arResult['WITH_PREKURSOR'] = 1;
            break;
        }
    }
} 