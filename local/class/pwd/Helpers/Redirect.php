<?php


namespace Pwd\Helpers;

use \Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;

class Redirect
{
    public function get(?int $id, ?int $iblockID)
    {
        if (!self::getAvailable($id)) {
            Loader::includeModule('iblock');
            // Ищем заполнено ли свойство
            $redirectElement = \CIBlockElement::GetList(
                [],
                ['ID' => $id, 'IBLOCK_ID' => $iblockID],
                false,
                ['nTopCount' => 1],
                ['ID', 'PROPERTY_REDIRECT.DETAIL_PAGE_URL', 'PROPERTY_REDIRECT']
            )->GetNext();

            if (
                $redirectElement['PROPERTY_REDIRECT_VALUE'] &&
                $redirectElement['PROPERTY_REDIRECT_DETAIL_PAGE_URL'] &&
                self::getAvailable($redirectElement['PROPERTY_REDIRECT_VALUE'])
            ) {
                return $redirectElement['PROPERTY_REDIRECT_DETAIL_PAGE_URL'];
            }
        }

        return null;
    }

    public function getAvailable(?int $id)
    {
        if (!$id) return false;

        Loader::includeModule('catalog');
        $product = ProductTable::getList([
            'filter' => ['=ID' => $id],
            'select' => ['AVAILABLE', 'ID'],
            'limit' => 1,
        ])->fetch();

        return $product['AVAILABLE'] != 'N';
    }
}