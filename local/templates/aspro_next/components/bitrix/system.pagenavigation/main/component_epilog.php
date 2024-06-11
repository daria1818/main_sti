<?
if(isset($templateData["ERROR_404"]) && $templateData["ERROR_404"]){
    if (!defined("ERROR_404"))
    {
        define("ERROR_404", "Y");
        \CHTTP::setStatus("404 Not Found");
    }
}
    