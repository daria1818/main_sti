<?php
namespace Eshoplogistic\Delivery\Helpers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use DateTime;
use Eshoplogistic\Delivery\Api\Tariffs;
use Eshoplogistic\Delivery\Config;

class ExportFileds {

    public function sendExportFields($name){
        $result = array();
        if ( $name === 'boxberry' ) {
            $result = array(
                'order' => array(
                    'barcode' => '',
                    'type' => '',
                    'packing_type' => '',
                    'issue'        => ''
                )
            );
        }
        if ( $name === 'sdek' ) {
            $result = array(
                'order'    => array(
                    'type' => ''
                ),
                'delivery' => array(
                    'tariff' => '',
                )
            );
        }
        if ( $name === 'delline' ) {
            $result = array(
                'sender'   => array(
                    'requester'    => '',
                    'counterparty' => '',
                ),
                'order'    => array(
                    'accept' => '',
                ),
                'delivery' => array(
                    'mode' => '',
                    'produce_date' => '',
                )
            );
        }

        return $result;
    }


    public function exportFields($name , $shippingMethods = array()){
        $result = array();
        if($name === 'boxberry'){
            $result = array(
                'order' => array(
                    'barcode||text' => '',
                    'type||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_BOXBERRY_1"),
                    'packing_type||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_BOXBERRY_2"),
                    'issue||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_BOXBERRY_3"),
                )
            );
        }
        if($name === 'sdek') {
            $tariffsApi = new Tariffs();
            $tariffs = $tariffsApi->sendExport($name);
            $tariffs = $tariffs['data']??'';
            if(isset($shippingMethods['terminal']['tariff'])){
                $selectedTariffCode = $shippingMethods['terminal']['tariff']['code'];
                if(isset($tariffs[$selectedTariffCode])) {
                    $value[$selectedTariffCode] = $tariffs[$selectedTariffCode];
                    unset($tariffs[$selectedTariffCode]);
                    $tariffs = $value + $tariffs;
                }
            }
            $result = array(
                'order' => array(
                    'type||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_SDEK_1"),
                ),
                'delivery' => array(
                    'tariff||select' => $tariffs,
                )
            );
        }
        if ( $name === 'delline' ) {
            $date = new DateTime();
            $date->modify('+1 day');
            $produce_date = $date->format('Y-m-d');

            $result = array(
                'sender'   => array(
                    'requester||text'    => (Option::get(Config::MODULE_ID, 'sender-uid-delline'))??'',
                    'counterparty||text' => (Option::get(Config::MODULE_ID, 'sender-counter-delline'))??'',
                ),
                'order'    => array(
                    'accept||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_DELLINE_1"),
                ),
                'delivery' => array(
                    'mode||select' => Loc::GetMessage("ESHOP_LOGISTIC_HELPERS_EXPORT_DELLINE_2"),
                    'produce_date||date' => $produce_date,
                )
            );
        }


        return $result;
    }

}
