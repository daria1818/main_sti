<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
CJSCore::Init(array("popup"));?>

<div class="table_rb_personal">
	<?if(isset($arResult['RB_SUCCESS'])){?>
		<div class="rb_success_text"><?=$arResult['RB_SUCCESS']?></div>
	<?}?>
	<?if(isset($arResult['RB_FATAL'])){?>
		<div class="rb_fatal_text"><?=$arResult['RB_FATAL']?></div>
	<?}?>	
	<?if(isset($arResult['RB_NO_REGIST'])){?>
		<form action="<?=POST_FORM_ACTION_URI?>" method='POST' enctype='multipart/form-data' id="<?=$arResult['RB_JS_BLOCK']['FORM_ID']?>">
			<?=bitrix_sessid_post();?>
			<p><?=$arResult['RB_NO_REGIST']?></p>
			<button type="submit" class="btn btn-default" id="<?=$arResult['RB_JS_BLOCK']['BUTTON']?>"><?=Loc::getMessage("RB_PERSONAL_BUTTOM")?></button>
		</form>	
	<?}else{?>
		<p><?=Loc::getMessage("RB_PERSONAL_BALANCE", array("#BONUS#" => (!empty($arResult['RB_BALANCE_FORMATED']) ? $arResult['RB_BALANCE_FORMATED'] : $arResult["RB_JS_BLOCK"]['BALANCE'])))?></p>
		<?if($arResult["RB_JS_BLOCK"]['BALANCE'] > 0){?>
			<p><?=Loc::getMessage("RB_SEND_TO_USER")?></p>
			<form action="<?=POST_FORM_ACTION_URI?>" method='POST' enctype='multipart/form-data' id="<?=$arResult['RB_JS_BLOCK']['FORM_ID']?>_send">
				<div class="rb_error_text"></div>
				<?=bitrix_sessid_post();?>
				<div class="rb_div">
					<label for="rb_amount_send"><?=Loc::getMessage("RB_LABLE_AMOUNT")?>:</label>
					<input type="text" name="rb_amount_send" class="rb_amount_send" required />
				</div>
				<div class="rb_div">
					<label for="rb_email_send"><?=Loc::getMessage("RB_LABLE_EMAIL")?>:</label>
					<input type="email" name="rb_email_send" class="rb_email_send" placeholder="admin@system.ru" required />
				</div>
				<button type="submit" class="btn btn-default" id="<?=$arResult['RB_JS_BLOCK']['BUTTON']?>_send"><?=Loc::getMessage("RB_PERSONAL_BUTTOM_SEND")?></button>
			</form>
		<?}?>	
	<?}?>	
</div>

<script>
	BX.message({
		RB_FORM_TITLE: '<?=GetMessageJS('RB_FORM_TITLE')?>',
		RB_BTN_OK: '<?=GetMessageJS('RB_BTN_OK')?>',
		RB_BTN_CLOSE: '<?=GetMessageJS('RB_BTN_CLOSE')?>',
		RB_ERROR_EMAIL: '<?=GetMessageJS('RB_ERROR_EMAIL')?>',
		RB_ERROR_AMOUNT: '<?=GetMessageJS('RB_ERROR_AMOUNT')?>',
		RB_ERROR_AMOUNT_NUM: '<?=GetMessageJS('RB_ERROR_AMOUNT_NUM')?>',
		RB_GLOBAL_ERROR: '<?=GetMessageJS('RB_GLOBAL_ERROR')?>'
	});
	var jsFrom = <?=CUtil::PhpToJSObject($arResult['RB_JS_BLOCK'])?>;
</script>