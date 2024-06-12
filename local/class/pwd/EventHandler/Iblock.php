<?php


namespace Pwd\EventHandler;

use \Bitrix\Main\Loader;
use \Pwd\Entity\CatalogTable;


class Iblock
{
    public static function OnAfterIBlockElementUpdateAdd(&$arFields)
    {
        if (
            !$arFields["RESULT"] ||
            CatalogTable::getIblockId() != $arFields['IBLOCK_ID']
        ) {
            return true;
        }

        Loader::includeModule('iblock');

        $newPractice = $newPracticeXML = [];
        $barProps = \CIBlockElement::GetProperty(
            $arFields['IBLOCK_ID'],
            $arFields['ID'],
            'sort',
            'asc',
            ['CODE' => 'BAR']
        );
        while ($bar = $barProps->GetNext()) {
            $newPracticeXML[] = 'BAR_' . $bar['VALUE_ENUM'];
        }

        if (!empty($newPracticeXML)) {
            $practices = \CIBlockPropertyEnum::GetList(
                [],
                [
                    'IBLOCK_ID' => $arFields['IBLOCK_ID'],
                    'CODE' => 'PRACTICE',
                    'XML_ID' => $newPracticeXML,
                ]
            );
            while ($practice = $practices->GetNext()) {
                $newPractice[] = $practice['ID'];
            }
        }

        \CIBlockElement::SetPropertyValuesEx(
            $arFields['ID'],
            $arFields['IBLOCK_ID'],
            ['PRACTICE' => $newPractice ?: false]
        );
    }

}