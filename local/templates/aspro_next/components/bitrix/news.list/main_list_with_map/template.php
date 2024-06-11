<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);

$count = 0;
$countI = 0;
$city = "";

if($arResult['ITEMS']):?>
<div class="top_info">
	<div class="top_info__list top_info__list_js">
		<ul>
		<?foreach($arResult['ITEMS'] as $i => $arItem):?>
			<?$count++;?>
			<li>
				<?=$arItem["PROPERTIES"]["ADDRESS"]["VALUE"];?>
			</li>
			<?
			$arPointDone[$countI]["TEXT"] = $arItem["PROPERTIES"]["ADDRESS"]["VALUE"];
			$arCoord = explode(",", $arItem["PROPERTIES"]["COORD"]["VALUE"]);
		 	$arPointDone[$countI]["LAT"] = $arCoord[0];
		 	$arPointDone[$countI]["LON"] = $arCoord[1];
		 	$countI++;
		 	?>
		<?endforeach;?>

		</ul>
	</div>
	<?if($count>5){?>
	<div class="top_info__more-block top_info__more-block_js">
		<span>Показать все</span>
		<span class="top_info__arrow top_info__arrow_js">↓</span>
	</div>
	<?}?>
</div>


<div class="p30">


<?$APPLICATION->IncludeComponent( 
	"bitrix:map.yandex.view",
	"",
	Array( 
		"INIT_MAP_TYPE" => "MAP", 
		"MAP_DATA" => serialize(array( 
		'yandex_lat' => trim($arResult['COORDS'][0]), 
		'yandex_lon' => trim($arResult['COORDS'][1]),
		'yandex_scale' => 13, 
		'PLACEMARKS' => $arPointDone, 
		)), 
		"MAP_WIDTH" => "100%", 
		"MAP_HEIGHT" => "300", 
		"CONTROLS" => array("ZOOM", "MINIMAP", "TYPECONTROL", "SCALELINE"), 
		"OPTIONS" => array("DESABLE_SCROLL_ZOOM", "ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING"), 
		"MAP_ID" => "" 
	), 
	false 
);?> 
</div>
<?endif;?>

