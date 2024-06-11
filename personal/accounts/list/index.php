<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle('Главная');

\Bitrix\Main\Loader::IncludeModule('sale');


// !! обезXSSывание всего - для сканера безопасности !!
$_REQUEST['accounts'] = htmlspecialcharsbx($_REQUEST['accounts']);
$_REQUEST['by'] = htmlspecialcharsbx($_REQUEST['by']);
$_REQUEST['order'] = htmlspecialcharsbx($_REQUEST['order']);
$_REQUEST['FILTER_ID'] = htmlspecialcharsbx($_REQUEST['FILTER_ID']);
$_REQUEST['FILTER_NAME'] = htmlspecialcharsbx($_REQUEST['FILTER_NAME']);
$_REQUEST['FILTER_USER_NAME'] = htmlspecialcharsbx($_REQUEST['FILTER_USER_NAME']);
$_REQUEST['FILTER_EMAIL'] = htmlspecialcharsbx($_REQUEST['FILTER_EMAIL']);
$_REQUEST['FILTER_DATE_REGISTER'] = htmlspecialcharsbx($_REQUEST['FILTER_DATE_REGISTER']);


$_REQUEST['accounts'] = !isset($_REQUEST['accounts'])?1:(+htmlspecialcharsbx($_REQUEST['accounts']));
$_REQUEST['by'] = !isset($_REQUEST['by'])?'ID':htmlspecialcharsbx($_REQUEST['by']);
$_REQUEST['order'] = !isset($_REQUEST['order'])?'asc':htmlspecialcharsbx($_REQUEST['order']);

$_REQUEST['PAGE_1'] = isset($_REQUEST['PAGE_1'])?$_REQUEST['PAGE_1']:1;

$user = new \CUser;

$arParams['SALE_PRESON_TYPE']['GETLIST'] = array(
    'BY'  => 'sort',
    'ORDER'  => 'asc',
    'FILTER'  => array('ACTIVE'=>'Y'),
);

$arParams['USER']['GETLIST'] = array(
    'ORDER'  => array(
        $_REQUEST['by'],
        $_REQUEST['order'],
    ),
    'FILTER'  => array(),
);
$arParams['SALE_USER_PROFILES']['GETLIST'] = array(
    'ORDER'     => array($_REQUEST['by']=>$_REQUEST['order']),
    'FILTER'    => array(),
    'LIMIT'     => false,
    'GROUP'     => false,
);

if(!$_REQUEST['accounts']):
    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_EMAIL'])):
        $arParams['USER']['GETLIST']['FILTER']['%EMAIL'] = htmlspecialcharsbx($_REQUEST['FILTER_EMAIL']);
    endif;

    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_ID'])):
        $arParams['USER']['GETLIST']['FILTER']['ID'] = htmlspecialcharsbx($_REQUEST['FILTER_ID']);
    endif;

    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_USER_NAME'])):
        $arParams['USER']['GETLIST']['FILTER']['NAME'] = htmlspecialcharsbx($_REQUEST['FILTER_USER_NAME']);
    endif;

    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_DATE_REGISTER'])):
        $arParams['USER']['GETLIST']['FILTER']['DATE_REGISTER'] = htmlspecialcharsbx($_REQUEST['DATE_REGISTER']);
    endif;

    $arParams['USER']['GETLIST']['SELECT']  = array(
        'NAV_PARAMS'    => array(
            "nPageSize"=>+$_REQUEST['PAGE_1'],
            "nTopCount"=>'40',
        ),
    );
else:
    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_PERSON_TYPE_ID'])):
        $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER']['PERSON_TYPE_ID'] = htmlspecialcharsbx($_REQUEST['FILTER_PERSON_TYPE_ID']);
    endif;

    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_NAME'])):
        $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER']['%NAME'] = htmlspecialcharsbx($_REQUEST['FILTER_NAME']);
    endif;

    if(isset($_REQUEST['FILTER'])&&!empty($_REQUEST['FILTER_ID'])):
        $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER']['ID'] = htmlspecialcharsbx($_REQUEST['FILTER_ID']);
    endif;
    $arParams['SALE_USER_PROFILES']['GETLIST']['LIMIT'] = array(
        "nPageSize"=>+$_REQUEST['PAGE_1'],
        "nTopCount"=>'40',
    );
