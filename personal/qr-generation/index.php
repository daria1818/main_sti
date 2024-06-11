<?php

use Pwd\Helpers\UserHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Генератор QR форм");
$APPLICATION->SetTitle("Генератор QR форм");
?>
<?php
if (!UserHelper::isModeratorOfForms()) LocalRedirect('/personal');
?>
    <br>
    <h1>Генерация формы с QR-кодом</h1>
<?php
$APPLICATION->IncludeComponent(
    "pwd:form_generation.qr",
    "",
    []
);
?>
    <br>

<?php
$APPLICATION->IncludeComponent(
    "pwd:form_generation.qr.list",
    "",
    []
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>