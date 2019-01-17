<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;
Loc::loadMessages(__FILE__); 
try
{
	if (!Main\Loader::includeModule('main'))
		throw new Main\LoaderException(Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_MAIN_MODULE_NOT_INSTALLED'));
	
	
	
	$sortFields = array(
		'ID' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_ID'),
		'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_NAME'),
		'ACTIVE_FROM' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_ACTIVE_FROM'),
		'SORT' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_SORT')
	);
	
	$sortDirection = array(
		'ASC' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_ASC'),
		'DESC' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_DESC')
	);
	
	$arComponentParameters = array(
		'GROUPS' => array(
		),
		'PARAMETERS' => array(
		    'SHOW_NAV' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SHOW_NAV'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N'
			),
			'COUNT' =>  array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_COUNT'),
				'TYPE' => 'STRING',
				'DEFAULT' => '0'
			),
			'SORT_FIELD1' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_FIELD1'),
				'TYPE' => 'LIST',
				'VALUES' => $sortFields
			),
			'SORT_DIRECTION1' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_DIRECTION1'),
				'TYPE' => 'LIST',
				'VALUES' => $sortDirection
			),
			'SORT_FIELD2' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_FIELD2'),
				'TYPE' => 'LIST',
				'VALUES' => $sortFields
			),
			'SORT_DIRECTION2' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_LIST_PARAMETERS_SORT_DIRECTION2'),
				'TYPE' => 'LIST',
				'VALUES' => $sortDirection
			),
			'CACHE_TIME' => array(
				'DEFAULT' => 3600
			)
		)
	);
}
catch (Main\LoaderException $e)
{
	ShowError($e->getMessage());
}
?>