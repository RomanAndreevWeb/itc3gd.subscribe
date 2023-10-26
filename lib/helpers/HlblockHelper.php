<?php

namespace Itc3gd;

class HlblockHelper
{
    private static $hlblockName = 'ItcSubscribeEmail';
    private static $hlblockNameTable = 'itc3gd_subscribe_email';

    public static function getHlblockName()
    {
        return self::$hlblockName;
    }

    public static function getHlblockNameTable()
    {
        return self::$hlblockNameTable;
    }
}