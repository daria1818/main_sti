<?php

use Pwd\Helpers\UserHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Генератор форм");
$APPLICATION->SetTitle("Генератор форм");
?>
<?php
if (!UserHelper::isModeratorOfForms()) LocalRedirect('/personal');
?>
    <h1>Генерация формы (со сылкой на пост для анализа текста)</h1>
<?php
$APPLICATION->IncludeComponent(
    "pwd:form_generation.form",
    "",
    []
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>