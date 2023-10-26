<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class SubscribeOnNews extends CBitrixComponent
{
    private function checkModules()
    {
        if (!Loader::includeModule("highloadblock")) {
            throw new \Exception('Не загружены модули необходимые для работы модуля');
        }
    }

    public function addEmail(int $hlblockId, string $email)
    {
        $this->checkModules();

        $fields = [
            "UF_EMAIL" => $email
        ];

        if ($this->isEmailExists($hlblockId, $email)) {
            return false;
        }

        return $this->addEntryToHighloadBlock($hlblockId, $fields);
    }

    public function isEmailExists(int $hlblockId, string $email)
    {
        $this->checkModules();

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblockId)->fetch();

        if (!$hlblock) {
            return false;
        }

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        $result = $entityDataClass::getList(array(
            "select" => array("ID"),
            "filter" => array("UF_EMAIL" => $email)
        ));

        return $result->fetch() !== false;
    }


    private function addEntryToHighloadBlock(int $hlblockId, $fields)
    {
        $this->checkModules();

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblockId)->fetch();

        if (!$hlblock) {
            return false;
        }

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        $result = $entityDataClass::add($fields);

        return $result->isSuccess();
    }

    public function getSettings(int $iblockId, int $elementId)
    {
        if(Loader::includeModule('iblock')) {
            $sort = [];
            $filter = ["IBLOCK_ID" => $iblockId, "ID" => $elementId];
            $select = ["IBLOCK_ID", "ID", "PROPERTY_COUPON", "PROPERTY_HL_BLOCK_ID"];

            $rsSettings = \CIBlockElement::GetList($sort, $filter, false, false, $select)->Fetch();

            $result = [
                "COUPON" => $rsSettings["PROPERTY_COUPON_VALUE"],
                "HL_BLOCK_ID" => (int) $rsSettings["PROPERTY_HL_BLOCK_ID_VALUE"],
            ];

            return $rsSettings ? $result : false;
        }

        return false;
    }

    public function getHighloadBlockId(string $name)
    {
        $this->checkModules();
        
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=NAME' => $name],
            'select' => ['ID']
        ])->fetch();

        return $hlblock['ID'] ?: false;
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}
