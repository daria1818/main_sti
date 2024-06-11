<?
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

	use Bitrix\Socialservices;

if(CModule::IncludeModule('socialservices')){


	//$propertyTypeID = "ITIN";
	//$countryID = 1; // 1 - Россия
	$propertyValue = "2222826218";
	 
	$client = new Socialservices\Properties\Client();
	$result = $client->getByInn($propertyValue);
	// echo '<pre style="">', print_r($result,1), '</pre>';
	// echo '<pre style="">', print_r($result), '</pre>';
}else{
	echo "No";
}


global $USER;
$arUserGroups = CUser::GetUserGroup($USER->GetID());
global $APPLICATION;
$APPLICATION->SetTitle("Работа с STICoin");
use Bitrix\Main\Page\Asset;
$dir = $APPLICATION->GetCurDir();
Asset::getInstance()->addCss($dir."style.css");
if(in_array(39, $arUserGroups)){
	CModule::IncludeModule("iblock");?>

	<h2>Форма для начисления баллов</h2>
	<form action="" id="send_coins" class="clio-form">
		<input type="email" name="email" class="clio-input" placeholder="Email пользователя">
		<input type="text" name="count" class="clio-input" placeholder="Количество STICoins">
		<button type="submit" class="clio-btn">Начислить</button>
		<div id="send_result"></div>
	</form>



	<h2>Форма для списания баллов</h2>
	<form action="" id="remove_coins" class="clio-form">
		<input type="email" name="email" class="clio-input" placeholder="Email пользователя">
		<input type="number" name="count" class="clio-input" placeholder="Количество STICoins">
		<button type="submit" class="clio-btn">Списать</button>
		<div id="remove_result"></div>
	</form>


	<script type="text/javascript">
		$("#send_coins").submit(function(e){
	    e.preventDefault();
	    $.ajax({
	        type: "POST",
	        url: "send_form.php",
	        data: $("#send_coins").serialize(),
	        success: function(data) {
	            $("#send_result").html(data);
	        }
	    });
	});
	</script>
	<script type="text/javascript">
		$("#remove_coins").submit(function(e){
	    e.preventDefault();
	    $.ajax({
	        type: "POST",
	        url: "remove_form.php",
	        data: $("#remove_coins").serialize(),
	        success: function(data) {
	            $("#remove_result").html(data);
	        }
	    });
	});
	</script>
<?}else{?>
	<h3 style="color: red;">Доступ запрещен, обратитесь к администратору сайта</h3>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>