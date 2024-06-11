<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый раздел");
?><div class="maxwidth-theme">
	<div id="permalink" class="form inline">
		<div class="form_head">
			 <h4 class="margin-none">Заголовок</h4>
		</div>
		<div>
			 харизма профилактика лечение
		</div>
		<form action="http://142.93.100.228:4000/parse" method="POST" target="_blank">
 <input type="hidden" name="campaign_uid" value="reward_for_2021_jan">
			<div class="form_body">
				<div class="row">
					<div class="col-md-5">
						<div class="form-control">
 <label><span>Ссылка на пост для анализа текста&nbsp;<span class="star">*</span></span></label> <input type="text" required="" class="inputtext" name="permalink" value="" data-sid="LINK" aria-required="true">
						</div>
						<div class="form-control">
 <label>E-mail</label> <input type="email" required="" placeholder="mail@domen.com" class="inputtext" name="email" value="" data-sid="EMAIL" aria-required="true">
						</div>
					</div>
				</div>
			</div>
			<div class="form_footer">
 <input type="submit" class="btn btn-default" value="Отправить">
			</div>
		</form>
	</div>
</div>
<script>
	jQuery('form').submit(function () {
        console.log('campaign_uid ' + jQuery(this).find('input[name=campaign_uid]').val());
        console.log('permalink ' + jQuery(this).find('input[name=permalink]').val());
        console.log('email ' + jQuery(this).find('input[name=email]').val());
    })
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>