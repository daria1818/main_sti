<?php
use Pwd\Helpers\UserHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

?>


<script type="text/javascript" src="/bitrix/js/fileman/core_file_input.min.js?159435743221486"></script>
<script type="text/javascript" src="/bitrix/components/bitrix/catalog.product.search/templates/.default/script.min.js?16572872867206"></script>


<script type="text/javascript" src="/bitrix/js/main/core/core_ls.js?159435743410430"></script>
<script type="text/javascript" src="/bitrix/js/main/session.js?16423977303701"></script>
<script type="text/javascript" src="/bitrix/js/pull/protobuf/protobuf.js?1595943230274055"></script>
<script type="text/javascript" src="/bitrix/js/pull/protobuf/model.js?159594323070928"></script>
<script type="text/javascript" src="/bitrix/js/rest/client/rest.client.js?160499584917414"></script>
<script type="text/javascript" src="/bitrix/js/pull/client/pull.client.js?165728724170481"></script>
<script type="text/javascript" src="/bitrix/js/main/pageobject/pageobject.js?1594357434864"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_autosave.js?15943574349741"></script>
<script type="text/javascript" src="/bitrix/js/main/utils.js?162395251029279"></script>
<script type="text/javascript" src="/bitrix/js/main/hot_keys.js?159435743417302"></script>
<script type="text/javascript" src="/bitrix/js/main/admin_tools.js?162395256667939"></script>
<script type="text/javascript" src="/bitrix/js/main/popup_menu.js?159435743412913"></script>
<script type="text/javascript" src="/bitrix/js/main/admin_search.js?15943574407230"></script>
<script type="text/javascript" src="/bitrix/js/main/popup/dist/main.popup.bundle.js?1657699177112628"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_window.js?165728737498768"></script>
<script type="text/javascript" src="/bitrix/js/main/date/main.date.js?159435743434530"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_date.js?162395251036080"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_fx.js?159435743416888"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_admin_interface.js?1657699176154774"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_clipboard.js?16239524464773"></script>
<script type="text/javascript" src="/bitrix/js/main/sidepanel/manager.js?165728737433605"></script>
<script type="text/javascript" src="/bitrix/js/main/sidepanel/slider.js?164239773048349"></script>
<script type="text/javascript" src="/bitrix/js/main/helper/helper.js?16263482187739"></script>
<script type="text/javascript" src="/bitrix/js/main/dd.js?159435743414809"></script>
<script type="text/javascript" src="/bitrix/js/catalog/core_tree.js?159435743265214"></script>
<script type="text/javascript" src="/bitrix/js/iblock/subelement.js?159435744013997"></script>

<?$APPLICATION->SetPageProperty("title", "Генератор ссылок");
$APPLICATION->SetTitle("Генератор ссылок");
?>
<?php
if (!UserHelper::isBrandManager()) LocalRedirect('/personal');
?>
    <h1>Генератор ссылок</h1>
<?php
$APPLICATION->IncludeComponent(
    "rtop:form.generation.link",
    "",
    []
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
