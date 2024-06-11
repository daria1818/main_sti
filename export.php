<?


error_reporting(E_ALL);

ini_set("display_error", true);

set_time_limit(0);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//global $DB; // ?


CModule::IncludeModule('iblock');


/*
$rsParentSection = CIBlockSection::GetList(
		Array('name' => 'asc'),
		Array( 'IBLOCK_ID' => 30, 'ACTIVE' => 'Y', 'SECTION_ID' => 0 )
	);


while ($arParentSection = $rsParentSection->GetNext())
{
	
	// lvl0
	// CODE - символьный
	// XML_ID - внешний
	// {$arParentSection['ID']}; +
	echo "{$arParentSection['NAME']};{$arParentSection['XML_ID']};<br>";
	
	
	//$arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']); // выберет потомков без учета активности
	$arFilter = array( 'SECTION_ID' => $arParentSection['ID'] );
		
	//$rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
	$rsSect = CIBlockSection::GetList( array('left_margin' => 'asc'), $arFilter);
	
	
	while ($arSect = $rsSect->GetNext())
	{
		
	    //echo '&#8195;'.$arSect['NAME'].'<br>';
		echo "&#8195;{$arSect['NAME']};{$arSect['XML_ID']};{$arParentSection['XML_ID']}<br>";
		
	}
	
}
*/


$lvl = 0; // !! global


function printSectionsRec($parent=0, $parent_key="") {
	
	
	global $lvl;
	
	
	$rsParentSection = CIBlockSection::GetList(
		Array('sort' => 'asc'),
		Array( 'IBLOCK_ID' => 30, 'ACTIVE' => 'Y', 'SECTION_ID' => $parent )
	);
	
	
	while ($arParentSection = $rsParentSection->GetNext())
	{
	
		// str_repeat( "&#8195;", $lvl ) . 
		//echo "\"{$arParentSection['NAME']}\";\"{$arParentSection['XML_ID']}\";\"{$parent_key}\"\r\n"; //;{$lvl}<br>";
		//echo "\"{$arParentSection['NAME']}\";\"{$arParentSection['XML_ID']}\";\"{$parent_key}\"	"; //;{$lvl}<br>";
		//echo "\"{$arParentSection['NAME']}\";\"{$arParentSection['XML_ID']}\";\"{$parent_key}\";;;"; //;{$lvl}<br>";
		
		echo "{$arParentSection['NAME']};;;{$arParentSection['XML_ID']};;;{$parent_key}\r";
	
	
		$lvl++;
		
		printSectionsRec( $arParentSection['ID'], $arParentSection['XML_ID'] );
		
		$lvl--;
		
		
		//echo "<br/><br/>";
		
	
	}
		
}



// !!!

printSectionsRec(); // start ;)


