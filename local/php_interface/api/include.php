<?
use \Bitrix\Main\Application,
    \Bitrix\Main\Type\Collection,
    \Bitrix\Main\Loader,
    \Bitrix\Main\IO\File,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;
/**
 * Константа ИД каталога
 */
define('CATALOG_IBLOCK', 30);
/**
 * Константа ИД ИБ торговых предложений
 */
define('OFFERS_IBLOCK', 64);       
define('IBLOCKS_CATALOG', [CATALOG_IBLOCK, OFFERS_IBLOCK]);       
/**
 * Автоподключение классов.
 */
\Bitrix\Main\Loader::registerAutoLoadClasses($module = null, [
    'ApiFor1C\\ApiProvider' => '/local/php_interface/api/classes/apiProvider.php', //Класс для обработки входящих запросов
    'ApiFor1C\\Sender' => '/local/php_interface/api/classes/sender.php', //Класс для отправки запросов в 1С
    'ApiFor1C\\Handlers' => '/local/php_interface/api/classes/handlers.php', //Класс для описания событий (хэндлеров)
    'ApiFor1C\\BuyersAndCounterparties' => '/local/php_interface/api/classes/buyersAndCounterparties.php', //Класс для работы с покупателями и контрагентами
    'ApiFor1C\\OrderSender' => '/local/php_interface/api/classes/orderSender.php', // Класс для отправки информации по заказам
    //'ApiFor1C\\OrderUpdater' => '/local/php_interface/api/classes/orderUpdater.php',
    //'ApiFor1C\\Update\\Product' => '/local/php_interface/api/classes/updateProduct.php',
    //'ApiFor1C\\Update\\Sales' => '/local/php_interface/api/classes/salesUpdate.php',
    //'ApiFor1C\\Check\\Product' => '/local/php_interface/api/classes/checkProduct.php',
    'ApiFor1C\\ToolsApi' => '/local/php_interface/api/classes/tools.php', //Класс с различнами вспомогательными функциями
]);

/**
 * Инициализация хэндлеров
 */
ApiFor1C\Handlers::init();

Loader::includeModule('aspro.next');

