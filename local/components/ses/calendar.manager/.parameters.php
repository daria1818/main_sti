<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "SELECTION_DAYS" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("PARAM1_NAME"),
            "TYPE" => "LIST",
            "VALUES" => [
                "temp1" => Loc::getMessage("OPTION1"),
                "temp2" => Loc::getMessage("OPTION2"),
            ],
            "DEFAULT" => "OPTION1",
            "ADDITIONAL_VALUES" => "Y",
        ],
        "CACHE_TIME"  =>  ["DEFAULT"=>36000000],
    ],
];
?>
