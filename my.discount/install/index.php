<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class my_discount extends CModule{

	public function __construct(){

		if(file_exists(__DIR__."/version.php")){

			$arModuleVersion = array();

			include_once(__DIR__."/version.php");

			$this->MODULE_ID 		   = str_replace("_", ".", get_class($this));
			$this->MODULE_VERSION 	   = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
			$this->MODULE_NAME 		   = Loc::getMessage("DISCOUNT_NAME");
			$this->MODULE_DESCRIPTION  = Loc::getMessage("DISCOUNT_DESCRIPTION");
			
		}

		return false;
	}

	public function DoInstall(){

		global $APPLICATION;

		if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){

			$this->InstallFiles();
			$this->InstallDB();

			ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallEvents();
		}else{

			$APPLICATION->ThrowException(
				Loc::getMessage("DISCOUNT_INSTALL_ERROR_VERSION")
			);
		}

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage("DISCOUNT_INSTALL_TITLE")." \"".Loc::getMessage("DISCOUNT_NAME")."\"",
			__DIR__."/step.php"
		);

		return false;
	}
	public function InstallFiles(){

		CopyDirFiles(
			__DIR__."/assets/scripts",
			Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID."/",
			true,
			true
		);

		CopyDirFiles(
			__DIR__."/assets/styles",
			Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID."/",
			true,
			true
		);

		return false;
	}

	public function InstallDB(){

		return false;
	}

	public function InstallEvents(){

		/*EventManager::getInstance()->registerEventHandler(
			"main",
			"OnBeforeEndBufferContent",
			$this->MODULE_ID,
			"Falbar\ToTop\Main",
			"appendScriptsToPage"
		);*/

		return false;
	}

	public function DoUninstall(){

		global $APPLICATION;

		$this->UnInstallFiles();
		$this->UnInstallDB();
		$this->UnInstallEvents();

		ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage("DISCOUNT_UNINSTALL_TITLE")." \"".Loc::getMessage("DISCOUNT_NAME")."\"",
			__DIR__."/unstep.php"
		);

		return false;
	}
	public function UnInstallFiles(){

		Directory::deleteDirectory(
			Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID
		);

		Directory::deleteDirectory(
			Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID
		);

		return false;
	}

	public function UnInstallDB(){

		Option::delete($this->MODULE_ID);

		return false;
	}

	public function UnInstallEvents(){

	

		return false;
	}
}
?>