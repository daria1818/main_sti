<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
CJSCore::Init(array("jquery"));
use \Bitrix\Main\Localization\Loc;?>
<?if($_SESSION['RB_PAY_COUNT'] > 0 && $arResult['RB']['ballance'] > 0 && !isset($_GET['ORDER_ID'])){?>
	<script>
		function afterFormReload(e) 
		{
			if (e != undefined)
			{
				if(typeof e == 'string')
					return false;

				if(e.order == undefined)
					return false;

				if(e.order.TOTAL != undefined)
				{
					var used_rb_pay = document.querySelectorAll('.used_rb_pay');
					if(used_rb_pay.length > 0)
					{
						for (var i = 0; i < used_rb_pay.length; i++)
						{
							if(e.order.TOTAL.NEGATIVE_RB_BONUS === 'Y')
								used_rb_pay[i].innerHTML = '<div class="rb-adding-block"><div class="bx-soa-cart-total-line"><span class="bx-soa-cart-t"><?=Loc::getMessage("RB_NEGATIVE")?></span></div></div>';
							else
								used_rb_pay[i].innerHTML = '<div class="rb-adding-block"><div class="bx-soa-cart-total-line"><span class="bx-soa-cart-t"><?=Loc::getMessage("RB_USED")?>:</span><span class="bx-soa-cart-d"><?=$_SESSION['RB_PAY_COUNT']?></span></div></div>';
						}
					}
				}

				return true;
			}
			else
				return false;
		}

		if (window.jQuery || window.$){
			$(document).ready(function()
			{				
				var totalsystem = BX('bx-soa-total'),
					pay_rb = BX('bx-soa-rbpay'),
					rb_input = BX('RB_PAY'),
					pay_count = BX('RB_PAY_COUNT');

				if (totalsystem)
				{
					if(pay_rb != null)
						BX.prepend(pay_rb, totalsystem);

					BX.addCustomEvent('onAjaxSuccess', afterFormReload);
					BX.Sale.OrderAjaxComponent.sendRequest();
				}
			});					
		}
	</script>

	<?if($arParams['SET_MESSAGE'] == "Y"){?>
		<div id="bx-soa-rbpay" class="table_rb_pay">
			<div class="rb_pay_title">
				<div class="bx-soa-section-title">
					<?=Loc::getMessage("RB_SOA_TITLE")?>
				</div>
			</div>
			<div class="bx-soa-section-content">
				<p class="used_rb_pay">
					<?=Loc::getMessage("RB_USED")?>: <?=$_SESSION['RB_PAY_COUNT']?>
				</p>
			</div>			
		</div>
	<?}?>
<?}?>