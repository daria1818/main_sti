<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Активация скидки");

use Bitrix\Sale;
?>

<? if (isset($_GET['fdv'])) :?>
	<?
	CModule::IncludeModule('iblock');
	CModule::IncludeModule("sale");
	CModule::IncludeModule("catalog");

	$rsRequest = CIBlockElement::GetList([], ['IBLOCK_ID' => 97, 'ACTIVE' => 'Y', 'CODE' => htmlspecialchars($_GET['fdv'])], false, false, []);

	if ($arRequest = $rsRequest->GetNextElement()) {
		$props = $arRequest->getProperties();
		$coupons = $props['COUPONS']['VALUE'];

		$siteId = "s1";
		$fuser = Sale\Fuser::getId();

		$basket = \Bitrix\Sale\Basket::create($siteId);
		$basket->setFUserId($fuser);

		$products = $props['PRODUCTS']['VALUE'];
		// https://stionline.ru/discounts/?fdv=5df181e4a4526f22

		foreach($products as $product) {
			$basketResult = \Bitrix\Catalog\Product\Basket::addProduct([
				'PRODUCT_ID' => $product,
    			'QUANTITY' => 1
			]); 
		}

		foreach($coupons as $coupon) {
			$arCoupon = CCatalogDiscountCoupon::GetByID($coupon);
			\Bitrix\Sale\DiscountCouponsManager::add($arCoupon['COUPON']);
		}

		LocalRedirect('/basket/');

		// ShowMessage(['TYPE' => 'OK', 'MESSAGE' => 'Скидка успешно применена']);
	} else {
		ShowError('Скидка не найдена');
	}
	?>
<? else :?>
	<? ShowError('Скидка не найдена'); ?>
<? endif; ?>

<a class="btn btn-default btn-lg" href="/">В каталог</a>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>