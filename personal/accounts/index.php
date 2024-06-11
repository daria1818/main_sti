<?php /** @noinspection PhpUndefinedMethodInspection */
define('PERSONAL_DIR',true,true);
/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';

global $USER,$APPLICATION;

$APPLICATION->SetTitle('Главная');
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
\Bitrix\Main\Loader::IncludeModule('sale');

$arParams['SALE_PRESON_TYPE']['GETLIST'] = array(
    'BY'  => 'SORT',
    'ORDER'  => 'ASC',
    'FILTER'  => array('ACTIVE'=>'Y'),
);
$arParams['SALE_ORDER_PROPERTIES']['GETLIST'] = array(
    'ORDER'  => array('SORT'=>'ASC'),
    'FILTER'  => array('ACTIVE'=>'Y','UTIL'=>'N','USER_PROPS'=>'Y'),
);
$arParams['SALE_USER_PROFILES']['GETLIST'] = array(
    'ORDER'  => array('DATE_INSERT'=>'ASC'),
    'FILTER'  => array('USER_ID'=>$USER->GetId()),
);
/** @noinspection PhpUndefinedClassInspection */
$nsItem = \CSalePersonType::GetList(
    $arParams['SALE_PRESON_TYPE']['GETLIST']['BY'],
    $arParams['SALE_PRESON_TYPE']['GETLIST']['ORDER'],
    $arParams['SALE_PRESON_TYPE']['GETLIST']['FILTER']
);

while($arItem = $nsItem->Fetch()):
    $arResult['PERSON_TYPE'][$arItem['ID']] = $arItem;
endwhile;

/** @noinspection PhpUndefinedClassInspection */
$nsItem = \CSaleOrderProps::GetList(
    $arParams['SALE_ORDER_PROPERTIES']['GETLIST']['ORDER'],
    $arParams['SALE_ORDER_PROPERTIES']['GETLIST']['FILTER']
);

$arGroups=array();

while($arItem = $nsItem->Fetch()):
    $arGroups[$arItem['SORT']]=!isset($arGroups[$arItem['SORT']])? 1 :(++$arGroups[$arItem['SORT']]);
    $arResult['ORDER_PROPS'][$arItem['PERSON_TYPE_ID']][$arItem['ID']] = $arItem;
    $arResult['ORDER_PROPS_CODE'][$arItem['PERSON_TYPE_ID']][$arItem['CODE']] = $arItem['ID'];
    $arResult['~ORDER_PROPS'][$arItem['CODE']][$arItem['ID']] = $arItem['ID'];
endwhile;

/** @noinspection PhpUndefinedClassInspection */
$nsItem = \CSaleOrderUserProps::GetList(
    $arParams['SALE_USER_PROFILES']['GETLIST']['ORDER'],
    $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER']
);

while($arItem = $nsItem->Fetch()):
    $arResult['PROFILES'][] = $arItem;
endwhile;

