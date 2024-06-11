<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
use Bitrix\Main\Loader;
use Pwd\Helpers\StringHelper;

if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY'])){
	$loadCurrency = false;
	if (!empty($templateData['CURRENCIES']))
		$loadCurrency = Loader::includeModule('currency');
	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency){?>
	<script type="text/javascript">
		BX.Currency.setCurrencies(<? echo $templateData['CURRENCIES']; ?>);
	</script>
	<?}
}

if(!empty($arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION']))
{
	$params =& $component->arParams;
	$params["SET_META_DESCRIPTION"] = "N";

	if(strlen($arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION']) > 165)
	{
		$desc = StringHelper::TruncateSentence($arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'], 2);
		$APPLICATION->SetPageProperty("description", $desc);
	}
	else
	{
		$APPLICATION->SetPageProperty("description", $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION']);
	}
}
?>