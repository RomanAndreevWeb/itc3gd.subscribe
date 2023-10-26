<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);
class itc3gd_subscribe extends CModule
{
    // переменные модуля
    public  $MODULE_ID;
    public  $MODULE_VERSION;
    public  $MODULE_VERSION_DATE;
    public  $MODULE_NAME;
    public  $MODULE_DESCRIPTION;
    public  $errors;
    public function __construct()
    {
        $arModuleVersion = array();
        include_once(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_ID = Loc::getMessage("MODULE_ID");
        $this->MODULE_NAME = Loc::getMessage("MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MODULE_DESCRIPTION");
    }
    public function DoInstall()
    {
        // глобальная переменная с обстрактным классом
        global $APPLICATION;
        // проверка версии Битрикс
        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            // копируем файлы, необходимые для компонентов
            $this->InstallFiles();
            $this->InstallDB();
            // регистрируем модуль в системе
            ModuleManager::registerModule($this->MODULE_ID);
        } else {
            // если версия битрикс меньше 14, значит выводим сообщение
            $APPLICATION->ThrowException(
                Loc::getMessage("INSTALL_ERROR")
            );
        }

        // для успешного завершения, метод должен вернуть true
        return true;
    }
    // метод отрабатывает при удалении модуля
    public function DoUninstall()
    {
        // глобальная переменная с обстрактным классом
        global $APPLICATION;
        // удаляем файлы, необходимые для работы модуля
        $this->UnInstallFiles();
        $this->UninstallDB();
        // удаляем регистрацию модуля в системе
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }
    // метод для копирования файлов модуля при установке
    public function InstallFiles()
    {
        // копируем файлы, которые устанавливаем вместе с модулем, копируем в пространство имен для компонентов которое будет иметь имя модуля hmarketing.7d
        CopyDirFiles(
            __DIR__ . '/components',
            Application::getDocumentRoot() . '/bitrix/components/' . $this->MODULE_ID . '/',
            true,
            true
        );
        // для успешного завершения, метод должен вернуть true
        return true;
    }
    // метод для удаления файлов модуля при удалении
    public function UnInstallFiles()
    {
        // удаляем директорию по указанному пути до папки
        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/bitrix/components/' . $this->MODULE_ID
        );
        // для успешного завершения, метод должен вернуть true
        return true;
    }

    public function InstallDB()
    {
        Loader::includeModule('highloadblock');
        require_once __DIR__ . '/../lib/helpers/HlblockHelper.php';

        $data = array(
            'NAME' => \Itc3gd\HlblockHelper::getHlblockName(),
            'TABLE_NAME' => \Itc3gd\HlblockHelper::getHlblockNameTable(),
        );

        $result = \Bitrix\Highloadblock\HighloadBlockTable::add($data);

        if ($result->isSuccess()) {
            $highloadBlockId = $result->getId();

            $fieldData = array(
                'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
                'FIELD_NAME' => 'UF_EMAIL',
                'USER_TYPE_ID' => 'string',
                'SORT' => 100,
                'MANDATORY' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                "EDIT_FORM_LABEL" => array('ru' => 'Email', 'en' => 'Email'),
                "LIST_COLUMN_LABEL" => array('ru' => 'Email', 'en' => 'Email'),
                "LIST_FILTER_LABEL" => array('ru' => 'Email', 'en' => 'Email'),
            );

            $obUserField = new \CUserTypeEntity();
            $obUserField->Add($fieldData);
        } else {
            $errors = $result->getErrorMessages();
        }
    }

    public function UninstallDB()
    {
        Loader::includeModule('highloadblock');
        require_once __DIR__ . '/../lib/helpers/HlblockHelper.php';

        $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
            'filter' => array('NAME' => \Itc3gd\HlblockHelper::getHlblockName()),
        ))->fetch();

        if ($hlBlock) {
            $result = \Bitrix\Highloadblock\HighloadBlockTable::delete($hlBlock['ID']);

            if (!$result->isSuccess()) {
                $errors = $result->getErrorMessages();
            }
        }

        $fieldId = CUserTypeEntity::GetList(array(), array(
            'ENTITY_ID' => 'HLBLOCK_' . $hlBlock['ID'],
            'FIELD_NAME' => 'UF_EMAIL',
        ))->Fetch();

        if ($fieldId) {
            $result = \CUserTypeEntity::Delete($fieldId['ID']);
        }
    }
}