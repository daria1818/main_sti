<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
?>
<?if(isset($_POST['email'])) {
global $USER;
if(!$USER->IsAuthorized()):
$arPropsForGetListForOferL["SELECT"] = array("email");
$rsUsers = CUser::GetList(($by=""), ($order=""), $arFilter, $arPropsForGetListForOferL);
if ($rsUsers->SelectedRowsCount()>0) {

   while($rsUsersa = $rsUsers->Fetch()) {
      if ($rsUsersa["EMAIL"] == $_POST['email']) {

         $regMail = $rsUsersa["EMAIL"];
		 $user_id= $rsUsersa["ID"];
         break;
      };
   };
} 
if($regMail == ''){
$user = new CUser;
$pass = bin2hex(openssl_random_pseudo_bytes(4));
$arFields = Array(
  "NAME"              => $_POST['name'],
  "EMAIL"             => $_POST['email'],
  "LOGIN"             => $_POST['email'],
  "LID"               => "ru",
  "ACTIVE"            => "Y",
  "GROUP_ID"          => "6",
  "PASSWORD"          => $pass,
  "CONFIRM_PASSWORD"  => $pass
);

$ID = $user->Add($arFields);
if($ID != ""){
	$user_id=$ID;
}
$regMail = $_POST['email'];
$arEventFields = array(
    "EMAIL"      => $_POST['email'],
    "LOGIN"      => $_POST['email'],
    "PASSWORD"      => $pass
    );
CEvent::Send("NEW_USER_GET_COIN", SITE_ID, $arEventFields);



};
else:
$user_id=$USER->GetId();
endif;
$rsUser = CUser::GetByID($user_id); 
if ($arUser2 = $rsUser->Fetch())
{
	 $regMail = $arUser2['EMAIL'];
	 $get_coin=$arUser2['UF_GET_COIN_BOT'];
     $coin= $arUser2['UF_LOYALTY_COIN'];

}
$total = $coin + 200;
$rub_total=$total*25;
if($get_coin != 1):
$user = new CUser;
$fields = Array(
  "UF_GET_COIN_BOT" => 'Y',
  "UF_LOYALTY_COIN" => $total,
  "UF_DATE_GET_COIN" => date('d.m.Y'),
);
$user->Update($user_id, $fields);
$arEventFields = array(
    "EMAIL"      => $regMail,
    "LOGIN"      => $regMail,
	"COINS_COUNT" => $total,
	"RUB_COUNT" => $rub_total
    );
CEvent::Send("GET_COIN", SITE_ID, $arEventFields);

endif;
?>
<?}?>
