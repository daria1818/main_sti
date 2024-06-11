<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
CModule::IncludeModule('iblock');
CModule::IncludeModule("main");
    	global $USER;
    	$rsUsers = CUser::GetList(
					array('sort' => 'asc'), 
					'sort', 
					array("ID" => $USER->GetID()),
					array("SELECT"=>array("UF_LOYALTY_COIN"),
					"FIELDS"=>array("ID"))
				);

		while($arBXUser = $rsUsers->NavNext()) { 
			$coinsCount = $arBXUser['UF_LOYALTY_COIN'] * 25;
		};
		$coinsCount = $coinsCount - 1000;
		$WriteOfCoins = intdiv($coinsCount,25);
		if ($WriteOfCoins <= 0) $WriteOfCoins = 0;
		$user= new CUser;
		if($user->Update($USER->GetID(), array("UF_LOYALTY_COIN"=> $WriteOfCoins))){
			echo "GOOD";
		}else{
			  echo $user->LAST_ERROR;
		};
?>