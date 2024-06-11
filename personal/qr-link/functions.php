<?
use Bitrix\Highloadblock as HL;
CModule::IncludeModule('highloadblock');

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

$arHL = getHlElementsList(31);

?>