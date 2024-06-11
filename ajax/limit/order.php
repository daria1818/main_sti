<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST')
	return;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$data = $request->getPostList()->toArray();
$data = $data['data'];

$person_type = $data['PERSON_TYPE'];

$orderProps = \Bitrix\Sale\Internals\OrderPropsTable::getList([
	'order' => ['SORT' => 'asc'],
	'filter' => ['ACTIVE' => 'Y', 'PERSON_TYPE_ID' => $person_type],
	'select' => ['ID', 'NAME']
])->fetchAll();
$arProps = [];
foreach($orderProps ?:[] as $prop)
{
	if(isset($data['ORDER_PROP_' . $prop['ID']]) && !empty($data['ORDER_PROP_' . $prop['ID']]))
		$arProps[] = $prop['NAME'] . ": " . $data['ORDER_PROP_' . $prop['ID']];
}

$arPropsString = implode("<br/>", $arProps);

$delivery = $data['DELIVERY_ID'];
$deliveryString = \Bitrix\Sale\Delivery\Services\Table::getList(['filter' => ['ID' => $delivery], 'select' => ['ID', 'NAME']])->fetch()['NAME'];
$pay_system = $data['PAY_SYSTEM_ID'];
$payString = \Bitrix\Sale\Internals\PaySystemActionTable::getList(['filter' => ['ID' => $pay_system], 'select' => ['ID', 'NAME']])->fetch()['NAME'];
$comment = $data['ORDER_DESCRIPTION'];
if(!empty($comment))
{
$comment = <<<MESSAGE
	<tr>
		<td colspan='2' style='text-align:center;font-size: 16px; font-weight: bold;'>Комментарий к заказу:</td>
	</tr>
	<tr>
		<td style='font-size: 14px'>{$comment}</td>
	</tr>
MESSAGE;
}


$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
$items = $basket->getBasketItems();
if(!empty($items))
{
	foreach ($items as $item)
	{
		if(!$item->isDelay())
		{
			$arProducts[] = [
				'NAME' => $item->getField('NAME'),
				'QUANTITY' => $item->getField('QUANTITY'),
				'PRICE' => $item->getField('PRICE'),
				'BASE_PRICE' => $item->getField('BASE_PRICE'),
				'DETAIL_PAGE_URL' => $item->getField('DETAIL_PAGE_URL'),
				'MEASURE_NAME' => $item->getField('MEASURE_NAME'),
			];
			$item->delete();
		}
	}

	$arProductsString = '';

	foreach($arProducts as $product)
	{
		$arProductsString .= "<tr><td style='width: 50%'><a href='https://" . SITE_SERVER_NAME . $product['DETAIL_PAGE_URL'] . "'>" . $product['NAME'] . "</a></td><td style='width: 50%'>";
		$arProductsString .= "Количество: " . (float)$product['QUANTITY'] . " " . $product['MEASURE_NAME'] . "<br/>";
		$arProductsString .= "Цена: " . ($product['BASE_PRICE'] != $product['PRICE'] ? '<span style="text-decoration: line-through">' . $product['BASE_PRICE'] . ' руб.</span> ' : '');
		$arProductsString .= "<span>" . $product['PRICE'] . " руб.</span><br/>";
		$arProductsString .= "</td></tr>";
	}
}

$html = <<<MESSAGE
	<table width='100%' cellpadding="5" cellspacing="0" border="1" style="width:100%">
		<tbody>
			<tr>
				<td colspan='2' style='text-align:center;font-size: 16px; font-weight: bold;'>Данные пользователя:</td>
			</tr>
			<tr>
				<td colspan='2' style='font-size: 14px;'>{$arPropsString}</td>
			</tr>
			<tr>
				<td colspan='2' style='text-align:center;font-size: 16px; font-weight: bold;'>Заказ:</td>
			</tr>
			{$arProductsString}
			<tr>
				<td colspan='2' style='font-size: 14px;'>Способ доставки: {$deliveryString}</td>
			</tr>
			<tr>
				<td colspan='2' style='font-size: 14px;'>Способ оплаты: {$payString}</td>
			</tr>
			{$comment}
		</tbody>
	</table>
MESSAGE;

$send = Bitrix\Main\Mail\Event::send(array(
    "EVENT_NAME" => "FORM_FEEDBACK_ANESTEZIYA",
    'MESSAGE_ID' => 195,
    "LID" => SITE_ID,
    "C_FIELDS" => array(
        "CONTENT" => $html
    ),
)); 

if($send)
{
	$basket->save();
	echo "Y";
}