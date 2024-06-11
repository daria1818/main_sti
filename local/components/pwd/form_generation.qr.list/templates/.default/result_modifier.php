<?php

(defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) || die();

/**
 * @var $arResult array
 * @var $arParams array
 * @var $component Pwd\Components\FormQrGenerationListComponent
 * @var $this CBitrixComponentTemplate
 */

$arResult['ROWS'] = $this->getComponent()->rows;
$this->getComponent()->setResultCacheKeys(['ROWS']);