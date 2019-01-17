<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc as Loc;
Loc::loadMessages(__FILE__);
$arComponentDescription = array(
	"NAME" => Loc::getMessage('STANDARD_ELEMENTS_LIST_DESCRIPTION_NAME'),
	"DESCRIPTION" => Loc::getMessage('STANDARD_ELEMENTS_LIST_DESCRIPTION_DESCRIPTION'),
	"ICON" => '/images/icon.gif',
	"SORT" => 20,
	"PATH" => array(
		"ID" => 'users.list',
		"NAME" => Loc::getMessage('USERS_LIST_DESCRIPTION'),
		"SORT" => 10,
		"CHILD" => array(
			"ID" => 'standard',
			"NAME" => Loc::getMessage('USERS_LIST_DESCRIPTION_DIR'),
			"SORT" => 10
		)
	),
);
?>