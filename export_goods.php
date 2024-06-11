<?


error_reporting(E_ALL);

ini_set("display_error", true);

set_time_limit(0);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//global $DB; // ?


CModule::IncludeModule('iblock');


$lvl = 0; // !! global


function printSectionsRec($parent=0, $parent_key="") {
	
	
	global $lvl;
	
	
	$rsParentSection = CIBlockSection::GetList(
		Array('sort' => 'asc'),
		Array( 'IBLOCK_ID' => 30, 'ACTIVE' => 'Y', 'SECTION_ID' => $parent )
	);
	
	
	while ($arParentSection = $rsParentSection->GetNext())
	{
			
		// !!! получаем все товары в разделе
		$arSelect = Array("IBLOCK_ID", "ID", "XML_ID");
		//$arFilter = Array("IBLOCK_ID"=>30);
		$arFilter = Array("SECTION_ID" => $arParentSection['ID']);
		
		
		$res = CIBlockElement::GetList(Array("ID"=>"ASC"), $arFilter, false, false, $arSelect);
		
		while($ob = $res->Fetch())
		{
			
			$res_id = CIBlockElement::GetByID($ob['ID']);
			
			//if($ar_res = $res_id->GetNext()) $external_id = $ar_res['XML_ID'];
			if( $ar_res = $res_id->GetNext() ) {
						
			echo "{$ar_res['XML_ID']};;;{$arParentSection['XML_ID']}\r";
			
			}			
			
		}
	
	
		$lvl++;
		
		printSectionsRec( $arParentSection['ID'], $arParentSection['XML_ID'] );
		
		$lvl--;
		
		
		//echo "<br/><br/>";
		
	
	}
		
}



// !!!

printSectionsRec(); // start ;)


