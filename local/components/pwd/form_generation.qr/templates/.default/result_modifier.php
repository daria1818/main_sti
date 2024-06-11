<?php

(defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) || die();

/**
 * @var $arResult array
 * @var $arParams array
 * @var $component Pwd\Components\FormQrGenerationComponent
 * @var $this CBitrixComponentTemplate
 */

$arResult['GENERATION_MODE'] = $this->getComponent()->generationMode;
$arResult['ROW'] = $this->getComponent()->row;
$arResult['PARAMS'] = $this->getComponent()->params;
$arResult['SPEC_ENUMS'] = $this->getComponent()->specEnums;
$arResult['EMPL'] = $this->getComponent()->empl;
$this->getComponent()->setResultCacheKeys(['GENERATION_MODE', 'ROW', 'PARAMS', 'SPEC_ENUMS', 'EMPL']);