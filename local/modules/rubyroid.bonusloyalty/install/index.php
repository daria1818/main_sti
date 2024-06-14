<?
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\IO\File;
use \Bitrix\Main\Config\Option;

global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall) - strlen("/index.php"));
Loc::loadMessages($PathInstall . "/install.php");

if(class_exists("rubyroid_bonusloyalty")) return;
class rubyroid_bonusloyalty extends CModule
{   
    var $MODULE_ID = "rubyroid.bonusloyalty";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("rubyroid_bonusloyalty_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("rubyroid.bonusloyalty_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("rubyroid.bonusloyalty_PARTNER_NAME"); 
        $this->PARTNER_URI = Loc::getMessage("rubyroid.bonusloyalty_PARTNER_URI");
        $this->DIR = (preg_match("/\/local\//", __DIR__) ? "local" : "bitrix");
    }
    function DoInstall()
    {
        global $APPLICATION;
        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00"))
        {
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
            RegisterModule("rubyroid.bonusloyalty");
            $APPLICATION->IncludeAdminFile(Loc::getMessage("RB_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/step.php");
        }
        else
        {
            $APPLICATION->ThrowException(
                Loc::getMessage("RB_INSTALL_ERROR_VERSION")
            );
        }
        return true;
    }
 
    function DoUninstall()
    {
        global $APPLICATION;
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
        UnRegisterModule("rubyroid.bonusloyalty");
        $APPLICATION->IncludeAdminFile(Loc::getMessage("RB_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/unstep.php");


        return true;
    }

    function InstallFiles()
    {

        CopyDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/install/components",  
            Application::getDocumentRoot()."/bitrix/components/", true, true);
        CopyDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/innerbonus",  
            Application::getDocumentRoot()."/".$this->DIR."/php_interface/include/sale_payment/innerbonus", true, true);
        CopyDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/images/innerbonus.png", 
            Application::getDocumentRoot()."/bitrix/images/sale/sale_payments/innerbonus.png");
        
        return true;
    }

    function UnInstallFiles()
    {
        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/components/rubyroid"
        );
    }

    function InstallDB(){
        return false;
    }

    function UnInstallDB(){
        Option::delete($this->MODULE_ID);
        return false;
    }

    function InstallEvents(){
        return false;
    }

    function UnInstallEvents(){
        return false;
    }
}

?>