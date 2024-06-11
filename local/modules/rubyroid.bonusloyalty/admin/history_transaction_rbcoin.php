<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Loader;
use \Rubyroid\Loyality\RBTransactionsTable;
use \Rubyroid\Loyality\RBRequests;
$module_id = "rubyroid.bonusloyalty";
Loader::includeModule($module_id);


$sTableID = "rb_history_transactions_more"; // ид таблицы
$lAdmin = new CAdminList($sTableID);

$lAdmin->AddHeaders([
    ['id' => 'ID',    'content' => 'ID', 'default' => true],
    ['id' => 'EVENT_DATE', 'content' => 'Дата события', 'default' => true],
    ['id' => 'ORDER_ID', 'content' => 'ID Заказа', 'default' => true],
    ['id' => 'TYPE_OPERATION', 'content' => 'Тип транзакции', 'default' => true],
    ['id' => 'TYPE_EVENT', 'content' => 'Событие', 'default' => true],
    ['id' => 'COIN', 'content' => 'STICoin', 'default' => true],
    ['id' => 'BALANCE', 'content' => 'Баланс до', 'default' => true],
    ['id' => 'AFTER_BALANCE', 'content' => 'Баланс после', 'default' => true],
    ['id' => 'USER_ID', 'content' => 'ID Пользователя', 'default' => true]
]);

$rsData = RBTransactionsTable::getList(['filter' => [], 'select' => ['ID', 'ORDER_ID', 'TYPE_OPERATION', 'TYPE_EVENT', 'COIN', 'BALANCE', 'AFTER_BALANCE', 'USER_ID', 'EVENT_DATE']]);

$data = new CAdminResult($rsData, $sTableID);
while ($element = $data->NavNext(true, "f_")) {
	$lAdmin->AddRow($element['id'], $element);
}

$APPLICATION->SetTitle("История транзакций");


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>