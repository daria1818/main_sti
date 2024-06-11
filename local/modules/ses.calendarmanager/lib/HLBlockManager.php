<?php

namespace SES\CalendarManager;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable;
use CUserTypeEntity;
use CUserFieldEnum;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Text\Converter;

class HLBlockManager
{
    /**
     * Добавляет значения в поле типа список.
     *
     * @param int $fieldId ID поля.
     * @param array $values Значения для добавления.
     * @return void
     */
    public static function addEnumValuesToField($fieldId, $values) {
        $enum = new CUserFieldEnum();
        $enumValues = array();
        foreach ($values as $value) {
            $xmlId = \CUtil::translit($value, LANGUAGE_ID, [
                'max_len' => 100,
                'change_case' => '-',
                'replace_space' => '_',
                'replace_other' => '_',
                'delete_repeat_replace' => true,
                'use_google' => false,
            ]);
            $enumValues['n' . count($enumValues)] = [
                'VALUE' => $value,
                'XML_ID' => $xmlId,
                'DEF' => 'N',
                'SORT' => 500
            ];
        }

        $enum->SetEnumValues($fieldId, $enumValues);
    }

    /**
     * Создает новый Highload блок.
     *
     * @param string $name Название Highload блока.
     * @param string $tableName Имя таблицы Highload блока.
     * @return int ID созданного Highload блока.
     * @throws \Exception Если не удалось создать Highload блок.
     */
    public static function createHLBlock($name, $tableName) {
        $result = HighloadBlockTable::add([
            'NAME' => $name,
            'TABLE_NAME' => $tableName
        ]);

        if (!$result->isSuccess()) {
            throw new \Exception('Ошибка при создании Highload блока: ' . implode(', ', $result->getErrorMessages()));
        }

        return $result->getId();
    }

    /**
     * Добавляет поля в Highload блок.
     *
     * @param int $hlBlockId ID Highload блока.
     * @param array $fields Массив полей для добавления.
     * @return void
     * @throws \Exception Если не удалось добавить поле.
     */
    public static function addHLBlockFields($hlBlockId, $fields) {
        Loader::includeModule('highloadblock');
        $userField = new CUserTypeEntity();

        foreach ($fields as $field) {
            $editFormLabel = ['ru' => $field['title'], 'en' => $field['title_en'] ?? $field['title']];
            $listColumnLabel = ['ru' => $field['title'], 'en' => $field['title_en'] ?? $field['title']];
            $listFilterLabel = ['ru' => $field['title'], 'en' => $field['title_en'] ?? $field['title']];

            $userFieldId = $userField->Add([
                'ENTITY_ID'         => 'HLBLOCK_' . $hlBlockId,
                'FIELD_NAME'        => $field['code'],
                'XML_ID'            => $field['code'],
                'USER_TYPE_ID'      => $field['type'],
                'SORT'              => $field['sort'],
                'MULTIPLE'          => 'N',
                'MANDATORY'         => $field['mandatory'] ? 'Y' : 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'EDIT_FORM_LABEL'   => $editFormLabel,
                'LIST_COLUMN_LABEL' => $listColumnLabel,
                'LIST_FILTER_LABEL' => $listFilterLabel,
                'SETTINGS'          => $field['settings'] ?? []
            ]);

            if ($field['type'] === 'enumeration' && isset($field['values'])) {
                self::addEnumValuesToField($userFieldId, $field['values']);
            }

            if (!$userFieldId) {
                $errorMessages = $userField->LAST_ERROR;
                throw new \Exception('Ошибка при добавлении поля ' . $field['code'] . ': ' . $errorMessages);
            }
        }
    }
}