endif;

if(!$_REQUEST['accounts']):
    $nsItem = $user->GetList(
        $arParams['USER']['GETLIST']['ORDER'][0],
        $arParams['USER']['GETLIST']['ORDER'][1],
        $arParams['USER']['GETLIST']['FILTER'],
        $arParams['USER']['GETLIST']['SELECT']
    );

    while($arItem = $nsItem->Fetch()):
        $arResult['USERS'][$arItem['ID']] = $arItem;
        $arResult['INDEX']['USERS'][$arItem['EMAIL']] = $arItem['ID'];
    endwhile;

    $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER']['USER_ID'] = array_keys($arResult['USERS']);

    $nsItem = \CSaleOrderUserProps::GetList(
        $arParams['SALE_USER_PROFILES']['GETLIST']['ORDER'],
        $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER'],
        $arParams['SALE_USER_PROFILES']['GETLIST']['LIMIT']
    );

    while($arItem = $nsItem->Fetch()):
        $arResult['PROFILES'][$arItem['USER_ID']][$arItem['ID']] = $arItem;
        $arResult['INDEX']['PROFILES'][$arItem['ID']] = $arItem['USER_ID'];
    endwhile;
else:
    $nsItem = \CSaleOrderUserProps::GetList(
        $arParams['SALE_USER_PROFILES']['GETLIST']['ORDER'],
        $arParams['SALE_USER_PROFILES']['GETLIST']['FILTER'],
        $arParams['SALE_USER_PROFILES']['GETLIST']['GROUP'],
        $arParams['SALE_USER_PROFILES']['GETLIST']['LIMIT']
    );

    while($arItem = $nsItem->Fetch()):
        $arResult['PROFILES'][$arItem['ID']] = $arItem;
        $arResult['INDEX']['USER_ID'][$arItem['USER_ID']] = $arItem['USER_ID'];
    endwhile;

    $arResult['NAV_STRING'] = $nsItem->GetPageNavStringEx(
        $navComponentObject,
        '',
        '.default'
    );

    $arParams['USER']['GETLIST']['FILTER']['ID'] = implode('|',$arResult['INDEX']['USER_ID']);

    $nsItem = $user->GetList(
        $arParams['USER']['GETLIST']['ORDER'][0],
        $arParams['USER']['GETLIST']['ORDER'][1],
        $arParams['USER']['GETLIST']['FILTER'],
        $arParams['USER']['GETLIST']['SELECT']
    );

    while($arItem = $nsItem->Fetch()):
        $arResult['USERS'][$arItem['ID']] = $arItem;
        $arResult['INDEX']['USERS'][$arItem['EMAIL']] = $arItem['ID'];
    endwhile;
endif;

$nsItem = \CSalePersonType::GetList(
    $arParams['SALE_PRESON_TYPE']['GETLIST']['BY'],
    $arParams['SALE_PRESON_TYPE']['GETLIST']['ORDER'],
    $arParams['SALE_PRESON_TYPE']['GETLIST']['FILTER']
);

while($arItem = $nsItem->Fetch()):
    $arResult['PERSON_TYPE'][$arItem['ID']] = $arItem;
endwhile;

