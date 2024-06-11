<?
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\IO\File;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\EventManager;

global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall) - strlen("/index.php"));
Loc::loadMessages($PathInstall . "/install.php");

if(class_exists("rtop_kpi")) return;
class rtop_kpi extends CModule
{   
    var $MODULE_ID = "rtop.kpi";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    function rtop_kpi()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("rtop.kpi_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("rtop.kpi_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("rtop.kpi_PARTNER_NAME"); 
        $this->PARTNER_URI = Loc::getMessage("rtop.kpi_PARTNER_URI");
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
            RegisterModule("rtop.kpi");
            $APPLICATION->IncludeAdminFile(Loc::getMessage("RT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/step.php");
        }
        else
        {
            $APPLICATION->ThrowException(
                Loc::getMessage("RT_INSTALL_ERROR_VERSION")
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
        UnRegisterModule("rtop.kpi");
        $APPLICATION->IncludeAdminFile(Loc::getMessage("RT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/unstep.php");


        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/install/components",  
            Application::getDocumentRoot()."/bitrix/components/", true, true);

        CopyDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/install/admin", 
            Application::getDocumentRoot()."/bitrix/admin", true, true);
        
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(
            Application::getDocumentRoot()."/".$this->DIR."/modules/".$this->MODULE_ID."/install/admin", 
            Application::getDocumentRoot()."/bitrix/admin");
    }

    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/db/mysql/install.sql");
        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }
        return true;
    }

    function UnInstallDB(){
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/".$this->DIR."/modules/".$this->MODULE_ID."/install/db/mysql/uninstall.sql");
        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }
        return true;
    }

    function InstallEvents(){
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandlerCompatible(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'Rtop\KPI\Admin\Menu',
            'add'
        );
    }

    function UnInstallEvents(){
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'Rtop\KPI\Admin\Menu',
            'add'
        );
    }
}

?>