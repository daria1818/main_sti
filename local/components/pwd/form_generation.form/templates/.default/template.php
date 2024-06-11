<?php

(defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) || die();

/**
 * @var $arResult array
 * @var $arParams array
 * @var $component Pwd\Components\FormGenerationComponent
 * @var $this CBitrixComponentTemplate
 */

use Bitrix\Main\Component\ParameterSigner;
use Nette\Utils\Html;

$formGeneration = Html::el('div class="form form-generation"');
$form = $formGeneration->create('form name="form-generation" method="POST" enctype="multipart/form-data"');

ob_start();
$APPLICATION->IncludeComponent("bitrix:fileman.light_editor","",Array(
        "CONTENT" => "",
        "INPUT_NAME" => "form_generation_text",
        "INPUT_ID" => "",
        "WIDTH" => "100%",
        "HEIGHT" => "200px",
        "RESIZABLE" => "Y",
        "AUTO_RESIZE" => "Y",
        "VIDEO_ALLOW_VIDEO" => "Y",
        "VIDEO_MAX_WIDTH" => "640",
        "VIDEO_MAX_HEIGHT" => "480",
        "VIDEO_BUFFER" => "20",
        "VIDEO_LOGO" => "",
        "VIDEO_WMODE" => "transparent",
        "VIDEO_WINDOWLESS" => "Y",
        "VIDEO_SKIN" => "/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf",
        "USE_FILE_DIALOGS" => "Y",
        "ID" => "",
        "JS_OBJ_NAME" => ""
    )
);
$htmlAreaBuffer = ob_get_clean();

$formControl1 = $form->create('div class="form-control"');
$label1 = $formControl1->create('label');
$label1->create('span')->addHtml('Заголовок&nbsp;');
$input1 = $formControl1->create('input type="text" class="inputtext" name="form_generation_title" value="" aria-required="true"');

$formControl3 = $form->create('div class="form-control"');
$label3 = $formControl3->create('label');
$label3->create('span')->addHtml('Описание&nbsp;');
$htmlArea = $formControl3->addHtml($htmlAreaBuffer);

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('ID кампании&nbsp;');
$label2->create('span class="star"')->addHtml('*');
$input2 = $formControl2->create('input type="text" class="inputtext" required="" name="form_generation_id_camp" value=""
                   aria-required="true"');

$form->create('div class="clearboth"');
$form->create('textarea name="nspm" style="display:none;"');
$form->create('div class="clearboth"');
$form->create('div class="form_footer"')->create('input type="submit" class="btn btn-default" value="Сгенерировать" name="web_form_submit"');
$form->create('div class="output-src"');

echo $formGeneration;
?>
<div id="okay" class="modal fade">
</div>
<!--/noindex-->
<script type="text/javascript">
    (function () {
        var FormGenerationComponent = BX.namespace('__pwd.FormGenerationComponent');
        FormGenerationComponent.signedParameters = '<?= ParameterSigner::signParameters($component->getName(), $arParams) ?>';
    })();
</script>
