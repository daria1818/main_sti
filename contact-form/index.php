<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->IncludeComponent(
    "pwd:form_generation.qr",
    "",
    []
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>