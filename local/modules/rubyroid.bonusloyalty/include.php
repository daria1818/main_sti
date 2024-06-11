<?
Bitrix\Main\Loader::registerAutoloadClasses(
	"rubyroid.bonusloyalty",
	array(
	   "Rubyroid\\Loyality\\RBprogramm" => "classes/general/RBprogramm.php",
	   "Rubyroid\\Loyality\\RBRequests" => "classes/general/RBRequests.php",
	   "Rubyroid\\Loyality\\RBLogger" => "classes/general/RBLogger.php",
	   "Rubyroid\\Loyality\\RBTransactionsTable" => "classes/general/RBTransactionsTable.php",
	   "Rubyroid\\Loyality\\RBTransactions" => "classes/general/RBTransactions.php",
	   "Rubyroid\\Loyality\\Admin\\Menu" => "admin/menu.php",
	)
);
require_once(__DIR__ .'/classes/general/RBprogramm.php'); 

$eventManager = \Bitrix\Main\EventManager::getInstance();

$ar_events[] = array(
	"NAME" => "OnSaleComponentOrderOneStepComplete",
	"FUNCTION" => "OnSaleComponentOrderOneStepCompleteHandler"
);
$ar_events[] = array(
	"NAME" => "OnSaleComponentOrderResultPrepared",
	"FUNCTION" => "OnSaleComponentOrderResultPreparedHandler"
);
$ar_events[] = array(
	"NAME" => "OnSaleComponentOrderOneStepPaySystem",
	"FUNCTION" => "OnSaleComponentOrderOneStepPaySystemHandler"
);

foreach ($ar_events as $event) {
	$eventManager->registerEventHandler('sale', $event['NAME'], "rubyroid.bonusloyalty", '\\Rubyroid\\Loyality\\RBprogramm', $event['FUNCTION']);
}