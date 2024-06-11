<?php
	IncludeModuleLangFile(__FILE__);
?>
<div class="alfabank__wrapper">
	<div class="alfabank__content">
		<?php if (in_array($params['alfabank_result']['errorCode'], array(999, 1, 2, 3, 4, 5, 7, 8))) { ?>
			<span class="alfabank__description"><?=getMessage("ALFABANK_PAYMENT_DESCRIPTION");?>:</span>
			<span class="alfabank__error-code"><?=getMessage("ALFABANK_PAYMENT_ERROR_TITLE");?><?=$params['alfabank_result']['errorCode']?></span>
			<span class="alfabank__error-message"><?=$params['alfabank_result']['errorMessage']?></span>

		<?php } else if ($params['alfabank_result']['payment'] == 1) { ?>

			<span class="alfabank__error-message"><?=getMessage("ALFABANK_PAYMENT_MESSAGE_PAYMENT_ALLREADY");?></span>

		<?php } else if ($params['alfabank_result']['errorCode'] == 0) { ?>

			<? if($params['ALFABANK_HANDLER_AUTO_REDIRECT'] == 'Y') {?>
				<script>
					var needRedirect = true;
					var currentPage = window.location.pathname
					var auto_redirect_exceptions = JSON.parse('<?=json_encode($params['auto_redirect_exceptions']);?>');
					auto_redirect_exceptions.forEach((element) => {
					  if(currentPage.match(element)) {
					  	needRedirect = false;
					  }
					})
					if(needRedirect) {
						window.location = '<?=$params['payment_link']?>';
					}
				</script>
			<?php } ?>

			<span class="alfabank__price-string"><?=getMessage("ALFABANK_PAYMENT_PAYMENT_TITLE");?>: <b><?=CurrencyFormat($params['ALFABANK_ORDER_AMOUNT'], $params['currency'])?></b></span>
			<a href="<?=$params['payment_link']?>" class="alfabank__payment-link"><?=getMessage("ALFABANK_PAYMENT_PAYMENT_BUTTON_NAME");?></a>
			<span class="alfabank__payment-description"><?=getMessage("ALFABANK_PAYMENT_PAYMENT_DESCRIPTION");?></span>

		<?php } else { ?>
			<span class="alfabank__error-message"><?=getMessage("ALFABANK_PAYMENT_ERROR_MESSAGE_UNDEFIND");?></span>

		<?php } ?>
	</div>
	<div class="alfabank__footer">
		<span class="alfabank__description"><?=getMessage("ALFABANK_PAYMENT_FOOTER_DESCRIPTION");?></span>
	</div>
</div>

<style>
	body .alfabank__wrapper {
		font-family: arial;
		text-align: left;
		margin-bottom: 20px;
		margin-top: 20px;
	}
	body .alfabank__price-block {
		font-family: arial;
		display: block;
		margin: 20px 0px;
	}
	body .alfabank__price-string {
		font-family: arial;
		font-weight: bold;
		font-size: 14px;
	}
	body .alfabank__price-string b {
		font-family: arial;
		font-size: 20px;
	}
	body .alfabank__content {
		font-family: arial;
	    max-width: 400px;
	    width: 100%;
	    padding: 10px 10px 13px;
	    border: 1px solid #e5e5e5;
	    text-align: center;
	    margin-bottom: 12px;
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	}
	body .alfabank__payment-link {
		font-family: arial;
		display: inline-block;
		max-width: 320px;
		width: 100%;
		margin: 8px 0 5px;
		background-color: #ef3124 !important;
		color: #fff !important;
		border:none;
		box-shadow: none;
    	outline: none;
    	font-size: 14px;
	    font-weight: normal;
	    line-height: 1.42857143;
	    text-align: center;
    	white-space: nowrap;
    	vertical-align: middle;
    	padding: 6px 12px;
    	text-decoration: none !important;
	}
	body .alfabank__payment-link:hover,.alfabank__payment-link:active,.alfabank__payment-link:focus {
		font-family: arial;
		background: #c72217;
		color: #fff;
	}
	body .alfabank__payment-description {
		font-family: arial;
		display: block;
		font-size: 12px;
		color: #939393;
	}
	body .alfabank__description {
		font-family: arial;
		font-size: 12px;
		max-width: 400px;
		display: block;
	}
	body .alfabank__error-code {
		font-family: arial;
		color: red;
		font-size: 20px;
		display: block;
		margin-top:5px;
		margin-bottom: 7px;
	}
	body .alfabank__error-message {
		font-family: arial;
		color:#000;
		font-size: 14px;
		display: block;
	}
	@media(max-width: 500px) {
		body .sberbank__price-string b {
			display:block;
			text-align:center;
		}

	}
</style>