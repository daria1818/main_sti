<?php

use Bitrix\Main\Component\ParameterSigner;
use Nette\Utils\Html;

$formGeneration = Html::el('div class="form form-generation"');
$form = $formGeneration->create('form name="form-qr-generation" method="POST" enctype="multipart/form-data"');

ob_start();
$APPLICATION->IncludeComponent("bitrix:fileman.light_editor", "", Array(
        "CONTENT" => "",
        "INPUT_NAME" => "html",
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
$input1 = $formControl1->create('input type="text" class="inputtext" name="title" value="Добро пожаловать в STIOnline" placeholder="Добро пожаловать в STIOnline" aria-required="true"');

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('Менеджер&nbsp;');
$label2->create('span class="star"')->addHtml('*');

$options = '';
foreach ($arResult['EMPL'] as $emp) {
    $options .= '<option value=' . $emp['ID'] . '>' . $emp['NAME'] . ' ' . $emp['LAST_NAME'] . '</option>';
}
$input3 = $formControl2->addHtml('
                            <select name="manager" required class="usual">
                                ' . $options . '
                            </select>
');

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('Мероприятие&nbsp;');
$label2->create('span class="star"')->addHtml('*');
$input4 = $formControl2->create('input type="text" class="inputtext" required="" name="event" value=""
                   aria-required="true"');

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('Дата&nbsp;');
$label2->create('span class="star"')->addHtml('*');
$input3 = $formControl2->create('input id="date-input" type="date" class="inputtext" required="" name="date" value="' . date("d-m-Y") . '"
                   aria-required="true"');

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('Город&nbsp;');
$label2->create('span class="star"')->addHtml('*');
$input5 = $formControl2->create('input type="text" class="inputtext" required="" name="city" value=""
                   aria-required="true"');

$formControl2 = $form->create('div class="form-control"');
$label2 = $formControl2->create('label');
$label2->create('span')->addHtml('Лектор&nbsp;');
$label2->create('span class="star"')->addHtml('*');
$input6 = $formControl2->create('input type="text" class="inputtext" name="teacher" value=""
                   aria-required="true"');

$formControl3 = $form->create('div class="form-control"');
$label3 = $formControl3->create('label');
$label3->create('span')->addHtml('Комментарий&nbsp;');
$htmlArea = $formControl3->addHtml($htmlAreaBuffer);

$form->create('div class="clearboth"');
$form->create('textarea name="nspm" style="display:none;"');
$form->create('div class="clearboth"');
$form->create('div class="form_footer"')->create('input type="submit" class="btn btn-default" value="Получить QR Код" name="web_form_submit"');
$form->create('div class="output-src-generation"');

echo $formGeneration;