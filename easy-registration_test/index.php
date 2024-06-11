<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
include_once('./functions.php');
CModule::IncludeModule('iblock'); 
?>

<?
if ($_GET['code'] && !$USER->IsAuthorized()){
	$res = check_code($_GET['code']);

	$registration = empty($res["REG_DATE"]["~VALUE"]) ? "first" : "second";

	if ($res){
		if(strtotime($res["CODE_LIFE_TIME"]["~VALUE"]) >= strtotime(date('d.m.Y')) && $res["REG_DATE_2"]["~VALUE"] == ''){?>
			<?$APPLICATION->IncludeComponent(
				"rtop:main.register.test", 
				".default", 
				array(
					"USER_PROPERTY_NAME" => "",
					"SEF_MODE" => "Y",
					"SHOW_FIELDS" => array(
						0 => "EMAIL",
						1 => "NAME",
						2 => "SECOND_NAME",
						3 => "LAST_NAME",
						4 => "PERSONAL_PHONE",
						5 => "PERSONAL_CITY",
					),
					"REQUIRED_FIELDS" => array(
						0 => "EMAIL",
					),
					"AUTH" => "Y",
					"USE_BACKURL" => "Y",
					"SUCCESS_PAGE" => "",
					"SET_TITLE" => "Y",
					"USER_PROPERTY" => array(
						0 => "UF_INN_KOMPANII",
						1 => "UF_SPACIALIZATSIA",
					),
					"SEF_FOLDER" => "/",
					"COINS" => $res["ACCRUAL"]["~VALUE"],
					"COMPONENT_TEMPLATE" => ".default",
					"COMPOSITE_FRAME_MODE" => "A",
					"COMPOSITE_FRAME_TYPE" => "AUTO",
					"LINK_ID" => $res['ID'],
					"REGISTRATION_STATUS" => $registration
				),
				false
			);?> 
		<?}elseif($res["CODE_LIFE_TIME"]["~VALUE"] != ''){
			echo $result = 'По данной ссылке нельзя зарегистрироваться';	
		}else{
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

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>