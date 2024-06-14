#!/usr/bin/php
<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use Rubyroid\Loyality\RBTransactions;
Loader::includeModule("rubyroid.bonusloyalty");

  $date = date("Y-m-d");
  file_get_contents("http://admin:123456789@vk.stident.ru/export?start_date=".$date);
  $parse_array = file_get_contents("http://admin:123456789@vk.stident.ru/files/export/main.csv");
  $parse_array = explode("\n", $parse_array);
  foreach ($parse_array as $parse_user_fields) {
    $arParseUsers[] = explode(";",$parse_user_fields); 
  }


$Filter = array("!UF_VK_ID" => false);
$order = array('sort' => 'asc');
$tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
$rsUsers = CUser::GetList($order, $tmp, $Filter,array("SELECT"=>array("UF_VK_ID","UF_LOYALTY_COIN","UF_PREV_VK_LIKE"),
                                                      "FIELDS"=>array("ID")));

while($arBXUser = $rsUsers->NavNext()) {   
    $BX_VK_ID = explode('vk.com/',$arBXUser['UF_VK_ID'])[1];
    $arBXUsers[$BX_VK_ID] = $arBXUser;  
};


$ratio_like =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_like')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_like') ;
$ratio_coment =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_coment')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_coment') ;
$ratio_repost =  empty(Option::get("rubyroid.bonusloyalty", 'ratio_repost')) ? 1 : Option::get("rubyroid.bonusloyalty", 'ratio_repost') ;

foreach ($arParseUsers as $ParseUser) {
  $PARSE_VK_ID = explode('vk.com/',$ParseUser[1])[1]; 
  if (array_key_exists($PARSE_VK_ID, $arBXUsers)){
    settype($ParseUser[2], "integer");
    settype($ParseUser[3], "integer");
    settype($ParseUser[4], "integer");
    $SelectBXuser = $arBXUsers[$PARSE_VK_ID];
    $user_coin = ($ParseUser[2]*$ratio_repost) + ($ParseUser[3]*$ratio_coment);
    $user_likes = $ParseUser[4] - $SelectBXuser['UF_PREV_VK_LIKE'];
    $user_likes1 = $user_likes <= 0 ? 0 : $user_likes * $ratio_like;

    $user = new CUser;
    $UFields = Array(
      "UF_LOYALTY_COIN" => $user_coin + $SelectBXuser["UF_LOYALTY_COIN"] + $user_likes1,
      "UF_PREV_VK_LIKE" => $user_likes + $SelectBXuser['UF_PREV_VK_LIKE'],
    );
    $user->Update($SelectBXuser["ID"], $UFields);
    if($user->LAST_ERROR){
      $strError .= $user->LAST_ERROR;
    }else{
      echo "<pre>";
      print_r($ParseUser);
      echo "</pre>";
      $arPoints = array();
      if($ParseUser[2])
      {
        $arPoints["REPOST"] = $ParseUser[2] * $ratio_repost;
      }
      if($ParseUser[3])
      {
        $arPoints["COMMENT"] = $ParseUser[3] * $ratio_coment;
      }
      if($ParseUser[4])
      {
        $arPoints["LIKE"] = $user_likes1;
      }

      if(count($arPoints) > 0)
      {
        foreach($arPoints as $event => $point)
        {
          RBTransactions::bonus([
            "TYPE_EVENT" => $event,
            "COIN" =>  $point,
            "USER_ID" => $SelectBXuser["ID"],
            "AFTER_BALANCE" => $SelectBXuser["UF_LOYALTY_COIN"] + $point,
          ]);
        }
      }

    }
    
  }
}
?>