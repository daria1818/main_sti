<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$oUserTypeEntity    = new CUserTypeEntity();
$aUserFields    = array(
	'ENTITY_ID'         => 'USER',
	'FIELD_NAME'        => "UF_NEW_PROP22",
	'USER_TYPE_ID'      => 'string',
	'XML_ID'            => "UF_NEW_PROP22",
	'SORT'              => 500,
	'MULTIPLE'          => 'N',
	'MANDATORY'         => 'N',
	'SHOW_FILTER'       => 'N',
	'SHOW_IN_LIST'      => '',
	'EDIT_IN_LIST'      => '',
	'IS_SEARCHABLE'     => 'N',
	'SETTINGS'          => array(
		'DEFAULT_VALUE' => '',
		'SIZE'          => '20',
		'ROWS'          => '1',
		'MIN_LENGTH'    => '0',
		'MAX_LENGTH'    => '0',
		'REGEXP'        => '',
	),
	'EDIT_FORM_LABEL'   => array(
		'ru'    => "UF_NEW_PROP22",
		'en'    => "UF_NEW_PROP22",
	),
	'LIST_COLUMN_LABEL' => array(
		'ru'    => "UF_NEW_PROP22",
		'en'    => "UF_NEW_PROP22",
	),
	'LIST_FILTER_LABEL' => array(
		'ru'    => "UF_NEW_PROP22",
		'en'    => "UF_NEW_PROP22",
	),
	'ERROR_MESSAGE'     => array(
		'ru'    => "Ошибка при заполнении" . "UF_NEW_PROP22",
		'en'    => "An error in completing the " . "UF_NEW_PROP22",
	),
	'HELP_MESSAGE'      => array(
		'ru'    => '',
		'en'    => '',
	),
);
$iUserFieldId = $oUserTypeEntity->Add($aUserFields); // int

echo "<pre>";
print_r($oUserTypeEntity->Add($aUserFields));
echo "</pre>";
?>