if(!empty($_REQUEST['PERSON_TYPE_ID'])):
    function SetRecParametrs(&$a)
    {
        foreach($a as &$value):
            if(!is_array($value)):
                /** @noinspection PhpUndefinedFunctionInspection */
                $value = htmlspecialcharsbx($value);
            else:
                SetRecParametrs($value);
            endif;
        endforeach;
    }

    $keyProfileName = current(array_intersect($arResult['~ORDER_PROPS']['FULL_NAME'],array_keys($_REQUEST['PROPS'])));

    $arFields = array(
        'USER_ID'           => (string) $USER->GetId(),
        'USER_PROFILE_ID'   => $_REQUEST['USER_PROFILE_ID'],
        'PROFILE_NAME'      => $_REQUEST['PROPS'][$keyProfileName],

    );
    $arFields['SOAP'] = xGuard\Main\Soap\Params::prepareIdToCode(
        array(
            'ID'    => $_REQUEST['PROPS'],
            'CODE'    => $arResult['ORDER_PROPS_CODE'][$_REQUEST['PERSON_TYPE_ID']],
        )
    );
    $arFields['SOAP']['PERSON_TYPE_ID'] = constant('PERSON_TYPE_'.$_REQUEST['PERSON_TYPE_ID']);
    $arFields['SOAP']['USER_EMAIL'] = $USER->GetEmail();

    try
    {
        $soap = xGuard\Main\Soap\Params::getSoapInstance();

        xGuard\Main\Soap\Params::checkFields(
            array(
                'VALUE' => &$arFields['SOAP'],
                'SCHEME'=> &$arResult['ORDER_PROPS'][$_REQUEST['PERSON_TYPE_ID']],
            )
        );
        $arFields['SOAP'] = xGuard\Main\Soap\Params::prepare(
            array(
                'VALUE' => $arFields['SOAP'],
                'PARAMS'    => array(
                    'NOT_EMPTY' => true,
                    'ACTION'    => 'CreateClient',
                ),
            )
        );
        $arFields['XML'] = $soap->CreateClient(array('pStruct'=>$arFields['SOAP']));
        debugfile(array($_REQUEST,$arFields),'user.log');

        $arErrors = [];

        /** @noinspection PhpUndefinedClassInspection */
        CSaleOrderUserProps::DoSaveUserProfile($arFields['USER_ID'], $arFields['USER_PROFILE_ID'], $arFields['PROFILE_NAME'], $_REQUEST['PERSON_TYPE_ID'], $_REQUEST['PROPS'], $arErrors);

        if($arFields['XML']->return->Status&&!isset($arFields['SOAP']['GUID'])):
            if(empty($arFields['USER_PROFILE_ID'])):
                /** @noinspection PhpUndefinedClassInspection */
                $arFields['USER_PROFILE']   = \CSaleOrderUserProps::GetList(
                    array('ID'=>'DESC'),
                    array('USER_ID'=>$arFields['USER_ID'])
                )->Fetch();
                $arFields['USER_PROFILE_ID'] = $arFields['USER_PROFILE']['ID'];
            endif;

            $id = $arResult['ORDER_PROPS_CODE'][$_REQUEST['PERSON_TYPE_ID']]['GUID'];
            $arOrderProperty = $arResult['ORDER_PROPS'][$_REQUEST['PERSON_TYPE_ID']][$id];

            $arFields['USER_PROPS_GUID'] = array(
                'USER_PROPS_ID'  => $arFields['USER_PROFILE_ID'],
                'ORDER_PROPS_ID' => $id,
                'NAME'           => $arOrderProperty['NAME'],
                'VALUE'          => $arFields['XML']->return->GUID,
            );

            /** @noinspection PhpUndefinedClassInspection */
            CSaleOrderUserPropsValue::Add($arFields['USER_PROPS_GUID']);
        endif;

        /** @noinspection PhpUndefinedFunctionInspection */
        LocalRedirect($APPLICATION->GetCurPageParam('',array_keys($_GET),false));
    }
    catch(\xGuard\Main\Exception $e)
    {
        $arResult['ERRORS'][__LINE__] = $e->getMessage();
    }

