<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);


$data = array(
	'NAME' => Loc::getMessage("ALFABANK_PAYMENT_MODULE_TITLE"),
	'SORT' => 100,
	'CODES' => array(
		"ALFABANK_GATE_LOGIN" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_API_LOGIN_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_API_LOGIN_DESCR"),
			'SORT' => 100,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_GATE"),
		),
		"ALFABANK_GATE_PASSWORD" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_API_PASSWORD_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_API_PASSWORD_DESCR"),
			'SORT' => 120,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_GATE"),
		),
		"ALFABANK_GATE_TEST_MODE" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_API_TEST_MODE_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_API_TEST_MODE_DESCR"),
			'SORT' => 130,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_GATE"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_HANDLER_TWO_STAGE" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_TWO_STAGE_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_TWO_STAGE_DESCR"),
			'SORT' => 140,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_GATE"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_HANDLER_AUTO_REDIRECT" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_DESCR"),
			'SORT' => 200,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_HANDLER"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_HANDLER_LOGGING" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_LOGGING_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_LOGGING_DESCR"),
			'SORT' => 250,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_HANDLER"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		
		"ALFABANK_HANDLER_SHIPMENT" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_SHIPMENT_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_HANDLER_SHIPMENT_DESCR"),
			'SORT' => 220,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_HANDLER"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_RETURN_URL" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_RETURN_URL_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_RETURN_URL_DESCR"),
			'SORT' => 230,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_HANDLER"),
		),
		"ALFABANK_FAIL_URL" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FAIL_URL_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_FAIL_URL_DESCR"),
			'SORT' => 240,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_HANDLER"),
		),		

		"ALFABANK_OFD_RECIEPT" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_OFD_RECIEPT_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_OFD_RECIEPT_DESCR"),
			'SORT' => 520,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_OFD_TAX_SYSTEM" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_OFD_TAX_SYSTEM_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_OFD_TAX_SYSTEM_DESCR"),
			'SORT' => 530,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
	                "0"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_0'),
	                "1"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_1'),
	                "2"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_2'),
	                "3"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_3'),
	                "4"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_4'),
	                "5"  =>  GetMessage('ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_5'),
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "1",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_FFD_VERSION" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FFD_VERSION_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_FFD_VERSION_DESCR"),
			'SORT' => 400,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'1.05' => '1.05',
					'1.2' => '1.2',
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "1.05",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_FFD_PAYMENT_METHOD" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_NAME"),
			"DESCRIPTION" => '',
			'SORT' => 540,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
	                "1" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_1'),
	                "2" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_2'),
	                "3" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_3'),
	                "4" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_4'),
	                "5" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_5'),
	                "6" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_6'),
	                "7" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_7'),
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "1",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_FFD_PAYMENT_OBJECT" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_NAME"),
			"DESCRIPTION" => '',
			'SORT' => 550,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
	                "1"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_1'),
	                "2"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_2'),
	                "3"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_3'),
	                "4"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_4'),
	                "5"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_5'),
	                "6"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_6'),
	                "7"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_7'),
	                "8"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_8'),
	                "9"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_9'),
	                "10" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_10'),
	                "11" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_11'),
	                "12" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_12'),
	                "13" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_13'),
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "1",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_FFD_PAYMENT_OBJECT_DELIVERY" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_DELIVERY_NAME"),
			"DESCRIPTION" => '',
			'SORT' => 561,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
	                "1"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_1'),
	                "2"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_2'),
	                "3"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_3'),
	                "4"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_4'),
	                "5"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_5'),
	                "6"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_6'),
	                "7"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_7'),
	                "8"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_8'),
	                "9"  =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_9'),
	                "10" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_10'),
	                "11" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_11'),
	                "12" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_12'),
	                "13" =>  GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_13'),
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "4",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_FFD_PAYMENT_METHOD_DELIVERY" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_FFD_PAYMENT_DELIVERY_METHOD_NAME"),
			"DESCRIPTION" => '',
			'SORT' => 572,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_OFD"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
	                "1" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_1'),
	                "2" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_2'),
	                "3" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_3'),
	                "4" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_4'),
	                "5" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_5'),
	                "6" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_6'),
	                "7" => GetMessage('ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_7'),
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "1",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"ALFABANK_ORDER_NUMBER" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_NUMBER_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_NUMBER_DESCR"),
			'SORT' => 650,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"ALFABANK_ORDER_AMOUNT" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_AMOUNT_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_AMOUNT_DESCR"),
			'SORT' => 660,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"ALFABANK_ORDER_DESCRIPTION" => array(
			"NAME" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_DESCRIPTION_NAME"),
			"DESCRIPTION" => Loc::getMessage("ALFABANK_PAYMENT_ORDER_DESCRIPTION_DESCR"),
			'SORT' => 670,
			'GROUP' => Loc::getMessage("ALFABANK_PAYMENT_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'USER_DESCRIPTION'
			)
		),

	)
);