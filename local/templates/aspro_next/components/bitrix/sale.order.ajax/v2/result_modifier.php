<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */
\CJSCore::Init(array("popup"));
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
$arParams['LIMIT_FEEDBACK_FORM'] = 'N';

if(!empty($arResult['BASKET_ITEMS'])){
    $rsElements = \CIBlockElement::GetList([], ['IBLOCK_ID' => CATALOG_IBLOCK, 'ID' => array_column($arResult['BASKET_ITEMS'], 'PRODUCT_ID')], false, false, ['ID', 'PROPERTY_PREKURSOR']);
    while($arElement = $rsElements->GetNext()){
        if($arElement['PROPERTY_PREKURSOR_VALUE'] == 'Да'){
            $arResult['WITH_PREKURSOR'] = 1;
            break;
        }
    }
}
/*limit basket*/
if(!empty($arResult['BASKET_ITEMS']))
{
    $arItems = array_column($arResult['BASKET_ITEMS'], 'PRODUCT_XML_ID');
    $arItems = array_map(function($item){
        return (preg_match('/#/', $item) ? strstr($item, '#', true) : $item);
    }, $arItems);
    $elements = CNextCache::CIBlockElement_GetList([], ['XML_ID' => $arItems], false, false, ['ID', 'SECTION_ID', 'IBLOCK_SECTION_ID', 'XML_ID']);
    $arSections = [];
    foreach($elements as $element)
    {
        $arSections[$element['XML_ID']] = $element['IBLOCK_SECTION_ID_SELECTED'];
    }

    $sections = CNextCache::CIBlockSection_GetList([], ['IBLOCK_ID' => CATALOG_IBLOCK, 'ID' => array_values($arSections), '!UF_LIMIT_BASKET' => false], false, ['ID', 'NAME', 'UF_LIMIT_BASKET'], false);
    if(!empty($sections))
    {
        $arParams['LIMIT_FEEDBACK_FORM'] = 'Y';
    }
}