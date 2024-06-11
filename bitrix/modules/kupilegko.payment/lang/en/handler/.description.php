<?php
$MESS["ALFABANK_PAYMENT_MODULE_TITLE"] = 'Alfabank - Payment by credit card';
$MESS["ALFABANK_PAYMENT_GROUP_GATE"] = 'Payment Gateway Connection Settings';
$MESS["ALFABANK_PAYMENT_GROUP_HANDLER"] = 'Payment Processor Parameters';
$MESS["ALFABANK_PAYMENT_GROUP_ORDER"] = 'Order Settings';
$MESS["ALFABANK_PAYMENT_GROUP_FFD"] = 'FFD Settings';
$MESS["ALFABANK_PAYMENT_GROUP_OFD"] = "Fiscalization";

$MESS["ALFABANK_PAYMENT_API_LOGIN_NAME"] = 'Login';
$MESS["ALFABANK_PAYMENT_API_LOGIN_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_API_PASSWORD_NAME"] = 'Password';
$MESS["ALFABANK_PAYMENT_API_PASSWORD_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_API_TEST_MODE_NAME"] = 'Test mode';
$MESS["ALFABANK_PAYMENT_API_TEST_MODE_DESCR"] = 'If checked, the plugin will work in test mode. If empty, the standard operation mode will be. ';

$MESS["ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_NAME"] = 'Automatic redirect to the form of payment';
$MESS["ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_DESCR"] = 'If noted, after placing the order, the buyer will be automatically redirected to the payment form page.';
$MESS["ALFABANK_PAYMENT_HANDLER_LOGGING_NAME"] = 'Logging';
$MESS["ALFABANK_PAYMENT_HANDLER_LOGGING_DESCR"] = 'If checked, the plugin will log requests to a file.';
$MESS["ALFABANK_PAYMENT_HANDLER_TWO_STAGE_NAME"] = 'Two Stage Payments';
$MESS["ALFABANK_PAYMENT_HANDLER_TWO_STAGE_DESCR"] = 'If checked, a two-step payment will be made. With an empty value, a one-step payment will be made.';
$MESS["ALFABANK_PAYMENT_HANDLER_SHIPMENT_NAME"] = 'Allow Shipment';
$MESS["ALFABANK_PAYMENT_HANDLER_SHIPMENT_DESCR"] = 'If checked, then after successful payment the order will be automatically shipped.';

$MESS["ALFABANK_PAYMENT_ORDER_NUMBER_NAME"] = 'Unique order identifier in the store';
$MESS["ALFABANK_PAYMENT_ORDER_NUMBER_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_ORDER_AMOUNT_NAME"] = 'Order price';
$MESS["ALFABANK_PAYMENT_ORDER_AMOUNT_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_ORDER_DESCRIPTION_NAME"] = 'Order Description';
$MESS["ALFABANK_PAYMENT_ORDER_DESCRIPTION_DESCR"] = '';


$MESS["ALFABANK_PAYMENT_FFD_VERSION_NAME"] = 'Fiscal Document Format';
$MESS["ALFABANK_PAYMENT_FFD_VERSION_DESCR"] = 'The format of the version is required to be indicated in the personal account of the bank and in the office of the fiscalization service';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_NAME"] = 'Payment type';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_DESCR"] = 'For FFD version 1.05 and higher';
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_1'] = "Full advance payment before the transfer of the subject of calculation";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_2'] = "Partial prepayment until the transfer of the subject of calculation";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_3'] = "Prepaid expense";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_4'] = "Full payment at the time of transfer of the subject of calculation";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_5'] = "Partial payment of the subject of payment at the time of its transfer with subsequent payment on credit";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_6'] = "Transfer of the subject of calculation without its payment at the time of its transfer with subsequent payment on credit";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_7'] = "Payment of the subject of calculation after its transfer with payment on credit";

$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_NAME"] = "Type of paid position";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_DESCR"] = "For FFD version 1.05 and higher";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_1"]  = "Product";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_2"]  = "Excisable goods";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_3"]  = "Job";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_4"]  = "Service";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_5"]  = "Gambling bet";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_6"]  = "Gambling winnings";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_7"]  = "Lottery ticket";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_8"]  = "Winning the lottery";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_9"]  = "Providing REED";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_10"] = "Payment";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_11"] = "Agent's commission";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_12"] = "Composite subject of calculation";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_13"] = "Other subject of calculation";


$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_NAME"] = "The check issues a bank";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_DESCR"] = "If the value is 'Y', then it will form and send a check to the client. The option is paid, for connection, contact the bank's service department. If you use it, you need Shared";

$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_0"] = "Shared";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_1"] = "Simplified, income";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_2"] = "Simplified, income minus expense";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_3"] = "A single tax on imputed income";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_4"] = "Uniform agricultural tax";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_5"] = "Patent taxation system";


$MESS["ALFABANK_PAYMENT_OFD_TAX_SYSTEM_NAME"] = "Taxation system";
$MESS["ALFABANK_PAYMENT_OFD_TAX_SYSTEM_DESCR"] = "";

$MESS["ALFABANK_PAYMENT_RETURN_URL_NAME"] = "Page your customer will be redirected to after a successful payment";
$MESS["ALFABANK_PAYMENT_RETURN_URL_DESCR"] = "Not required. The address must be indicated in full, including the protocol used.";
$MESS["ALFABANK_PAYMENT_FAIL_URL_NAME"] = "Page your customer will be redirected to after an unsuccessful payment";
$MESS["ALFABANK_PAYMENT_FAIL_URL_DESCR"] = "Not required. The address must be indicated in full, including the protocol used.";