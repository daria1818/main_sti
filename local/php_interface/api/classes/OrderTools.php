<?php

namespace Api\Classes;

use Bitrix\Main;

class OrderTools
{
    /**
     * обработчик события перед созданием заказа
     * @param Main\Event $obEvent
     */
    public static function saleOrderBeforeSavedHandler(Main\Event $obEvent){
        $obOrder = $obEvent->getParameter("ENTITY");

        $arPropertyCollection = $obOrder->getPropertyCollection();

        $arUtmProps = ['utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign'];

        //сохраняем utm метки в заказ
        foreach ($arPropertyCollection as $obPropertyItem) {
            $sField = $obPropertyItem->getField("CODE");
            if(in_array($sField, $arUtmProps) && $_SESSION[$sField]){
                $obPropertyItem->setField("VALUE", $_SESSION[$sField]);
                // удаляем из сессии
                unset($_SESSION[$sField]);
            }
        }
    }
}