if(!isset($_REQUEST['FILTER'])&&isset($_REQUEST['ID'])&&isset($_REQUEST['USER_ID'])&&isset($_REQUEST['PERSON_TYPE_ID'])):
    if(
        isset($arResult['PROFILES'][$_REQUEST['ID']])
            &&
        $arResult['PROFILES'][$_REQUEST['ID']]['USER_ID']!=$_REQUEST['USER_ID']
            ||
        $arResult['PROFILES'][$_REQUEST['ID']]['PERSON_TYPE_ID']!=$_REQUEST['PERSON_TYPE_ID']
    ):
        \CSaleOrderUserProps::Update($_REQUEST['ID'],array('USER_ID'=>$_REQUEST['USER_ID'],'PERSON_TYPE_ID'=>$_REQUEST['PERSON_TYPE_ID']));
    endif;

    LocalRedirect($APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'])),array_keys($_REQUEST),false));
endif;

$arResult['OPTIONS'] = array();

foreach($arResult['USERS'] as $arUser):
    $arResult['OPTIONS'][] = '<option value="'.$arUser['ID'].'">'.$arUser['LAST_NAME'].' '.$arUser['NAME'].' '.$arUser['SECOND_NAME'].' ['.$arUser['ID'].']</option>';
endforeach;
?>
<div class="b-user on">
    <div class="wrap">
        <h1 class="g-main-title">Контрагенты и пользователи</h1>
        <div class="b-user_tabs">
            <ul class="b-tabs">
                <li class="b-tabs_tab<?if($_REQUEST['accounts']):?> active<?endif;?>" data-acive="active"><a href="<?=$APPLICATION->GetCurPageParam('accounts=1',array_keys($_REQUEST),false);?>"><p class="tab_text"><svg class="" viewBox="0 0 75 75"><use xmlns:xlink="//www.w3.org/1999/xlink" xlink:href="/local/templates/dentlmanru/assets/images/personal.menu.icons.svg#personalaccounts"></use></svg></p>Список контрагентов</a></li>
                <li class="b-tabs_tab<?if(!$_REQUEST['accounts']):?> active<?endif;?>" data-acive="active"><a href="<?=$APPLICATION->GetCurPageParam('accounts=0',array_keys($_REQUEST),false);?>"><p class="tab_text"><svg class="" viewBox="0 0 75 75"><use xmlns:xlink="//www.w3.org/1999/xlink" xlink:href="/local/templates/dentlmanru/assets/images/personal.menu.icons.svg#personal"></use></svg></p>Список пользователей</a></li>
            </ul>
        </div>
        <div class="b-user on">
            <div class="wrap">
                <div class="b-user_tabs-content dmLicenseContainer<?=htmlspecialcharsbx($_REQUEST['accounts']);?> active">
                    <?if($_REQUEST['accounts']):?>
                        <h3 class="b-user_z">Список контрагентов</h3>
                        <div class="dmLicense">
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'ID','order'=>$_REQUEST['by']=='ID'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-1 <?=($_REQUEST['by']=='ID' ? htmlspecialcharsbx($_REQUEST['order']): ' asc desc')?>">ID</a>
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'NAME','order'=>$_REQUEST['by']=='NAME'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-3 <?=($_REQUEST['by']=='NAME' ? htmlspecialcharsbx($_REQUEST['order']) : 'asc desc');?>">Контрагент</a>
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'PERSON_TYPE_ID','order'=>$_REQUEST['by']=='PERSON_TYPE_ID'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-3 <?=( $_REQUEST['by']=='PERSON_TYPE_ID' ? htmlspecialcharsbx($_REQUEST['order']) : 'asc desc')?>">Тип</a>
                            <p class="dmLicense_cell col-lg-3">Пользователь</p>
                            <p class="dmLicense_cell col-lg-1" style="text-align:center;"></p>
                        </div>
                        <div class="dmLicense">
                            <form action="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>$_REQUEST['by'],'order'=>$_REQUEST['order'])),array_keys($_REQUEST),false);?>">
                                <p class="dmLicense_cell col-lg-1">
                                    <input type="hidden" name="accounts" value="<?=htmlspecialcharsbx($_REQUEST['accounts'])?>" class="accounts">
                                    <input type="hidden" name="by" value="<?=htmlspecialcharsbx($_REQUEST['by'])?>" class="by">
                                    <input type="hidden" name="order" value="<?=htmlspecialcharsbx($_REQUEST['order'])?>" class="order">
                                    <input type="hidden" name="FILTER" value="Y" class="FILTER">
                                    <input name="FILTER_ID" class="FILTER_ID" value="<?htmlspecialcharsbx($_REQUEST['FILTER_ID'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-3">
                                    <input name="FILTER_NAME" class="FILTER_NAME" value="<?=htmlspecialcharsbx($_REQUEST['FILTER_NAME'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-3">
                                    <select name="FILTER_PERSON_TYPE_ID" class="FILTER_PERSON_TYPE_ID">
                                        <option value=""<?=$_REQUEST['FILTER_PERSON_TYPE_ID']==''?' selected="selected"':'';?>>Все</option>
                                        <?foreach($arResult['PERSON_TYPE'] as $arItem):?>
                                            <option value="<?=$arItem['ID'];?>"<?=$_REQUEST['FILTER_PERSON_TYPE_ID']==$arItem['ID']?' selected="selected"':'';?>><?=$arItem['NAME'];?></option>
                                        <?endforeach;?>
                                    </select>
                                </p>
                                <p class="dmLicense_cell col-lg-1"></p>
                                <p class="dmLicense_cell col-lg-3">
                                    <input type="submit" name="submit" class="b-login_button" value="Фильтровать">
                                    <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>$_REQUEST['by'],'order'=>$_REQUEST['order'])),array_keys($_REQUEST),false);?>" class="b-login_button" title="Сбросить">Сбросить</a>
                                </p>
                            </form>
                        </div>
                        <?
                        foreach($arResult['PROFILES'] as $arItem):
                            $urlEdit = $APPLICATION->GetCurPageParam(http_build_query(array('ID'=>$arItem['ID'],'accounts'=>$_REQUEST['accounts'],'TYPE'=>'USER_ID','USER_ID'=>$arItem['USER_ID'],'PERSON_TYPE_ID'=>$arItem['PERSON_TYPE_ID'])),array_keys($_REQUEST),false);?>
                            <div class="dmLicense" style="position: relative;">
                                <p class="dmLicense_cell col-lg-1"><?=$arItem['ID'];?></p>
                                <p class="dmLicense_cell col-lg-3"><?=$arItem['NAME'];?></p>
                                <p class="dmLicense_cell col-lg-3"><?=$arResult['PERSON_TYPE'][$arItem['PERSON_TYPE_ID']]['NAME'];?></p>
                                <p class="dmLicense_cell col-lg-3"><?=$arResult['USERS'][$arItem['USER_ID']]['LAST_NAME'];?> <?=$arResult['USERS'][$arItem['USER_ID']]['NAME'];?> <?=$arResult['USERS'][$arItem['USER_ID']]['SECOND_NAME'];?> [<?=$arItem['USER_ID'];?>]</p>
                                <p class="dmLicense_cell col-lg-2"><a href="javascript:void(0);" class="data-ajax" data-ajax="<?=$urlEdit;?>" data-slave="#dmLicenseForm" data-trigger="Show" data-event="click" data-type="USER" data-id="<?=$arItem['ID'];?>">Редактировать</a></p>
                            </div>
                        <?endforeach;

                        if(empty($arResult['PROFILES'])):
                            echo '<div class="dmLicense" style="height:auto;text-align: center;padding:20px;">Нет данных для отображения</div>';
                        endif;?>
                    <?else:?>
                        <h3 class="b-user_z">Список пользователей</h3>
                        <div class="dmLicense" style="position: relative;">
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'ID','order'=>$_REQUEST['by']=='ID'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-1 <?=$_REQUEST['by']=='ID'?$_REQUEST['order']:'asc desc';?>">ID</a>
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'LAST_NAME','order'=>$_REQUEST['by']=='LAST_NAME'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-2 <?=$_REQUEST['by']=='LAST_NAME'?$_REQUEST['order']:'asc desc';?>">Пользователь</a>
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'EMAIL','order'=>$_REQUEST['by']=='EMAIL'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-2 <?=$_REQUEST['by']=='EMAIL'?$_REQUEST['order']:'asc desc';?>">Email</a>
                            <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>'DATE_REGISTER','order'=>$_REQUEST['by']=='DATE_REGISTER'?($_REQUEST['order']=='asc'?'desc':'asc'):'asc')),array_keys($_REQUEST),false);?>" class="dmLicense_cell col-lg-1 <?=$_REQUEST['by']=='DATE_REGISTER'?$_REQUEST['order']:'asc desc';?>">Регистрация</a>
                            <p class="dmLicense_cell col-lg-3">Контрагент</p>
                            <p class="dmLicense_cell col-lg-3">Тип</p>
                        </div>
                        <div class="dmLicense">
                            <form action="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>$_REQUEST['by'],'order'=>$_REQUEST['order'])),array_keys($_REQUEST),false);?>">
                                <p class="dmLicense_cell col-lg-1">
                                    <input type="hidden" name="accounts" value="<?=htmlspecialcharsbx($_REQUEST['accounts'])?>" class="accounts">
                                    <input type="hidden" name="by" value="<?=htmlspecialcharsbx($_REQUEST['by'])?>" class="by">
                                    <input type="hidden" name="order" value="<?=htmlspecialcharsbx($_REQUEST['order'])?>" class="order">
                                    <input type="hidden" name="FILTER" value="Y" class="FILTER">
                                    <input name="FILTER_ID" class="FILTER_ID" value="<?htmlspecialcharsbx($_REQUEST['FILTER_ID'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-2">
                                    <input name="FILTER_USER_NAME" class="FILTER_USER_NAME" value="<?=htmlspecialcharsbx($_REQUEST['FILTER_USER_NAME'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-2">
                                    <input name="FILTER_EMAIL" class="FILTER_EMAIL" value="<?=htmlspecialcharsbx($_REQUEST['FILTER_EMAIL'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-1">
                                    <input name="FILTER_DATE_REGISTER" class="FILTER_DATE_REGISTER" value="<?=htmlspecialcharsbx($_REQUEST['FILTER_DATE_REGISTER'])?>" style="width:80%;">
                                </p>
                                <p class="dmLicense_cell col-lg-3">
                                    <input type="submit" name="submit" class="b-login_button" value="Фильтровать">
                                    <a href="<?=$APPLICATION->GetCurPageParam(http_build_query(array('accounts'=>$_REQUEST['accounts'],'by'=>$_REQUEST['by'],'order'=>$_REQUEST['order'])),array_keys($_REQUEST),false);?>" class="b-login_button" title="Сбросить">Сбросить</a>
                                </p>
                            </form>
                        </div>
                        <?
                        foreach($arResult['USERS'] as $arItem):
                            foreach($arResult['PROFILES'][$arItem['ID']] as $arProfile):
                                $urlEdit = $APPLICATION->GetCurPageParam(http_build_query(array('ID'=>$arProfile['ID'],'accounts'=>$_REQUEST['accounts'],'TYPE'=>'USER_ID','USER_ID'=>$arItem['ID'],'PERSON_TYPE_ID'=>$arProfile['PERSON_TYPE_ID'])),array_keys($_REQUEST),false);?>
                                <div class="dmLicense" style="position: relative;">
                                    <p class="dmLicense_cell col-lg-1"><?=$arItem['ID'];?></p>
                                    <p class="dmLicense_cell  col-lg-2"><?=$arItem['LAST_NAME'];?> <?=$arItem['NAME'];?> <?=$arItem['SECOND_NAME'];?> [<?=$arItem['ID'];?>]</p>
                                    <p class="dmLicense_cell  col-lg-2"><?=$arItem['EMAIL'];?></p>
                                    <p class="dmLicense_cell  col-lg-1"><?=$arItem['DATE_REGISTER'];?></p>
                                    <p class="dmLicense_cell  col-lg-3"><?=$arProfile['NAME'];?> [<?=$arProfile['ID'];?>]</p>
                                    <p class="dmLicense_cell  col-lg-2"><?=$arResult['PERSON_TYPE'][$arProfile['PERSON_TYPE_ID']]['NAME'];?></p>
                                    <p class="dmLicense_cell  col-lg-1"><a href="javascript:void(0);" class="data-ajax" data-ajax="<?=$urlEdit;?>" data-slave="#dmLicenseForm" data-trigger="Show" data-event="click" data-type="USER" data-id="<?=$arProfile['ID'];?>">Редактировать</a></p>
                                </div>
                            <?endforeach;
                        endforeach;

                        if(empty($arResult['PROFILES'])):
                            echo '<div class="dmLicense" style="height:auto;text-align: center;padding:20px;">Нет данных для отображения</div>';
                        endif;?>
                    <?endif;?>
                </div>
                <?=$arResult['NAV_STRING'];?>
            </div>
        </div>
    </div>
