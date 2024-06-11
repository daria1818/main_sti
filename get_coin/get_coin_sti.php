<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$arPropsForGetListForOferL["SELECT"] = array("email", "UF_GET_COIN_BOT", "UF_LOYALTY_COIN", "UF_LOYALTY_COIN");
$rsUsers = CUser::GetList(($by=""), ($order=""), $arFilter, $arPropsForGetListForOferL);?>
<?echo date('d.m.Y');?>
<table style="border-collapse: collapse;" border="1px solid black">
<tr>
	<td><b>Id</b></td>
	<td><b>Имя</b></td>
	<td><b>Почта</b></td>
	<td><b>Количество баллов StiCoin</b></td>
</tr>
<?if ($rsUsers->SelectedRowsCount()>0) {
	while($rsUsersa = $rsUsers->Fetch()) {
        $regMail = $rsUsersa["EMAIL"];
        $id = $rsUsersa["ID"];
        $name = $rsUsersa["NAME"];
		$user_coin= $rsUsersa["UF_LOYALTY_COIN"];
		if($user_coin != '' && $user_coin !=0){
		echo '<tr><td>'.$rsUsersa["ID"].'</td>';
		echo '<td>'.$name.'</td>';
		echo '<td>'.$regMail.'</td>';
		echo '<td>'.$user_coin.'</td></tr>';
		}
};
} 

?></table>