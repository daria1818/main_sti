<?php
include_once(__DIR__ . "/../lib/HLBlockManager.php");
include_once(__DIR__ . "/../lib/CalendarLogger.php");
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Highloadblock\HighloadBlockTable;
use SES\CalendarManager\Logger;
use SES\CalendarManager\HLBlockManager;
use Bitrix\Main\DB\SqlQueryException;

Loc::loadMessages(__FILE__);

class ses_calendarmanager extends CModule {
    public $MODULE_ID = "ses.calendarmanager";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct() {
        include(__DIR__ . "/version.php");
        $this->MODULE_ID = 'ses.calendarmanager';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage("CALENDAR_MANAGER_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("CALENDAR_MANAGER_MODULE_DESC");
        $this->PARTNER_NAME = "Ses//R-top";
        $this->PARTNER_URI = "https://r-top.ru/";
    }

    public function installDB() {
        global $DB, $APPLICATION;
        Logger::log('install', 'module', 'Начало установки базы данных.', 'access');
        $connection = Application::getConnection();
        $sqlFiles = [
            'calendar_geo_city.sql',
            'calendar_geo_district.sql',
            'calendar_geo_regions.sql'
        ];

        foreach ($sqlFiles as $file) {
            $path = __DIR__ . '/db/mysql/' . $file;
            if (file_exists($path)) {
                try {
                    $DB->RunSqlBatch($path);
                    // Можно добавить логирование успешного выполнения
                    Logger::log('install', 'module', 'База данных успешно установлена.', 'access');
                } catch (SqlQueryException $e) {
                    Logger::log('install', 'module', 'Ошибка установки базы данных: ' . $e->getMessage(), 'error');
                    throw new Exception("Ошибка при выполнении файла $file: " . $e->getMessage());
                }
            } else {
                // Логирование или обработка ошибки, если файл не найден
                throw new Exception("SQL файл $file не найден.");
            }
        }
    }

    public function installEvents() {
        Logger::log('install', 'module', 'Установка обработчиков событий.', 'access');
    }

    public function installOptions() {
        Logger::log('install', 'module', 'Установка настроек модуля.', 'access');
        Option::set($this->MODULE_ID, "ID_COURSES_HL", "");
        Option::set($this->MODULE_ID, "ID_COURSES_TYPE_HL", "");
        Option::set($this->MODULE_ID, "ID_COURSES_USERS_HL", "");
        Option::set($this->MODULE_ID, "ID_COURSES_GROUP_ADMIN", "");
        Option::set($this->MODULE_ID, "ID_COURSES_GROUP_LECTOR", "");
        Option::set($this->MODULE_ID, "ID_COURSES_SCHEDULE_HL", "");
        Logger::log('install', 'module', 'Настройки модуля установлены.', 'access');
    }

    public function installUserGroups() {
        Logger::log('install', 'module', 'Установка пользовательских групп.', 'access');
        $group = new CGroup;
        $adminGroupId = $group->Add([
            "ACTIVE" => "Y",
            "C_SORT" => 100,
            "NAME" => "Админ Календаря",
            "DESCRIPTION" => "Группа администраторов календаря курсов"
        ]);
        $lectorGroupId = $group->Add([
            "ACTIVE" => "Y",
            "C_SORT" => 200,
            "NAME" => "Лектор Календаря",
            "DESCRIPTION" => "Группа лекторов календаря курсов"
        ]);

        Option::set($this->MODULE_ID, "ID_COURSES_GROUP_ADMIN", $adminGroupId);
        Option::set($this->MODULE_ID, "ID_COURSES_GROUP_LECTOR", $lectorGroupId);
    }

    public function installHLBlocks() {
        Logger::log('install', 'module', 'Установка Highload блоков.', 'access');
        Loader::includeModule('highloadblock');

        // Создание HL блока calendarmanager.courseType
        $courseTypeHLId = HLBlockManager::createHLBlock('CalendarCourseType', 'calendarmanager_coursetype');
        HLBlockManager::addHLBlockFields($courseTypeHLId, [
            ['code' => 'UF_NAME', 'type' => 'string', 'sort' => 100, 'mandatory' => true, 'title' => 'Наименование', 'title_en' => 'Name'],
            ['code' => 'UF_SORT', 'type' => 'integer', 'sort' => 200, 'mandatory' => false, 'title' => 'Сортировка', 'title_en' => 'Sorting']
        ]);

        // Создание HL блока calendarmanager.users
        $usersHLId = HLBlockManager::createHLBlock('CalendarUsers', 'calendarmanager_users');
        HLBlockManager::addHLBlockFields($usersHLId, [
            ['code' => 'UF_USER_ID', 'type' => 'employee', 'sort' => 100, 'mandatory' => true, 'title' => 'ID пользователя', 'title_en' => 'User ID'],
            ['code' => 'UF_ROLE', 'type' => 'enumeration', 'sort' => 200, 'mandatory' => true, 'title' => 'Роль', 'title_en' => 'Role', 'values' => ['Администратор', 'Лектор']],
            ['code' => 'UF_LAST_NAME', 'type' => 'string', 'sort' => 300, 'mandatory' => false, 'title' => 'Фамилия', 'title_en' => 'Last Name'],
            ['code' => 'UF_FIRST_NAME', 'type' => 'string', 'sort' => 400, 'mandatory' => false, 'title' => 'Имя', 'title_en' => 'First Name'],
            ['code' => 'UF_SORT', 'type' => 'integer', 'sort' => 500, 'mandatory' => false, 'title' => 'Сортировка', 'title_en' => 'Sorting']
        ]);

        // Создание HL блока calendarmanager.courses
        $coursesHLId = HLBlockManager::createHLBlock('CalendarCourses', 'calendarmanager_courses');
        HLBlockManager::addHLBlockFields($coursesHLId, [
            ['code' => 'UF_TYPE', 'type' => 'enumeration', 'sort' => 100, 'mandatory' => true, 'title' => 'Тип курса', 'title_en' => 'Course Type', 'values' => ['Индивидуальный', 'Групповой']],
            ['code' => 'UF_CITY', 'type' => 'string', 'sort' => 200, 'mandatory' => false, 'title' => 'Город', 'title_en' => 'City'],
            ['code' => 'UF_DATE', 'type' => 'datetime', 'sort' => 300, 'mandatory' => true, 'title' => 'Дата начала', 'title_en' => 'Event Date Start'],
            ['code' => 'UF_DATE_END', 'type' => 'datetime', 'sort' => 300, 'mandatory' => true, 'title' => 'Дата окончания', 'title_en' => 'Event Date End'],
            ['code' => 'UF_SORT', 'type' => 'integer', 'sort' => 400, 'mandatory' => false, 'title' => 'Сортировка', 'title_en' => 'Sorting'],
            ['code' => 'UF_NAME', 'type' => 'string', 'sort' => 500, 'mandatory' => true, 'title' => 'Наименование', 'title_en' => 'Name'],
            ['code' => 'UF_DESCRIPTION', 'type' => 'string', 'sort' => 500, 'mandatory' => false, 'title' => 'Описание курса', 'title_en' => 'Descripton course'],
            ['code' => 'UF_LOCATION', 'type' => 'string', 'sort' => 600, 'mandatory' => false, 'title' => 'Локация', 'title_en' => 'Location'],
            ['code' => 'UF_ADDRESS', 'type' => 'string', 'sort' => 700, 'mandatory' => false, 'title' => 'Адрес локации', 'title_en' => 'Location Address'],
            ['code' => 'UF_LECTOR', 'type' => 'hlblock', 'sort' => 800, 'mandatory' => true, 'title' => 'Лектор', 'title_en' => 'Lecturer', 'settings' => ['HLBLOCK_ID' => $usersHLId, 'HLFIELD_ID' => 'USER_FIELD_ID']],
            ['code' => 'UF_TICKETS', 'type' => 'integer', 'sort' => 900, 'mandatory' => true, 'title' => 'Кол-во билетов', 'title_en' => 'Num of Tickets'],
            ['code' => 'UF_TICKETS_BASE', 'type' => 'integer', 'sort' => 900, 'mandatory' => true, 'title' => 'Кол-во билетов(изначальное)', 'title_en' => 'Num of Tickets (def)'],
            ['code' => 'UF_LINK', 'type' => 'string', 'sort' => 1000, 'mandatory' => false, 'title' => 'Ссылка на форму', 'title_en' => 'Form Link'],
            ['code' => 'UF_CREATOR', 'type' => 'hlblock', 'sort' => 1100, 'mandatory' => true, 'title' => 'Создатель курса', 'title_en' => 'Course Creator', 'settings' => ['HLBLOCK_ID' => $usersHLId, 'HLFIELD_ID' => 'USER_FIELD_ID']],
            ['code' => 'UF_DATE_CREATE', 'type' => 'datetime', 'sort' => 1200, 'mandatory' => true, 'title' => 'Дата создания', 'title_en' => 'Creation Date'],
            ['code' => 'UF_CRM_EVENT_CALENDAR', 'type' => 'integer', 'sort' => 900, 'mandatory' => false, 'title' => 'Ивент в календаре ЦРМ', 'title_en' => 'Event on calendar CRM'],
        ]);

         // Создание HL блока calendarmanager.lecturerSchedule
        $lecturerScheduleHLId = HLBlockManager::createHLBlock('CalendarLecturerSchedule', 'calendarmanager_lecturerschedule');
        HLBlockManager::addHLBlockFields($lecturerScheduleHLId, [
            ['code' => 'UF_LECTURER_ID', 'type' => 'hlblock', 'sort' => 100, 'mandatory' => true, 'title' => 'ID лектора', 'title_en' => 'Lecturer ID', 'settings' => ['HLBLOCK_ID' => $usersHLId, 'HLFIELD_ID' => 'USER_FIELD_ID']],
            ['code' => 'UF_DAY_AVAILABLE', 'type' => 'enumeration', 'sort' => 200, 'mandatory' => true, 'title' => 'Доступность дня', 'title_en' => 'Day Availability', 'values' => ['Y', 'N', 'D']],
            ['code' => 'UF_DATE', 'type' => 'date', 'sort' => 300, 'mandatory' => true, 'title' => 'Дата', 'title_en' => 'Date'],
            ['code' => 'UF_SORT', 'type' => 'integer', 'sort' => 400, 'mandatory' => false, 'title' => 'Сортировка', 'title_en' => 'Sorting'],
            ['code' => 'UF_DATE_MODIFIED', 'type' => 'datetime', 'sort' => 500, 'mandatory' => false, 'title' => 'Дата изменения', 'title_en' => 'Modification Date'],
            ['code' => 'UF_DATE_ADDED', 'type' => 'datetime', 'sort' => 600, 'mandatory' => false, 'title' => 'Дата добавления', 'title_en' => 'Addition Date']
        ]);

        Option::set($this->MODULE_ID, "ID_COURSES_TYPE_HL", $courseTypeHLId);
        Option::set($this->MODULE_ID, "ID_COURSES_USERS_HL", $usersHLId);
        Option::set($this->MODULE_ID, "ID_COURSES_HL", $coursesHLId);
        Option::set($this->MODULE_ID, "ID_COURSES_SCHEDULE_HL", $lecturerScheduleHLId);
    }


    public function DoInstall() {
        global $APPLICATION;
        Logger::log('install', 'module', 'Начало установки модуля.', 'access');
        global $DB;
        $DB->StartTransaction();
        try {
            Loader::includeModule('highloadblock');
            $this->installDB();
            $this->installEvents();
            $this->installOptions();
            $this->installUserGroups();
            $this->installHLBlocks();
            $DB->Commit();
            Logger::log('install', 'module', 'Модуль успешно установлен.', 'access');
            ModuleManager::registerModule($this->MODULE_ID);
            // для успешного завершения, метод должен вернуть true
            return true;
        } catch (\Exception $e) {
            Logger::log('install', 'module', 'Ошибка установки модуля: ' . $e->getMessage(), 'error');
            $DB->Rollback();
            throw $e;
        }
    }

    public function UnInstallDB() {
        global $DB, $APPLICATION;
        Logger::log('uninstall', 'module', 'Начало удаления базы данных.', 'access');
        
        $tables = [
            'calendar_geo_city',
            'calendar_geo_district',
            'calendar_geo_regions'
        ];

        foreach ($tables as $table) {
            try {
                $DB->Query("DROP TABLE IF EXISTS {$table}", true);
                Logger::log('uninstall', 'module', "Таблица {$table} успешно удалена.", 'access');
            } catch (Exception $e) {
                Logger::log('uninstall', 'module', "Ошибка удаления таблицы {$table}: " . $e->getMessage(), 'error');
                $APPLICATION->ThrowException("Ошибка при удалении таблицы {$table}: " . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }

    public function DoUninstall() {
        Logger::log('uninstall', 'Module', 'Начало удаления модуля.', 'access');
        global $DB;
        $DB->StartTransaction();
        try {
            Loader::includeModule('highloadblock');

            $coursesHLId = Option::get($this->MODULE_ID, "ID_COURSES_HL");
            if (!empty($coursesHLId)) {
                HighloadBlockTable::delete($coursesHLId);
            }
            $courseTypeHLId = Option::get($this->MODULE_ID, "ID_COURSES_TYPE_HL");
            if (!empty($courseTypeHLId)) {
                HighloadBlockTable::delete($courseTypeHLId);
            }
            $usersHLId = Option::get($this->MODULE_ID, "ID_COURSES_USERS_HL");
            if (!empty($usersHLId)) {
                HighloadBlockTable::delete($usersHLId);
            }

            $scheduleHLId = Option::get($this->MODULE_ID, "ID_COURSES_SCHEDULE_HL");
            if (!empty($scheduleHLId)) {
                HighloadBlockTable::delete($scheduleHLId);
            }

            $adminGroupId = Option::get($this->MODULE_ID, "ID_COURSES_GROUP_ADMIN");
            if (!empty($adminGroupId)) {
                CGroup::Delete($adminGroupId);
            }
            $lectorGroupId = Option::get($this->MODULE_ID, "ID_COURSES_GROUP_LECTOR");
            if (!empty($lectorGroupId)) {
                CGroup::Delete($lectorGroupId);
            }
            $this->UnInstallDB();
            Option::delete($this->MODULE_ID);

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $DB->Commit();
            Logger::log('uninstall', 'Module', 'Модуль успешно удален.', 'access');
        } catch (\Exception $e) {
            Logger::log('uninstall', 'module', 'Ошибка установки модуля: ' . $e->getMessage(), 'error');
            $DB->Rollback();
            throw $e;
        }
    }
}