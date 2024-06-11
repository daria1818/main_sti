<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 3) . '';
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

set_time_limit(0);
error_reporting(E_ERROR | E_STRICT);

$sUploadDir = $_SERVER["DOCUMENT_ROOT"].'/upload';
$nDate = strtotime(date('d.m.Y').' 00:00:00');

foreach(glob($sUploadDir.'/1c_catalog*', GLOB_ONLYDIR) as $sItem){
    if($sItem == $sUploadDir.'/1c_catalog' || $sItem == $sUploadDir.'/1c_catalog_copy_askaron_pro1c'){
        // стартовую папку не трогаем
        continue;
    }

    if(!is_dir($sItem)){
        // если не папка - не трогаем
        continue;
    }

    $nFileMTime = filemtime($sItem);
    if($nFileMTime > $nDate){
        // пропускаем папки по дате изменения
        continue;
    }

    recursiveRemoveDir($sItem);
    echo date('d.m.Y H:i:s').' - Removed '.$sItem.' (mtime '.date('d.m.Y H:i:s', $nFileMTime).')<br>'.PHP_EOL;
    echo '<br>'.PHP_EOL;
}

/**
 * удаляет папку с содержимым
 * @param $dir
 */
function recursiveRemoveDir($dir) {

    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if(trim($entry) == '.' || trim($entry) == '..'){
                continue;
            }

            if(is_dir($dir.'/'.$entry)) {
                recursiveRemoveDir($dir.'/'.$entry);
            }else{
                if(unlink($dir.'/'.$entry)){
                    echo date('d.m.Y H:i:s').' - unlink '.$dir.'/'.$entry.'<br>'.PHP_EOL;
                }
            }
        }
        closedir($handle);
        if(rmdir($dir)){
            echo date('d.m.Y H:i:s').' - rmdir '.$dir.'<br>'.PHP_EOL;
        }
    }
}
