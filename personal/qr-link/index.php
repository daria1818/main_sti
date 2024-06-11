<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/cabinet/qr_create.css", true);
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/cabinet/qr_create.js");
?>
<h1>Генерация QR-кода и ссылки для легкой регистрации</h1>
<form action="form_submit.php" method="POST" name="QR_GENERATE" id="QR_GENERATE">
	<input type="text" hidden name="qr_hash">
	<div class="form-control">
        <label><span>Начислять по данной ссылке ₽ <span class="star">*</span></span></label>
		<input type="number" name="currency" class="inputtext" value="3000">
    </div>
	<button type="submit" class="btn btn-default"> Создать </button>
</form>
<div class="createQR__wrap">
	<div id="result">
		
	</div>
	<div class="createQR__item">
		<img id="QR_response" src=''>
	</div>
	<div class="createQR__item">
		<a id="QR_link" href="#" class="btn btn-default copy_link">Скопировать ссылку</a>
	</div>
</div>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>