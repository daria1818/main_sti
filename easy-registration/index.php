<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
include_once('./functions.php');
CModule::IncludeModule('iblock'); 
?>

<?
if ($_GET['code'] && !$USER->IsAuthorized()){
	$res = check_code($_GET['code']);
	if ($res){
		if(strtotime($res["CODE_LIFE_TIME"]["~VALUE"]) >= strtotime(date('d.m.Y'))){?>
			<?$APPLICATION->IncludeComponent("rtop:main.register","",Array(
		        "USER_PROPERTY_NAME" => "", 
		        "SEF_MODE" => "Y", 
		        "SHOW_FIELDS" => Array("LOGIN","EMAIL"), 
		        "REQUIRED_FIELDS" => Array("LOGIN","EMAIL"), 
		        // "SHOW_FIELDS" => Array("CITY","EVENT","LOGIN","EMAIL"), 
		        // "REQUIRED_FIELDS" => Array("CITY","EVENT","LOGIN","EMAIL"), 
		        "AUTH" => "Y", 
		        "USE_BACKURL" => "Y", 
		        "SUCCESS_PAGE" => "", 
		        "SET_TITLE" => "Y", 
		        "USER_PROPERTY" => Array(), 
		        "SEF_FOLDER" => "/", 
		        "VARIABLE_ALIASES" => Array(),
		        "COINS" => $res["ACCRUAL"]["~VALUE"]
		    )
		);?> 
		<?}else{
			echo $result = 'Срок действия ссылки истек';	
		}	
	}else{
		echo $result = 'Неверный код, проверьте ссылку';
	}

}else{
	header("Location: /");
	exit( );
}
?>
<style>
	.g-recaptcha{
		display: block !important;
	}
</style>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>