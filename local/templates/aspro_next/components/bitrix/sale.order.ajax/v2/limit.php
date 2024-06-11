<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
?>
<div class="bx-soa-empty-cart-container">
	<div class="bx-soa-empty-cart-text"><?=Loc::getMessage("LIMIT_BASKET_SUCCESS_TITLE")?></div>
	<div><?=Loc::getMessage("LIMIT_BASKET_SUCCESS_TEXT")?></div>
</div>