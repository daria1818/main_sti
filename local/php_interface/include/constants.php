<?

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');

class Constants
{
    static $arDefined = array();

    public function __construct()
    {
        try{

            static::defineIblocksConstants();
            static::defineHLConstants();
            static::defineGroupsConstants();

        }catch (\Exception $exception){

        }

    }

    /**
     * @param int $cacheTime
     * @throws Exception
     */
    public static function defineGroupsConstants($cacheTime = 86400*7) {

        if( array_key_exists(__METHOD__, static::$arDefined) ) {
            return false;
        }

        $obCache = Cache::createInstance();
        $cacheId = md5(__METHOD__);

        if( $obCache->initCache($cacheTime, $cacheId, 'Globals') ) {

            $arGroups = $obCache->getVars();

        } elseif( $obCache->startDataCache() ) {

            $arGroups = array();
            $rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), array()); // выбираем группы
            while ($group = $rsGroups->fetch()) {
                $arGroups[] = $group;
            }

            $obCache->endDataCache($arGroups);
        }

        foreach ($arGroups as $arGroup) {

            $arGroup['CODE'] = strtoupper(trim($arGroup['STRING_ID']));

            if ($arGroup['CODE']) {

                $const = 'CONST_GROUP_ID_' . $arGroup['CODE'];

                if (!defined($const)) {
                    /**
                     * @ignore
                     */
                    define($const, $arGroup['ID']);
                }
            }
        }

        self::$arDefined[__METHOD__] = true;

        return true;
    }

    /**
     * Define iblocks constants
     *
     * @param int $cacheTime
     *
     * @return bool
     * @throws Exception
     */
    public static function defineIblocksConstants($cacheTime = 86400)
    {
        if( array_key_exists(__METHOD__, static::$arDefined) ) {
            return false;
        }

        $obCache = Cache::createInstance();
        $cacheId = md5(__METHOD__);
        if( $obCache->initCache($cacheTime, $cacheId, 'Globals') ) {
            $arIblocks = $obCache->getVars();
        }
        elseif( $obCache->startDataCache() ) {
            $arIblocks = array();

            $rsIblocks = IblockTable::getList(
                array(
                    'filter' => array(
                        'ACTIVE' => 'Y',
                    )
                )
            );

            while ($iblock = $rsIblocks->fetch()) {
                $arIblocks[] = $iblock;
            }

            $obCache->endDataCache($arIblocks);
        }

        foreach ($arIblocks as $arIblock) {


            $arIblock['CODE'] = strtoupper(trim($arIblock['CODE']));
            if ($arIblock['CODE']) {
                $const = 'CONST_IBLOCK_ID_' . $arIblock['CODE'];

                if (!defined($const)) {
                    /**
                     * @ignore
                     */
                    define($const, $arIblock['ID']);
                }
            }
        }

        self::$arDefined[__METHOD__] = true;

        return true;
    }

    /**
     * Define HLoadBlocks constants
     *
     * @param int $cacheTime - cache time
     *
     * @return bool
     * @throws Exception
     */
    public static function defineHLConstants($cacheTime = 86400)
    {
        if( array_key_exists(__METHOD__, static::$arDefined) ) {
            return false;
        }

        if (!Loader::includeModule('highloadblock')) {
            throw new Exception("Highloadblock module is't installed.");
        }

        $obCache = Cache::createInstance();
        $cacheId = md5(__METHOD__);

        if ($obCache->initCache($cacheTime, $cacheId, 'Globals')) {
            $arHloads = $obCache->getVars();
        } elseif($obCache->startDataCache()) {
            $arHloads = array();
            $rsHloads = HighloadBlockTable::getList(
                array(
                    'select' => array(
                        'ID',
                        'NAME',
                    )
                )
            );

            while($arHload = $rsHloads->fetch()) {
                $arHloads[] = array(
                    "ID" => $arHload["ID"],
                    "CODE" => $arHload["NAME"]
                );
            }

            $obCache->endDataCache($arHloads);
        }

        foreach ($arHloads as $arHload) {
            $arHload['CODE'] = strtoupper(trim($arHload['CODE']));
            if ($arHload['CODE']) {
                $const = 'CONST_HL_ID_' . $arHload['CODE'];
                if (!defined($const)) {
                    define($const, $arHload['ID']);
                }
            }

            $const = 'CONST_HL_CODE_' . $arHload['ID'];
            if (!defined($const)) {
                define($const, $arHload['CODE']);
            }
        }

        self::$arDefined[__METHOD__] = true;

        return true;
    }

}

$obConstants = new Constants();


