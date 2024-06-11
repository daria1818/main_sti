<?php
use Bitrix\Main\EventManager;
use Pwd\EventHandler\Iblock;

$eventManager = EventManager::getInstance();
// Обновлении товара
$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    [Iblock::class, 'OnAfterIBlockElementUpdateAdd']
);
$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementAdd",
    [Iblock::class, 'OnAfterIBlockElementUpdateAdd']
);
