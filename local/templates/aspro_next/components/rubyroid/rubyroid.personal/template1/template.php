<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

CJSCore::Init(array("popup")); ?><div class="table_rb_personal">
	<? if (isset($arResult['RB_SUCCESS'])) { ?>
		<div class="rb_success_text"><?= $arResult['RB_SUCCESS'] ?></div>
	<? } ?>
	<? if (isset($arResult['RB_FATAL'])) { ?>
		<div class="rb_fatal_text"><?= $arResult['RB_FATAL'] ?></div>
	<? } ?>
	<? if (isset($arResult['RB_NO_REGIST'])) { ?>
		<?/*?><form action="<?=POST_FORM_ACTION_URI?>" method='POST' enctype='multipart/form-data' id="<?=$arResult['RB_JS_BLOCK']['FORM_ID']?>">
			<?=bitrix_sessid_post();?>
			<p><?=$arResult['RB_NO_REGIST']?></p>
			<button type="submit" class="btn btn-default" id="<?=$arResult['RB_JS_BLOCK']['BUTTON']?>"><?=Loc::getMessage("RB_PERSONAL_BUTTOM")?></button>
		</form>	<?*/ ?>
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:socserv.auth.split",
			"main",
			array(
				"SUFFIX" => "form",
				"SHOW_PROFILES" => "Y",
				"ALLOW_DELETE" => "Y",
				"COMPONENT_TEMPLATE" => "main",
				"COMPOSITE_FRAME_MODE" => "A",
				"COMPOSITE_FRAME_TYPE" => "AUTO"
			),
			false
		);
		?>
	<? } else { ?>
		<div class="your_account">
			<?= Loc::getMessage("RB_PERSONAL_BALANCE", array("#BONUS#" => $arResult['STI_COINS']['BALANCE'])) ?>
		
		</div>
		<div class="your_account-text">
			<?= Loc::getMessage("RB_EXCHANGE_RATE") ?>
			<strong><?= $arResult["STI_COINS"]['EXCHANGE_RATE'] ?></strong>

		</div>
		<div class="your_account-text">
			<?= Loc::getMessage("RB_PAY_BALANCE") ?>
			<strong><?= $arResult["STI_COINS"]['PAY_BALANCE'] ?></strong>

		</div>
		<? if ($arResult["RB_JS_BLOCK"]['BALANCE'] > 0) { ?>

			<?/*?><p><?=Loc::getMessage("RB_SEND_TO_USER")?></p>
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
			</form><?*/ ?>
		<? } ?>
	<? } ?>
</div>

<style>
	.table_rb_personal .your_account {
		background: #F9F9F9;
		border: 1px solid #F2F2F2;
		border-radius: 2px;
		width: 360px;
		height: 75px;
		margin-bottom: 30px;
		font-weight: 700;
		font-size: 18px;
		line-height: 110%;
		letter-spacing: -0.015em;
		color: #383838;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.table_rb_personal .your_account-text {
		margin-bottom: 14px;
		font-weight: 400;
		font-size: 16px;
		line-height: 110%;
		letter-spacing: -0.015em;
		color: #383838;
	}


	@media(max-width: 1024px) {
		.table_rb_personal .your_account {
			width: 310px;
			height: 60px;
			font-size: 16px;
		}

		.table_rb_personal .your_account-text {
			font-size: 14px;
			margin-bottom: 14px;
		}
	}

	@media(max-width: 767px) {
		.table_rb_personal .your_account {
			width: 100%;
			height: auto;
			font-size: 18px;
			padding: 17px 15px;
		}

		.table_rb_personal .your_account-text {
			font-size: 14px;
			text-align: center;
			line-height: 130%;
		}
	}
</style>

<script>
	BX.message({
		RB_FORM_TITLE: '<?= GetMessageJS('RB_FORM_TITLE') ?>',
		RB_BTN_OK: '<?= GetMessageJS('RB_BTN_OK') ?>',
		RB_BTN_CLOSE: '<?= GetMessageJS('RB_BTN_CLOSE') ?>',
		RB_ERROR_EMAIL: '<?= GetMessageJS('RB_ERROR_EMAIL') ?>',
		RB_ERROR_AMOUNT: '<?= GetMessageJS('RB_ERROR_AMOUNT') ?>',
		RB_ERROR_AMOUNT_NUM: '<?= GetMessageJS('RB_ERROR_AMOUNT_NUM') ?>',
		RB_GLOBAL_ERROR: '<?= GetMessageJS('RB_GLOBAL_ERROR') ?>'
	});
	var jsFrom = <?= CUtil::PhpToJSObject($arResult['RB_JS_BLOCK']) ?>;
</script>