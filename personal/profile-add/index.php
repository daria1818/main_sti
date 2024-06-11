<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление нового профиля");
?>
<?
global $USER;
if(!$USER->isAuthorized()){
	LocalRedirect(SITE_DIR.'auth/');
} else{?>
	<?$APPLICATION->IncludeComponent(
		"manao:sale.personal.profile.add",
		"",
		Array(
			"PATH_TO_LIST" => "/personal/profiles/",
			"PATH_TO_DETAIL" => "/personal/profiles/#ID#",
		)
	);?>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>