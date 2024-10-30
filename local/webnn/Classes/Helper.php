<?php
    namespace Classes;

    class Helper
    {

        public static function editSectionCourses()
        {
            $arSelect = array("ID", "NAME", "ACTIVE");
            $arOrder = array('SORT'=>'ASC');
            $arFilter = array(
                'SECTION_ID'=>8374, // Id категории
                'IBLOCK_ID' => 30,
            );

            $res = CIBlockElement::GetList($arOrder, $arFilter, $arSelect);
            while ($aItem = $res->GetNext())
            {
                $productsID[] = $aItem['ID'];
                if($aItem['ACTIVE'] === 'Y') {
                    $el = new CIBlockElement;
                    $el->Update(
                        $aItem['ID'], // айди элемента
                        ['ACTIVE' => 'Y'],
                        true
                    );
                }
            }
        }

        public function countProduts($IBLOCK_ID,$IBLOCK_SECTION_ID,$totalCount) {
            $navChain = CIBlockSection::GetNavChain($IBLOCK_ID, $IBLOCK_SECTION_ID);
            $parentSection = $navChain->GetNext();
            if($parentSection['ID'] === '7788' || $parentSection['ID'] === '8374') {
                $string = '<div class="item-stock"><span class="icon stock stock_range_2"></span><span class="value"><span class="">Достаточно</span></span></div>';
                $attachment = 'Доступно '.$totalCount.' ед';
                $quantityCount = preg_replace('#(<span class="">Достаточно</span>)#isU', $attachment, $string);
                $style = 'display: none';
            }
        }
    }