</div>
<div id="dmLicenseForm" class="dmLicense dmLicenseForm" data-trigger="GetAccountProperties" data-ajax="/ajax/?do=Get&mode=AccountsProperties" data-name="GetAccountProperties">
    <form action="<?=$APPLICATION->GetCurPageParam();?>" method="post">
        Выберите пользователя, чтобы привязать его к контрагенту.
        <input type="hidden" id="" name="ID" class="ID" value="">
        <select name="USER_ID" class="USER_ID"></select><br>
        <select name="PERSON_TYPE_ID" class="PERSON_TYPE_ID">
            <?foreach($arResult['PERSON_TYPE'] as $arItem):?>
                <option value="<?=$arItem['ID'];?>"><?=$arItem['NAME'];?></option>
            <?endforeach;?>
        </select><br><br>
        <div style="display: inline-block;width:100%;">
            <input type="submit" name="submit" class="b-login_button" value="Сохранить">
            <button class="b-login_button data-ajax" data-slave="#dmLicenseForm" data-trigger="Show" data-event="click">Закрыть</button>
        </div>
    </form>
</div>
<template><option value="#ID#" data-content="replace,html" data-attr="value">#FULL_NAME#</option></template>
<style type="text/css">
    .dmLicense{width:100%;clear:both;}
    .dmLicense form .b-login_button{margin:0!important;font-size: 10px;  padding: 13px;  height: auto;  width: auto;  display: block;  position: static;  line-height: 100%;  color: #ffffff;  border: none;}
    .dmLicense form a.b-login_button{position:absolute;right:0;top:0;}
    .dmLicense form input.b-login_button{position:absolute;left:0;top:0;}
    .dmLicense form select {height:20px;font-size:14px;}
    .dmLicense form input {height:20px;font-size:14px;}
    .dmLicense.dmLicenseForm{height: auto;text-align:center;width:50%;left:25%;top:25%;background:#f4f4f4;border:1px solid #c1c1c1;display:none;padding:5%;position:fixed;z-index:100;}
    .dmLicense.dmLicenseForm.active{display:inline-block;}
    .dmLicense.dmLicenseForm .b-login_button{position:static;margin:0% 19%!important;}
    .dmLicense_cell{overflow: hidden;width:10%;position: relative;}
    a.dmLicense_cell.asc::after{content:'\2191';position:absolute;}
    a.dmLicense_cell.desc::after{content:'\2193';position:absolute;}
    a.dmLicense_cell.asc.desc::after{content:'\2191\2193';position:absolute;}
    .dmLicense_id{width:4.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_user_name{width:19.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_email{font-size:10px;padding:0% 1%;overflow:hidden;width:7.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_date_register{width:14.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_name{width:19.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_person_type{font-size:10px;width:9.5%;border-right:1px solid #c1c1c1;text-align:center;}
    .dmLicense_status{width:20%;text-align:center;}
    .dmLicenseContainer1 .dmLicense_name{width:29.5%;}
    .dmLicenseContainer1 .dmLicense_person_type{width:14.5%;}
    .dmLicenseContainer1 .dmLicense_user_name{width:29.5%;}
    .dmLicenseContainer1 .dmLicense_person_type select{width:80%;}
</style>
<?$timestamp=mktime(date('H'),0,0,date('m'),date('n'),date('Y'));?>
    <script type="text/javascript">
        window.XGuardLoader=window.XGuardLoader||[];
        window.XGuardLoader.push(function()
        {
            try
            {
                xGuard.timestamp    = xGuard.timestamp||(function(a,b){return a=new Date(),b=new Date(a.getFullYear(),a.getMonth(),a.getDate(),a.getHours()-1,0,0,0),b.getTime();})();
                xGuard.R({name:'accounts.list',url:'//<?=$_SERVER['SERVER_NAME'];?><?=SITE_TEMPLATE_PATH;?>/assets/js/0.2/main/accounts.list.js?'+xGuard.timestamp});
            }
            catch(e)
            {
                console.log(e.stack);
            }
        });
    </script>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>