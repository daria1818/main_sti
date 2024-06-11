<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}

global $USER;

?>
<?

    function set_coin_param(){
    	global $USER;
    	$rsUsers = CUser::GetList(
					array('sort' => 'asc'), 
					'sort', 
					array("ID" => $USER->GetID()),
					array("SELECT"=>array("UF_LOYALTY_COIN"),
					"FIELDS"=>array("ID"))
				);

		while($arBXUser = $rsUsers->NavNext()) { 
			$coinsCount = $arBXUser['UF_LOYALTY_COIN'];
		};

		$points_exchange_rate = Option::get("rubyroid.bonusloyalty", 'points_exchange_rate');
		$exchange_rate = intval($points_exchange_rate) > 0 ? $points_exchange_rate : 1;

		$COUNT_PAY_COINS = $_SESSION['COUNT_PAY_COINS'] / $exchange_rate;

		$coinsCount = $coinsCount - $COUNT_PAY_COINS;
		$WriteOfCoins = $coinsCount;
		if ($WriteOfCoins <= 0) $WriteOfCoins = 0;
		$user= new CUser;
		$user->Update($USER->GetID(), array("UF_LOYALTY_COIN"=> $WriteOfCoins));
    }
if (strlen($_GET['ORDER_ID']) > 0){
    set_coin_param();

}
?>

<?
if( $arResult['PAY_SYSTEM']['ID'] == 23 ){ // alfabank
	$orderObj = \Bitrix\Sale\Order::load($arResult['ORDER_ID']);
	$paymentCollection = $orderObj ->getPaymentCollection();
	$payment = $paymentCollection[0];
	$service  = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
	$context = \Bitrix\Main\Application::getInstance()->getContext();
	$initResult = $service->initiatePay($payment, $context->getRequest(), \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
	$buffered_output = $initResult->getTemplate();?>
	<div style="display: none;"><?=$buffered_output;?></div>
<?}?>

<? if (!empty($arResult["ORDER"])): ?>

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i'),
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				))?>
				<? if (!empty($arResult['ORDER']["PAYMENT_ID"])): ?>
					<?=Loc::getMessage("SOA_PAYMENT_SUC", array(
						"#PAYMENT_ID#" => $arResult['PAYMENT'][$arResult['ORDER']["PAYMENT_ID"]]['ACCOUNT_NUMBER']
					))?>
				<? endif ?>
				<? if ($arParams['NO_PERSONAL'] !== 'Y'): ?>
					<br /><br />
					<?=Loc::getMessage('SOA_ORDER_SUC1', ['#LINK#' => $arParams['PATH_TO_PERSONAL']])?>
				<? endif; ?>
			</td>
		</tr>
	</table>

	<?
	if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
	{
		if (!empty($arResult["PAYMENT"]))
		{
			foreach ($arResult["PAYMENT"] as $payment)
			{
				if ($payment["PAID"] != 'Y')
				{
					if (!empty($arResult['PAY_SYSTEM_LIST'])
						&& array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
					)
					{
						$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

						if (empty($arPaySystem["ERROR"]))
						{
							?>
							<br />
							<br />
							<?
						}
						else
						{
							?>
							<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
							<?
						}
					}
					else
					{
						?>
						<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
						<?
					}
				}
			}
		}
	}
	else
	{
		?>
		<br /><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
		<?
	}
	?>

<? else: ?>

	<b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>

<? endif ?>