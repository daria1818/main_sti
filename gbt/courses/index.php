<?php
// define("NEED_AUTH", true);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $APPLICATION;
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
$APPLICATION->SetTitle("Календарь курсов");
?><?$APPLICATION->IncludeComponent(
	"ses:calendar.manager",
	"list",
	Array(
		"COMPONENT_TEMPLATE" => "list",
		"CALENDAR_TYPE" => array('SDA'),
		"FILTER" => array("UF_TYPE"=>array(244,245,252)),
		"SELECTION_DAYS" => "temp2"
	)
);?>
<p>
	 Хотите научиться выполнять лечение и профилактику заболеваний пародонта так, чтобы пациенты не испытывали боли и дискомфорта и возвращались к вам снова и снова?
</p>
<p>
	 Станьте мастером безболезненной стоматологии с <b>курсами от STI Dent</b>.
</p>
<p>
 <b>Освойте протокол GBT</b> -&nbsp; инновационный швейцарский протокол профессиональной гигиены полости рта, в основе которого подача уникального&nbsp; порошка на основе эритритола, благодаря чему появляется возможность комфортно и эффективно удалить биопленку, пятна и молодой зубной камень над и под десной, снижая необходимость в ручном и механическом инструменте. А с PIEZON NO PAIN и насадкой PS Instrument появляется возможность минимально инвазивно и безболезненно бороться с зубными камнями там, где это необходимо.
</p>
<p>
	 Обучение проводится на швейцарском оборудовании самого последнего поколения аккредитованными лекторами SDA (Swiss Dental Academy - Швейцарской Стоматологической Академии).
</p>
<p>
	 Выбирайте удобное для вас время в календаре курсов и записывайтесь на обучение!
</p><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>