<?php
    namespace Classes;

    // $exel = new \Classes\Helper();
    // $exel->countProduts($arParams["IBLOCK_ID"], $arResult["IBLOCK_SECTION_ID"],$totalCount);


    class Helper
    {
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
