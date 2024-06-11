<?
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\Dellin\AjaxService;

/** @var CMain $APPLICATION */
Loc::loadMessages(__FILE__);
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//var_dump($arParams['dellin']['currentDeliveryTimeStartField'] );
//var_dump($arParams);

?>


<script type="text/javascript">

    BX.namespace('BX.Dellin');

    BX.Dellin = {
        ajaxpage: "<?=$componentPath.'/ajax.php'?>",
        terminalList: <?=json_encode($arParams['dellin']['terminalList'], JSON_UNESCAPED_UNICODE) ?>,
        terminalsMethod: <?=json_encode($arParams['dellin']['terminalsMethod'], JSON_UNESCAPED_UNICODE) ?>,
        fieldTerminalID: <?=$arParams['dellin']['currentTerminalField'] ?>,
        fieldDeliveryTimeStart: <?=$arParams['dellin']['currentDeliveryTimeStartField']?>,
        fieldDeliveryTimeEnd: <?=$arParams['dellin']['currentDeliveryTimeEndField']?>,
        selectedDeliveryMethod: <?=$arParams['USER_RESULT']['DELIVERY_ID']?>,
        isTerminalMethodSelected: function(){
            if(!BX.Dellin.terminalsMethod) return false ;

            let res = false;

            BX.Dellin.terminalsMethod.map(function (method) {

                if(BX.Dellin.getDeliveryData().ID == method){
                    res = true;
                }

            });

            return res;

        },
        setIdFieldTerminal: function(result){

            if(!result.order) return ;
            let fieldId = '';

            result.order.ORDER_PROP.properties.map(function (field) {

                if(field.CODE == "TERMINAL_ID"){
                    fieldId = field.ID;
                }

            });

            return fieldId;
        },
        getBlockPickup: function (){
            return document.querySelector('#bx-soa-pickup');
        },
        init: function () {

            console.log("Dellin init");

            let terminalID = BX.Dellin.getFieldElementByID('input', BX.Dellin.fieldTerminalID);

            if(!terminalID){
                console.warn('DELLIN WARNING: Field terminalID is null!');
                return ;
            }

            terminalID.style = "display:none;";
            console.log('Hide input terminal');

            let buildTerminal = BX.Dellin.getTerminalsList();
            terminalID.after(buildTerminal);


        },
        styledFieldsSelect: function(){

            let selectClassForCheckout = 'form-control bx-soa-customer-select';

            let deliveryTimeStart = BX.Dellin.getFieldElementByID('select', BX.Dellin.fieldDeliveryTimeStart);

            if(!deliveryTimeStart){
                console.warn('DELLIN WARNING: Field deliveryTimeStart is null!');
                return ;
            }

            deliveryTimeStart.className = selectClassForCheckout;

            let deliveryTimeEnd =  BX.Dellin.getFieldElementByID('select', BX.Dellin.fieldDeliveryTimeEnd);

            if(!deliveryTimeEnd){
                console.warn('DELLIN WARNING: Field deliveryTimeEnd is null!');
                return ;
            }

            deliveryTimeEnd.className = selectClassForCheckout;

        },
        getTerminalBlock: function(){
            return document.querySelector('div[data-property-id-row="'+BX.Dellin.fieldTerminalID+'"]');
        },
        hideTerminalBlock: function(){

            let terminalBlock = BX.Dellin.getTerminalBlock();

            if(!terminalBlock){
                console.warn('Field terminal is null!');
                return ;
            }

            terminalBlock.style = 'display:none';

        },
        showTerminalBlock: function(){
            let terminalBlock = BX.Dellin.getTerminalBlock();
            if(!terminalBlock){
                console.warn('DELLIN WARNING: Properties with code "TERMINAL_ID" is null');
                return ;
            }
            terminalBlock.style = '';
        },
        getFieldElementByID: function(typeField, fieldID){
            return  document.querySelector(typeField+'[name="ORDER_PROP_'+fieldID+'"]');
        },
        removeSelectBlock: function(){
            let terminals = document.querySelector('#terminals');
                if(!terminals){
                    return ;
                }
                terminals.remove();
        },
        buildBlockDataWithTerminals: function(parent, result){

            let label = document.createElement('label');
            label.id = 'label_terminals';
            let strong = document.createElement('strong');
            strong.innerHTML = BX.message("DELLINDEV_TERMINAL_LIST");
            label.appendChild(strong);

            let br = document.createElement('br');
            br.id = 'container-br';

            parent.appendChild(label);
            parent.appendChild(br);

            BX.Dellin.getTerminalsList(result, parent);

        },
        builderOptionList:function(fieldterm, field, id, name, type){
            let option = document.createElement('option');
            option.innerHTML = name;
            option.value = id;
            option.id = type+'-added';
            option.selected = (field.dataset.value == id);
            field.appendChild(option);

            // option.addEventListener('click', function () {
            //     console.log(BX.message("DELLINDEV_SELECTED_TERMINAL"), option.value);
            //     BX.Dellin.setValueInInput(fieldterm, option.value);
            // })

        },
        setValueInInput: function(prop_id, value){
            let propElement = document.querySelector('[name="ORDER_PROP_'+prop_id+'"]');
            propElement.style = 'display:none';
            propElement.readonly = true;
            propElement.value = value;
        },
        getTerminalsList: function () {

            if(!BX.Dellin.terminalList){
                return ;
            }

            BX.Dellin.removeSelectBlock();

            let select = document.createElement('select');
            select.id = 'terminals';
            select.className = 'form-control bx-soa-customer-select';
            let prop_id = BX.Dellin.fieldTerminalID;
            let firstValue = BX.Dellin.terminalList[0].terminal_id;
            BX.Dellin.setValueInInput(prop_id, firstValue);
            if(!BX.Dellin.terminalList){
                BX.Dellin.hideTerminalBlock();
            }

            BX.Dellin.terminalList.map(function (terminal) {
                BX.Dellin.builderOptionList(prop_id, select, terminal.terminal_id, terminal.address, 'terminal');

            });


            select.addEventListener('change', function () {

                BX.Dellin.setValueInInput(prop_id, select.value);
                console.log(BX.message("DELLINDEV_SELECTED_TERMINAL"), select.value);

            });


            return select;
        },
        getDeliveryData: function () {
            return BX.Sale.OrderAjaxComponent.getSelectedDelivery()
        },
        getFormData: function () {
            return BX.Sale.OrderAjaxComponent.getAllFormData();
        },

        ajaxSend: function (id) {

            let postData = {
                sessid: BX.bitrix_sessid(),
                action: 'terminal_data',
                ajax: 'Y',
                delivery_id: id,
                person_type_id: BX.Dellin.getFormData().PERSON_TYPE,
            };

            BX.ajax({
                timeout:    300,
                method:     'POST',
                dataType:   'json',
                url:        window.location.pathname,
                data:       postData,

                onsuccess: function(result)
                {

                    if(result.RESULT == 'OK'){
                        BX.Dellin.init(result);
                    }

                },

                onfailure: function(status)
                {
                    console.log("onfailture", status);
                }
            });
        },
        changePropsID: function (result) {
            let props = result.order.ORDER_PROP.properties;
            props.map((prop)=> {

                if(prop.CODE == 'DELLIN_DELIVERYTIME_START'){
                    BX.Dellin.fieldDeliveryTimeStart = prop.ID;
                    console.log(BX.Dellin.fieldDeliveryTimeStart);
                    return;
                }

                if(prop.CODE == 'DELLIN_DELIVERYTIME_END'){
                    BX.Dellin.fieldDeliveryTimeEnd = prop.ID;
                    console.log(BX.Dellin.fieldDeliveryTimeEnd);
                    return;
                }

                if(prop.CODE == 'TERMINAL_ID'){
                    BX.Dellin.fieldTerminalID = prop.ID;
                    console.log(BX.Dellin.fieldTerminalID);
                    return;
                }

            })

        }
    }



    BX.ready(function () {

        BX.message({
            "DELLINDEV_TERMINAL_LIST": "<?=Loc::getMessage("DELLINDEV_TERMINAL_LIST")?>",
            "DELLINDEV_SELECTED_TERMINAL": "<?=Loc::getMessage("DELLINDEV_SELECTED_TERMINAL")?>"
        });


        console.log('Dellin ready');


        if(BX.Dellin.isTerminalMethodSelected()){

            BX.Dellin.showTerminalBlock();
            BX.Dellin.init();
            BX.Dellin.styledFieldsSelect();

        } else {

            BX.Dellin.styledFieldsSelect();
            BX.Dellin.hideTerminalBlock();

        }


        BX.addCustomEvent('onAjaxSuccess', function(result){

            if(!result) return;
            if(!result.dellin) return;
            console.log(result.dellin);
            console.log(result);

            BX.Dellin.changePropsID(result);

            BX.Dellin.terminalList = result.dellin.terminals;
            BX.Dellin.terminalsMethod = result.dellin.terminals_method_id;

            //���������, ������ �� � ��� ����� �������� "�� ���������"
            if(BX.Dellin.isTerminalMethodSelected()){

                BX.Dellin.showTerminalBlock();
                BX.Dellin.init();
                BX.Dellin.styledFieldsSelect();


            } else {

                console.log('Dellin init');
                BX.Dellin.styledFieldsSelect();
                BX.Dellin.hideTerminalBlock();

            }




        });


    });





 
</script>