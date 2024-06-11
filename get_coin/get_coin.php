<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$arPropsForGetListForOferL["SELECT"] = array("email", "UF_GET_COIN_BOT", "UF_LOYALTY_COIN","UF_DATE_GET_COIN");
$rsUsers = CUser::GetList(($by=""), ($order=""), $arFilter, $arPropsForGetListForOferL);
if ($rsUsers->SelectedRowsCount()>0) {
	while($rsUsersa = $rsUsers->Fetch()) {
		if($rsUsersa["UF_GET_COIN_BOT"] == '1'){
		$change_time = abs(date('d.m.Y') - $rsUsersa["UF_DATE_GET_COIN"]);
		if($change_time == 1){

		$total = $rsUsersa["UF_LOYALTY_COIN"]*25;
		if($change_time == 30){
			$arEventFields = array(
					"EMAIL"      => $rsUsersa["EMAIL"],
					"COINS_COUNT" => $rsUsersa["UF_LOYALTY_COIN"],
					"COINS_COUNT_RUB" => $total
				);
			CEvent::Send("DELETE_COIN", SITE_ID, $arEventFields);
	}
	}
};
} 
}
?>