class CNextCustom extends CNext
{
    public static function GetSKUPropsArray(&$arSkuProps, $iblock_id=0, $type_view="list", $hide_title_props="N", $group_iblock_id="N", $arItem = array(), $offerShowPreviewPictureProps = array())
    {
        $arSkuTemplate = array();
        $class_title=($hide_title_props=="Y" ? "hide_class" : "show_class");
        $class_title.=' bx_item_section_name';
        if($iblock_id){
            //$arPropsSku=CNext::GetPropertyViewType($iblock_id);
            $arPropsSku=CIBlockSectionPropertyLink::GetArray($iblock_id);
            if($arPropsSku){
                foreach ($arSkuProps as $key=>$arProp){
                    if($arPropsSku[$arProp["ID"]]){
                        $arSkuProps[$key]["DISPLAY_TYPE"]=$arPropsSku[$arProp["ID"]]["DISPLAY_TYPE"];
                    }
                }
            }
        }?>

        <?
        $bTextViewProp = (Option::get(parent::moduleID, "VIEW_TYPE_HIGHLOAD_PROP", "N", SITE_ID) == "Y");

        $arCurrentOffer = $arItem['OFFERS'][$arItem['OFFERS_SELECTED']];
        $j = 0;
        $arFilter = $arShowValues = array();

        /*get correct values*/
        foreach ($arSkuProps as $key => $arProp){
            $strName = 'PROP_'.$arProp['ID'];
            $arShowValues = parent::GetRowValues($arFilter, $strName, $arItem);

            if(in_array($arCurrentOffer['TREE'][$strName], $arShowValues))
            {
                $arFilter[$strName] = $arCurrentOffer['TREE'][$strName];
            }
            else
            {
                $arFilter[$strName] = $arShowValues[0];
            }

            /*if($arParams['SHOW_ABSENT'])
            {*/
                $arCanBuyValues = $tmpFilter = array();
                $tmpFilter = $arFilter;
                foreach($arShowValues as $value)
                {
                    $tmpFilter[$strName] = $value;
                    if(parent::GetCanBuy($tmpFilter, $arItem))
                    {
                        $arCanBuyValues[] = $value;
                    }
                }
            /*}
            else
            {
                $arCanBuyValues = $arShowValues;
            }*/

            $arSkuProps[$key] = parent::UpdateRow($arFilter[$strName], $arShowValues, $arCanBuyValues, $arProp, $type_view);
        }
        /**/

        if($group_iblock_id=="Y"){
            foreach ($arSkuProps as $iblockId => $skuProps){
                $arSkuTemplate[$iblockId] = array();
                $j = 0;
                foreach ($skuProps as $key=>&$arProp){
                    $templateRow = '';
                    $class_title.= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
                    $hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"]=="Y") ? '<div class="hint"><span class="icon"><i>?</i></span><div class="tooltip">'.$arProp["HINT"].'</div></div>' : '');
                    if(($arProp["DISPLAY_TYPE"]=="P" || $arProp["DISPLAY_TYPE"]=="R" ) && $type_view!= 'block' ){
                        $templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="SELECT" data-id="'.$arProp['ID'].'">'.
        '<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
        '<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
                        foreach ($arProp['VALUES'] as $arOneValue){
                            //if($arOneValue['ID']>0){
                                $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                                $templateRow .= '<option '.$arOneValue['SELECTED'].' '.$arOneValue['DISABLED'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="select" data-onevalue="'.$arOneValue['ID'].'" ';
                                if($arProp["DISPLAY_TYPE"]=="R"){
                                    $templateRow .= 'data-img_src="'.$arOneValue["PICT"]["SRC"].'" ';
                                }
                                $templateRow .= 'title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'">';
                                $templateRow .= '<span class="cnt">'.$arOneValue['NAME'].'</span>';
                                $templateRow .= '</option>';
                            //}
                        }
                        $templateRow .= '</select></div>'.
        '</div></div>';
                    }elseif ('PICT' == $arProp['SHOW_MODE'] || 'TEXT' == $arProp['SHOW_MODE']){
                        $templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
        '<span class="'.$class_title.'">'.$hint_block.'<span>Срок годности</span></span>'.
        '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
                        foreach ($arProp['VALUES'] as $arOneValue){
                            //if($arOneValue['ID']>0){
                                $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                                $templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
                            //}
                        }
                        $templateRow .= '</ul></div>'.
        '</div></div>';
                    }
                    $arSkuTemplate[$iblockId][$arProp['CODE']] = $templateRow;
                }
            }
        }else{
            foreach ($arSkuProps as $key=>&$arProp){
                $templateRow = '';
                $class_title.= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
                $hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"]=="Y") ? '<div class="hint"><span class="icon"><i>?</i></span><div class="tooltip">'.$arProp["HINT"].'</div></div>' : '');
                if(($arProp["DISPLAY_TYPE"]=="P" || $arProp["DISPLAY_TYPE"]=="R" ) && $type_view!= 'block' ){
                    $templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="SELECT" data-id="'.$arProp['ID'].'">'.
    '<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
    '<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
                    foreach ($arProp['VALUES'] as $arOneValue){
                        //if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            $templateRow .= '<option '.$arOneValue['SELECTED'].' '.$arOneValue['DISABLED'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="select" data-onevalue="'.$arOneValue['ID'].'" ';
                            if($arProp["DISPLAY_TYPE"]=="R"){
                                $templateRow .= 'data-img_src="'.$arOneValue["PICT"]["SRC"].'" ';
                            }
                            $templateRow .= 'title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'">';
                            $templateRow .= '<span class="cnt">'.$arOneValue['NAME'].'</span>';
                            $templateRow .= '</option>';
                        //}
                    }
                    $templateRow .= '</select></div>'.
    '</div></div>';
                }elseif ('PICT' == $arProp['SHOW_MODE'] || 'TEXT' == $arProp['SHOW_MODE']){
                    $templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
    '<span class="'.$class_title.'">'.$hint_block.'<span>Срок годности</span></span>'.
    '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
                    foreach ($arProp['VALUES'] as $arOneValue){
                        //if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            $templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
                        //}
                    }
                    $templateRow .= '</ul></div>'.
    '</div></div>';
                }
                $arSkuTemplate[$arProp['CODE']] = $templateRow;
            }
        }
        unset($templateRow, $arProp);
        return $arSkuTemplate;
    }
    public static function ShowHeaderPhones($class = '', $bFooter = false){
        static $hphones_call;
        global $arRegion;

        $iCalledID = ++$hphones_call;
        $arBackParametrs = parent::GetBackParametrsValues(SITE_ID_CUSTOM);
        $iCountPhones = ($arRegion ? count($arRegion['PHONES']) : $arBackParametrs['HEADER_PHONES']);
        $regionId = ($arRegion ? $arRegion['ID'] : '');
        if($arRegion){
            $frame = new \Bitrix\Main\Page\FrameHelper('header-allphones-block'.$iCalledID);
            $frame->begin();
        }
        ?>
        <?if($iCountPhones):?>
            <?
            $phone = ($arRegion ? $arRegion['PHONES'][0] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_0']);
            $href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
            ?>
            <?if($bFooter):?>
                <div class="phone blocks">
            <?endif;?>
            <div class="phone<?=($iCountPhones > 1 ? ' with_dropdown' : '')?><?=($class ? ' '.$class : '')?>">
                <?/*?><i class="svg svg-phone"></i><?*/?>
                <a class="clio-tel_link clio-link" rel="nofollow" href="<?=$href?>"><?=$phone?></a>
                <?if($iCountPhones > 1):?>
                    <div class="dropdown scrollbar">
                        <div class="wrap">
                            <?for($i = 1; $i < $iCountPhones; ++$i):?>
                                <?
                                $phone = ($arRegion ? $arRegion['PHONES'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_'.$i]);
                                $href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
                                $description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_'.$i]);
                                $description = (strlen($description) ? '<span>'.$description.'</span>' : '');
                                ?>
                                <div class="more_phone">
                                    <a class="clio-tel_link clio-link <?=(strlen($description) ? '' : 'no-decript')?>" rel="nofollow" href="<?=$href?>"><?=$phone?><?=$description?></a>
                                </div>
                            <?endfor;?>
                        </div>
                    </div>
                <?endif;?>
            </div>
            <?if($bFooter):?>
                </div>
            <?endif;?>
        <?endif;?>
        <?
        if($arRegion){
            $frame->end();
        }
    }
    public static function ShowBasketWithCompareLink($class_link='top-btn hover', $class_icon='', $show_price = false, $class_block='', $force_show = false, $bottom = false){?>
        <?global $APPLICATION, $arTheme, $arBasketPrices;
        static $basket_call;
        $type_svg = '';
        if($class_icon)
        {
            $tmp = explode(' ', $class_icon);
            $type_svg = '_'.$tmp[0];
        }
        $userID = self::GetUserID();

        $iCalledID = ++$basket_call;?>
        <ul class="clio-header_icon_svg">
        <?//if(($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'NORMAL' || ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'BOTTOM' && $bottom)) || $force_show):?>
            <?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-basket-with-compare-block'.$iCalledID);?>

                <?/* COMPARE */?>
                <?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N' && false):?>
                <li class="clio-market_icon_item clio-list">
                    <?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
                        array(
                            "COMPONENT_TEMPLATE" => ".default",
                            "PATH" => SITE_DIR."ajax/show_compare_preview_top.php",
                            "AREA_FILE_SHOW" => "file",
                            "AREA_FILE_SUFFIX" => "",
                            "AREA_FILE_RECURSIVE" => "Y",
                            "CLASS_LINK" => $class_link,
                            "CLASS_ICON" => $class_icon,
                            "FROM_MODULE" => "Y",
                            "EDIT_TEMPLATE" => "standard.php"
                        ),
                        false, array('HIDE_ICONS' => 'Y')
                    );?>
                </li>
                <?endif;?>
                <?if(self::getShowBasket()):?>
                    <?/* DELAYED */?>
                    <li class="clio-market_icon_item clio-list">
                        <!-- noindex -->
                        <a rel="nofollow" class="clio-link basket-link delay <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : '');?>" href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>#delayed" title="<?=$arBasketPrices['DELAY_SUMM_TITLE'];?>">
                            <span class="js-basket-block">
                                <img src="<?=SITE_TEMPLATE_PATH?>/images/svg/heart.svg" class="clio-menu_icon_item" alt="сравнение">
                                <span class="clio-red_notification count"><?=$arBasketPrices['DELAY_COUNT'];?></span>
                            </span>
                        </a>
                        <!-- /noindex -->
                    </li>

                    <?/* BASKET */?>
                    <li class="clio-market_icon_item clio-list clio-basket-js">
                        <!-- noindex -->
                        <a rel="nofollow" class="clio-link basket-link basket <?=($show_price ? 'has_prices' : '');?> <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : '');?>" href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>" title="<?=$arBasketPrices['BASKET_SUMM_TITLE'];?>">
                            <span class="js-basket-block">
                                <img src="<?=SITE_TEMPLATE_PATH?>/images/svg/corzina.svg" class="clio-menu_icon_item" alt="сравнение">
                                <span class="clio-red_notification count"><?=$arBasketPrices['BASKET_COUNT'];?></span>
                            </span>
                        </a>
                        <!-- /noindex -->
                    </li>
                    
                    <?/* CABINET */?>
                    <li class="clio-market_icon_item clio-list <?=!$userID?:'clio-login-completed'?>">
                        <?=self::ShowCabinetLink(true, true);?>
                    </li>
                <?endif;?>
            <?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-basket-with-compare-block'.$iCalledID, '');?>
        <?//endif;?>
        </ul>
    <?}
    public static function ShowCabinetLink($icon=true, $text=true, $class_icon='', $show_mess=false, $message=''){
        global $APPLICATION, $arTheme;
        static $hauth_call;

        $iCalledID = ++$hauth_call;

        $type_svg = '';
        if($class_icon)
        {
            $tmp = explode(' ', $class_icon);
            $type_svg = '_'.$tmp[0];
        }
        $html = '<!-- noindex -->';
        $userID = self::GetUserID();
        if(!$message)
            $message = Loc::getMessage('CABINET_LINK');

        if($userID)
        {
            global $USER;

            $html .= '<a rel="nofollow" title="'.Loc::getMessage('CABINET_LINK').'" class="clio-link'.($text ? /*' with_dropdown'*/ '' : '').'" href="'.$arTheme['PERSONAL_PAGE_URL']['VALUE'].'">';
            if($icon)
                $html .= self::showIconSvg('cabinet', SITE_TEMPLATE_PATH.'/images/svg/User_icon_new.svg', $message, $class_icon);

            if($text)
                $html .= '';

                if ($text)
                    $html .= '<p class="clio-full-name">'.$USER->GetFullName().'</p>';
                if($show_mess)
                    $html .= '<span class="title">'.$message.'</span>';

            if($text)
                $html .= '';

            $html .= '</a>';
        }
        else
        {
            $url = ((isset($_GET['backurl']) && $_GET['backurl']) ? $_GET['backurl'] : $APPLICATION->GetCurUri());
            $html .= '<a rel="nofollow" title="'.Loc::getMessage('CABINET_LINK').'" class="personal-link dark-color animate-load" data-event="jqm" data-param-type="auth" data-param-backurl="'.htmlspecialcharsbx($url).'" data-name="auth" href="'.$arTheme['PERSONAL_PAGE_URL']['VALUE'].'">';
            if($icon)
                $html .= self::showIconSvg('cabinet', SITE_TEMPLATE_PATH.'/images/svg/User_icon_new.svg', $message, $class_icon);
            if($text)
                $html .= '<span class="wrap">';

                if($text)
                    $html .= '<span class="name">'.Loc::getMessage('LOGIN').'</span>';
                if($show_mess)
                    $html .= '<span class="title">'.$message.'</span>';
            if($text)
                $html .= '</span>';

            $html .= '</a>';
        }
        $html .= '<!-- /noindex -->';?>

        <?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-auth-block'.$iCalledID);?>
            <?=$html;?>
        <?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-auth-block'.$iCalledID);?>

    <?}
}
?>