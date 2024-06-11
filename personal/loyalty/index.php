<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Программа лояльности");
?>

<?if(CSite::InGroup([CONST_GROUP_ID_LOYALTY_TEST]) || $USER->GetID() == 4741){?>
    <?$APPLICATION->IncludeComponent("rubyroid:rubyroid.personal", "template1", Array(
	
	),
	false
);?>
    <?$APPLICATION->IncludeComponent(
        "rubyroid:rubyroid.history",
        "",
        Array()
    );?>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>