endif;
?>
<div class="b-user_tabs-content active">
    <h3 class="b-user_z">Контрагенты</h3>
    <?if(!empty($arResult['ERRORS'])):?>
        <div class="page4xx black">
            <div class="errortext">Во время сохранения данных произошли следующие ошибки:</div>
            <?foreach($arResult['ERRORS'] as $arError):
                /** @noinspection PhpUndefinedFunctionInspection */
                ShowMessage($arError);
            endforeach;?>
        </div>
    <?endif;?>
    <div id="createAccountList" class="addresses collapse in" data-active="in">
        <div class="form-group">
            <a class="btn btn-primary btn-sm data-ajax collapse in" data-active="in" href="javascript:void(0);" data-ajax="" data-event="click" data-trigger="ShowAccount" data-slave="#createAccount,#createAccountList" data-name="accountData">Создать контрагента</a>
        </div>
        <?foreach($arResult['PROFILES'] as $arProfiles):?>
            <div class="bloc_adresses row" id="profile<?=$arProfiles['ID'];?>">
                <div class="col-xs-12 col-sm-6 address">
                    <ul class="last_item item box">
                        <li>
                            <h3 class="page-subheading"><?=$arProfiles['NAME'];?></h3>
                        </li>
                        <li>
                            <span><?=$arResult['PERSON_TYPE'][$arProfiles['PERSON_TYPE_ID']]['NAME'];?></span>
                        </li>
                        <li class="address_update">
                            <a class="btn btn-primary btn-sm data-ajax"
                                href="javascript:void(0);"
                                data-ajax="/ajax/v1/profile/<?=$arProfiles['ID'];?>/get.json?type=2"
                                data-event="click"
                                data-trigger="GetAccount"
                                data-slave="#agent_new_type,#createAccount,#createAccountList"
                                data-type="<?=$arProfiles['PERSON_TYPE_ID'];?>"
                                data-name="accountData"
                                title="Обновить"><span>Обновить<i class="fa fa-refresh right"></i></span></a>
                            <a class="btn btn-danger btn-sm data-ajax"
                                href="javascript:void(0);"
                                data-ajax="/ajax/v1/profile/<?=$arProfiles['ID'];?>/remove.json"
                                data-event="click"
                                data-trigger="RemoveAccount"
                                data-slave="#profile<?=$arProfiles['ID'];?>"
                                data-type="<?=$arProfiles['PERSON_TYPE_ID'];?>"
                                data-name="accountData"
                                title="Удалить"><span>Удалить <i class="fa fa-times right"></i></span> </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?endforeach;?>
    </div>
    <div id="createAccount" data-trigger="Show" data-slave="#agent_new_type_label" data-process="toggleAccount" class="collapse"  data-name="accountData" >
        <div id="agent_new_type_label" data-trigger="Show" data-slave="#agent_new_type" class="form-group col-sm-12 col-md-12 col-lg-12" >
            <label for="agent_new_type" class="agent_new_type_label">Выберите тип контрагента:</label>
            <select id="agent_new_type" class="form-control data-ajax" data-ajax="/ajax/?do=Get&mode=AccountProperties&id=" data-event="change" data-trigger="GetAccountProperties" data-slave="#accountList" data-auto-start data-append="#agent_new_type_label" data-name="accountData">
                <?foreach($arResult['PERSON_TYPE'] as $arItem):?>
                    <option value="<?=$arItem['ID'];?>"><?=$arItem['NAME'];?></option>
                <?endforeach;?>
            </select>
        </div>
        <form id="formCreateAccount" action="<?=$APPLICATION->GetCurPage();?>" method="post" class="std row">
            <?php
            $arExclusion = array();
            foreach($arResult['ORDER_PROPS'] as $keyGroup=>$arGroup):?>
                <div id="accountList<?=$keyGroup;?>" class="b-user-data collapse" data-active="in">
                    <br>
                    <input type="hidden" name="LID" value="<?= /** @noinspection PhpUndefinedConstantInspection */
                    SITE_ID;?>" disabled="disabled" />
                    <input type="hidden" name="PERSON_TYPE_ID" value="<?=$keyGroup;?>" disabled="disabled" />
                    <input type="hidden" name="PAYED" value="N" disabled="disabled" />
                    <input type="hidden" name="CANCELED" value="Y" disabled="disabled" />
                    <input type="hidden" name="STATUS_ID" value="N" disabled="disabled" />
                    <input type="hidden" name="PRICE" value="0" disabled="disabled" />
                    <input type="hidden" name="CURRENCY" value="RUB" disabled="disabled" />
                    <input type="hidden" name="USER_ID" value="<?= (int)$USER->GetID();?>" disabled="disabled" />
                    <input type="hidden" name="USER_DESCRIPTION" value="CreateAccount" disabled="disabled" />
                    <input type="hidden" name="USER_PROFILE_ID" value="" disabled="disabled"  class="USER_PROFILE_ID" data-content="value"  data-clear="true" />
                    <input type="hidden" name="PROPS[<?=$arResult['ORDER_PROPS_CODE'][$keyGroup]['GUID'];?>]" value="" disabled="disabled"  class="GUID" data-content="value" data-clear="true" />
                    <?$arExclusion['GUID']=true;?>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6">
                    <?php
                    $row            = count($arGroup)-count($arExclusion);
                    $column         = round($row/2);
                    $i              = $column;
                    $currentGroup   = false;

                    foreach($arGroup as $arItem):
                        if(isset($arExclusion[$arItem['CODE']])):
                            continue;
                        endif;

                        if($i<1):
                            echo '</div><div class="form-group col-sm-12 col-md-6 col-lg-6">';
                            $i=$column;
                        endif;

                        if($arGroups[$arItem['SORT']]>1):
                            $i              = !$currentGroup?($i+1):$i;
                            $currentGroup   = true;

                            echo '<div class="form-group">';
                        else:
                            $currentGroup=false;
                        endif;

                        $maskTag = !empty($arItem['DESCRIPTION']) ? (' data-mask="'.$arItem['DESCRIPTION'].'"'):'';
                        $maskClass = !empty($maskTag) ? ' data-ajax':'';
                        $requiredClass = $arItem['REQUIED']==='Y' ? ' is_required validate':'';
                        $requiredTag = $arItem['REQUIED']==='Y' ? ' required="required"':'';
                        ?>
                            <div>
                                <label for="<?=$arItem['CODE'];?><?=$arItem['ID'];?>"<?php echo $arItem['REQUIED']==='Y'?' class="required"':''; ?> data-sort="<?= $arItem['SORT']; ?>"><?=$arItem['NAME'];?></label>
                                <input id="<?=$arItem['CODE'];?><?=$arItem['ID'];?>" class="form-control<?php echo $requiredClass,$maskClass; ?> <?=$arItem['CODE'];?>" name="PROPS[<?=$arItem['ID'];?>]" type="text" value=""<?php echo $requiredTag,$maskTag; ?> disabled="disabled" data-content="value"  data-clear="true">
                            </div>
                        <?php
                        if($arGroups[$arItem['SORT']]>1):
                            echo '</div>';
                        endif;
                        $i--;
                    endforeach;?>
                    </div>
                </div>
            <?endforeach;?>
            <div class="btn-block">
                <div class="form-group col-sm-12 col-md-12 col-lg-12"><br>
                    <input type="submit" class="btn btn-primary btn-sm btn-block" name="submit" value="Сохранить">
                </div>
                <div class="form-group col-sm-12 col-md-12 col-lg-"><br>
                    <a class="btn btn-default btn-sm data-ajax block-align-right btn-block" href="javascript:void(0);" data-ajax="" data-event="click" data-trigger="Show" data-slave="#createAccount,#createAccountList">Отменить</a>
                </div>
            </div>
        </form>
    </div>

</div>
<?php

$obCDN = \xGuard\Main\Seo\CDN::GetInstance();
$jsUrl = $obCDN->GetServerName(array('TYPE'=>'js'));
$timestamp=mktime(date('H'),0,0,date('m'),date('n'),date('Y'));
?>
<script type="text/javascript">
    window.XGuardLoader=window.XGuardLoader||[];
    window.XGuardLoader.push(function()
    {
        try
        {
            xGuard.timestamp    = xGuard.timestamp||(function(a,b){return a=new Date(),b=new Date(a.getFullYear(),a.getMonth(),a.getDate(),a.getHours()-1,0,0,0),b.getTime();})();
            xGuard.R({name:'accounts',url:'<?=$jsUrl;?><?=/** @noinspection PhpUndefinedConstantInspection */SITE_TEMPLATE_PATH;?>/assets/js/0.2/main/accounts.js?'+xGuard.timestamp});
        }
        catch(e)
        {
            console.log(e.stack);
        }
    });
</script>
<?php

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
?>