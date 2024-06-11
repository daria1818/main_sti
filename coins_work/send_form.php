<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use \Bitrix\Main\Loader;
use Rubyroid\Loyality\RBTransactions;
Loader::includeModule("rubyroid.bonusloyalty");

function addCoin($arUser,$addCoins){
    $user = new CUser;
    $UFields = Array(
      "UF_LOYALTY_COIN" => $arUser['UF_LOYALTY_COIN'] + $addCoins,
    );
    $user->Update($arUser['ID'], $UFields);
    if($user->LAST_ERROR){
        $answer = $user->LAST_ERROR;
       return "<span style='color:red;'>" . $answer . "</span>";
    }else{
        RBTransactions::bonus([
            "TYPE_EVENT" => "MANUAL",
            "COIN" =>  $addCoins,
            "USER_ID" => $arUser['ID'],
            "BALANCE" => $arUser['UF_LOYALTY_COIN'],
            "AFTER_BALANCE"  => $arUser['UF_LOYALTY_COIN'] + $addCoins,
        ]);
        $answer = "<span style='color:limegreen;'>user ". $arUser['ID'] . " correct update, new coins ". ($arUser['UF_LOYALTY_COIN'] + $addCoins) . '</span>';
      return $answer;
    }

}


if(!empty($_POST['email']) && !empty($_POST['count'])){
    global $USER;
    $Filter = array("EMAIL" => $_POST['email']);
    $order = array('sort' => 'asc');
    $tmp = 'sort';
    $rsUsers = CUser::GetList($order, $tmp, $Filter,array("SELECT"=>array("UF_VK_ID","UF_LOYALTY_COIN","UF_PREV_VK_LIKE"),
                                                          "FIELDS"=>array("ID")));

    while($arBXUser = $rsUsers->NavNext()) {   
        $userInfo[] =$arBXUser;
    };

    if(count($userInfo) > 1){
        echo "<span style='color:red;'>Пользователей с таким email больше 1</span>";
    }else if(count($userInfo) < 1){
        echo "<span style='color:red;'>Пользователя с таким email не существует</span>";
    }else if(count($userInfo) == 1){
        $arUserGroups = CUser::GetUserGroup($userInfo[0]['ID']);
        if(in_array(33, $arUserGroups)){
            $answer = addCoin($userInfo[0],$_POST['count']);
            echo $answer;
            
        }else{
            echo "<span style='color:red;'>Добавьте пользователя в группу STICoin перед начислением</span>";
        }
    }
}
else{
    echo "<span style='color:red;'>Заполните все поля</span>";
}

?>