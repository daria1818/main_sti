<?php

namespace SES\CalendarManager;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

class CalendarUsers
{
    private $hlblockId;
    private $roleFieldCode = 'UF_ROLE';

    /**
     * Конструктор класса.
     * Инициализирует идентификатор highload блока и загружает модули.
     *
     * @throws \Exception Если не удалось подключить модули highloadblock или iblock.
     */
    public function __construct()
    {
        // Получаем ID HL-блока из настроек модуля
        $this->hlblockId = (int)Option::get('ses.calendarmanager', 'ID_COURSES_USERS_HL');

        // Проверяем, подключен ли модуль highloadblock
        try {
            Loader::includeModule('highloadblock');
            Loader::includeModule('iblock');
        } catch (LoaderException $e) {
            throw new \Exception("Ошибка подключения модуля highloadblock или iblock: " . $e->getMessage());
        }
    }

    /**
     * Получение объекта HL-блока для выполнения запросов.
     * 
     * @return Entity\DataManager|null
     */
    private function getEntity()
    {
        $hlblock = HL\HighloadBlockTable::getById($this->hlblockId)->fetch();
        if ($hlblock) {
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            return $entity->getDataClass();
        }

        return null;
    }

    /**
     * Получение списка всех пользователей.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getAllUsers()
    {
        $entityClass = $this->getEntity();
        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        return $entityClass::getList([
            'select' => ['*']
        ])->fetchAll();
    }

    /**
     * Получение идентификаторов значений ролей.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getRoleIds()
    {
        $entityClass = $this->getEntity();
        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        $enum = new \CUserFieldEnum();
        $roleEnums = [];
        $rsEnum = $enum->GetList([], ['USER_FIELD_NAME' => $this->roleFieldCode]);
        
        while ($role = $rsEnum->Fetch()) {
            $roleEnums[$role['VALUE']] = (int)$role['ID'];
        }

        return $roleEnums;
    }

    /**
     * Получение списка пользователей по роли.
     * 
     * @param string $roleName Название роли.
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден или роль не найдена.
     */
    public function getUsersByRoleName($roleName)
    {
        $roleIds = $this->getRoleIds();
        if (!array_key_exists($roleName, $roleIds)) {
            throw new \Exception("Роль с названием '$roleName' не найдена.");
        }

        $roleId = $roleIds[$roleName];
        $entityClass = $this->getEntity();
        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        $users = $entityClass::getList([
            'select' => ['*'],
            'filter' => ['=' . $this->roleFieldCode => $roleId]
        ])->fetchAll();

        // Преобразование массива для использования ID в качестве ключа
        $result = [];
        foreach ($users as $user) {
            $result[$user['ID']] = $user;
        }

        return $result;
    }


    /**
     * Получение списка пользователей-админов.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getAdminUsers()
    {
        return $this->getUsersByRoleName('Администратор');
    }

    /**
     * Получение списка пользователей-лекторов.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getLecturerUsers()
    {
        return $this->getUsersByRoleName('Лектор');
    }

    /**
     * Проверяет, имеет ли текущий пользователь доступ, исходя из его группы и присутствия в HL-блоке.
     * Возвращает массив с данными о доступе, роли и ФИО пользователя.
     *
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function checkCurrentUserAccessAndRole()
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            return ['ACCESS' => 'N', 'ROLE' => null, 'NAME' => null, 'TYPE' => 'Need Auth'];
        }

        $userId = $USER->GetID();
        $entityClass = $this->getEntity();

        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        // Проверяем, есть ли пользователь в HL-блоке и получаем его данные
        $userRecord = $entityClass::getList([
            'select' => ['*'],
            'filter' => ['=UF_USER_ID' => $userId]
        ])->fetch();

        if (!$userRecord) {
            return ['ACCESS' => 'N', 'ROLE' => null, 'NAME' => null, 'TYPE' => 'The user is not in the HL'];
        }

        $userGroups = $USER->GetUserGroupArray();
        $adminGroupId = Option::get('ses.calendarmanager', 'ID_COURSES_GROUP_ADMIN');
        $lectorGroupId = Option::get('ses.calendarmanager', 'ID_COURSES_GROUP_LECTOR');

        // Проверяем, входит ли пользователь в нужные группы
        $isInRequiredGroup = in_array($adminGroupId, $userGroups) || in_array($lectorGroupId, $userGroups);

        if (!$isInRequiredGroup) {
            return ['ACCESS' => 'N', 'ROLE' => null, 'NAME' => null, 'TYPE' => 'User is not in the calendar group'];
        }

        // Формирование результата
        $role = $this->getRoleNameById($userRecord['UF_ROLE']);
        $name = $userRecord['UF_FIRST_NAME'] . " " . $userRecord['UF_LAST_NAME'];
        $user_id = $userRecord["ID"];
        $bx_user_id = $userRecord["UF_USER_ID"];

        return [
            'ACCESS' => 'Y',
            'MODULE_ID' => $user_id,
            'ROLE' => $role,
            'NAME' => $name,
            'BX_ID' => $bx_user_id,
        ];
    }

    /**
     * Получает идентификатор пользователя в модуле по его Bitrix ID.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getCurUserModuleID()
    {
        global $USER;

        $userId = $USER->GetID();
        $entityClass = $this->getEntity();

        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        // Проверяем, есть ли пользователь в HL-блоке и получаем его данные
        $UFuserID = $entityClass::getList([
            'select' => ['ID'],
            'filter' => ['=UF_USER_ID' => $userId]
        ])->fetch();

        return $UFuserID;
    }

    /**
     * Получает всю инфу пользователя в модуле по его MODULE ID.
     * 
     * @return array
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function getCurUserModuleAr($ID)
    {

        $entityClass = $this->getEntity();

        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        // Проверяем, есть ли пользователь в HL-блоке и получаем его данные
        $UserInfo = $entityClass::getList([
            'select' => ['*'],
            'filter' => ['=ID' =>$ID]
        ])->fetch();

        return $UserInfo;
    }

     public function getUserIDviaModuleID($id)
    {
        $entityClass = $this->getEntity();

        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        // Проверяем, есть ли пользователь в HL-блоке и получаем его данные
        $BXuserID = $entityClass::getList([
            'select' => ['ID','UF_USER_ID'],
            'filter' => ['=ID' => $id]
        ])->fetch();

        return $BXuserID;
    }

    /**
     * Получает название роли по ID.
     *
     * @param int $roleId ID роли.
     * @return string|null Название роли или false, если не найдено.
     */
    private function getRoleNameById($roleId)
    {
        $roleIds = $this->getRoleIds();
        foreach ($roleIds as $roleName => $id) {
            if ($id == $roleId) {
                return $roleName;
            }
        }
        return false;
    }

    /**
     * Добавление лектора в HL-блок.
     *
     * @param array $fields Массив с данными пользователя.
     * @return \Bitrix\Main\Entity\AddResult
     * @throws \Exception Если HL-блок с указанным ID не найден.
     */
    public function addLectorToHL($fields)
    {
        $entityClass = $this->getEntity();
        if (!$entityClass) {
            throw new \Exception('HL-блок с указанным ID не найден.');
        }

        $result = $entityClass::add($fields);
        return $result;
    }
}
