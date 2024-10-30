<?php
    namespace Classes;

    class popularProducts
    {
        // local\templates\aspro_next\components\bitrix\catalog.element\main2\component_epilog.php стр 504
        public static function selectSection($id, $filter, $section)
        {
            if(!$filter) {
                $list = \CIBlockSection::GetNavChain(false,$section, array(), true);
                foreach ($list as $arSectionPath){
                    break;
                }
                $parentSect = $arSectionPath['ID'];

                $dbList = \CIBlockSection::GetList(
                    false,
                    array(
                        'IBLOCK_ID' => '30',
                        'ID' => $parentSect,
                    ),
                    false,
                    array(
                        'ID',
                        'UF_LINE_GOODS'
                    )
                );

                if($secResult = $dbList->GetNext()) {
                    if($secResult["UF_LINE_GOODS"]) {
                        $filterSect = $secResult["UF_LINE_GOODS"];
                    }

                }
                if(!$filterSect) {
                    $filterSect = $parentSect;
                }

                $arFilter = array(
                    "IBLOCK_ID" => 30,
                    "ACTIVE_DATE" => "Y",
                    "INCLUDE_SUBSECTIONS" => "Y",
                    "ACTIVE" => "Y",
                    "SECTION_ID" => $filterSect,
                    "!ID" => $id
                );
                $arSelect = array("ID");
                $arSort = array("RAND" => "ASC");
                $dbGet = \CIBlockElement::GetList($arSort, $arFilter, false, array(), $arSelect);

                while ($arElem = $dbGet->Fetch()) {
                    $i++;
                    $sku = \CCatalogSKU::getOffersList(
                        $arElem['ID'],
                        0,
                        array('ACTIVE' => 'Y')
                    );
                    if(count($sku) && $i < 7) {
                        foreach ($sku as $key => $value) {
                            foreach ($value as $sku) {
                                $offer = \CCatalogProduct::GetByID($sku['ID']);
                                if($offer['QUANTITY']) {
                                    $arrID['ID'][] = $arElem['ID'];
                                }
                            }
                        }
                    }
                }
                $GLOBALS['arrFilterAssoc'][] = $arrID;

            } else {
                $GLOBALS['arrFilterAssoc'][] = $filter;
            }
            // \Classes\Debug::pr($GLOBALS['arrFilterAssoc']);

        }
        // local/templates/aspro_next/components/bitrix/catalog.top/main/template.php
        public function goods() {

        }
    }
