<?
use \Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
CModule::IncludeModule('highloadblock');

AddEventHandler("main", "OnBeforeProlog", "MyOnBeforePrologHandler");
function MyOnBeforePrologHandler()
{
   global $USER;
   if(!is_object($USER)){
      $USER = new CUser();
   }
   if (!$USER->IsAdmin()){
      //include($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/site_closed.php");
      //die();
   }
}
// COption::SetOptionString("catalog", "DEFAULT_SKIP_SOURCE_CHECK", "Y"); 
// COption::SetOptionString("sale", "secure_1c_exchange", "N"); 



// AddEventHandler("sale", "OnSaleComponentOrderCreated", "MyBeforeCreateoOrder");
// AddEventHandler("sale", "OnSaleComponentOrderProperties", "MyBeforeCreateoOrder");
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
   "sale", 
   "OnSaleOrderEntitySaved", 
   "MyBeforeCreateoOrder"
);

function MyBeforeCreateoOrder(Bitrix\Main\Event $event){
   CModule::IncludeModule('iblock');
   $filePath = $_SERVER["DOCUMENT_ROOT"]."/log/SaleCreateOrder1.log";
   
   $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
   $arFilter = Array("IBLOCK_ID"=> 98, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "SECTION_ID" => "9053");
   $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
   while($ob = $res->GetNextElement()){ 
    $arBxUsers[$ob->GetProperties()["USER"]["~VALUE"]] = $ob->GetProperties();
   }

   $order = $event->getParameter("ENTITY");
   $arOrderVals = $order->getFields()->getValues();

   if(!empty($arOrderVals) && array_key_exists($arOrderVals["USER_ID"],$arBxUsers)){
      global $USER;
      $el = new CIBlockElement;
      $PROP = array();
      $PROP[1495] = $arOrderVals["USER_ID"];
      $PROP[1496] = $arBxUsers[$arOrderVals["USER_ID"]]["EVENT"]["~VALUE"];
      $PROP[1499] = "https://stionline.ru/bitrix/admin/sale_order_view.php?amp%3Bfilter=Y&%3Bset_filter=Y&lang=ru&ID=" . $arOrderVals["ID"];
      $arLoadProductArray = Array(
        "MODIFIED_BY"    => 1,
        "IBLOCK_SECTION_ID" => 9054,
        "IBLOCK_ID"      => 98,
        "PROPERTY_VALUES"=> $PROP,
        "NAME"           => "Пользователь " . $arOrderVals["USER_ID"] . " сделал заказ ". $arOrderVals["ID"] . " от " . date("d.m.Y H:i:s"),
      );

      if($PRODUCT_ID = $el->Add($arLoadProductArray)){

      }else{
           if($file = fopen($filePath, 'a')){
               $data = print_r($el->LAST_ERROR, true);
               fwrite($file, $data);
               fclose($file);
            }
      }
  }
}



function getEntityClass($hlID)
{
    $hlblock = HL\HighloadBlockTable::getById($hlID)->fetch(); // id highload блока
    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
    return $entity->getDataClass();
}
/**
 * Список элементов hl блока
 * @param $hlID
 * @return array
 * @throws ArgumentException
 */
function getHlElementsList($hlID,$np = false)
{
    $res = getEntityClass($hlID)::getList(array(
        'order' => array('ID' => 'ASC'),
        'select' => array('*'),
    ));
    $return = [];
    while ($l = $res->fetch()) {
        if($np){
            $return[$l["UF_XML_ID"]] = $l;
        }else{
            $return[] = $l;
        }
    }
    return $return;
}

/**
 * Получаем элемент HL блока по ID
 * @param $hlId
 * @param $id
 * @return mixed
 * @throws ArgumentException
 */
function getHlElementByXmlId($hlId, $id)
{

    $res = getEntityClass($hlId)::getList(array(
        'select' => array('*'),
        'filter' => array('UF_XML_ID' => $id)
    ));

    return $res->fetch();
}

function getHlElementsByXmlId($hlId, $arXmlId)
{

    $res = getEntityClass($hlId)::getList(array(
        'order' => array('UF_SORT' => 'ASC'),
        'select' => array('*'),
        'filter' => array('UF_XML_ID' => $arXmlId)
    ));

    return $res->fetchAll();
}

function getHlElementById($hlId, $id)
{
    $res = getEntityClass($hlId)::getList(array(
        'select' => array('UF_XML_ID'),
        'filter' => array('ID' => $id)
    ));

    $result = $res->fetch();

    return $result['UF_XML_ID'];
}
function writeArrayToFile($data) {
    // Преобразуем массив в строку
    $filePath = "/home/bitrix/www/file_log.txt";
    $isNewFile = !file_exists($filePath);
    
    // Открываем файл в режиме добавления данных (или создаем новый)
    $fileHandle = fopen($filePath, 'a');
    
    if (!$fileHandle) {
        return "Ошибка при открытии файла.";
    }
    
    // Записываем дату/время
    $dateTime = date("Y-m-d H:i:s");
    fwrite($fileHandle, "Дата/время записи: " . $dateTime . PHP_EOL);
    
    // Записываем массив
    fwrite($fileHandle, print_r($data, true) . PHP_EOL . PHP_EOL);
    
    fclose($fileHandle);

    if ($isNewFile) {
        // Устанавливаем права 777 для нового файла
        chmod($filePath, 0777);
    }
}

$currentUrl = $_SERVER['REQUEST_URI'];
if (strpos($currentUrl, 'ses_tools') !== false) {
    $APPLICATION->SetPageProperty("BLOCK_IKSELECT","Y");
}
?>