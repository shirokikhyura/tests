<?php

namespace Helper;

use \PHPUnit\Framework\Assert;

final class Device extends \Codeception\Module
{
    const DESKTOP = 1;
    const TABLET = 2;
    const MOBILE = 3;

    private static $deviceWidthById = [
        self::DESKTOP => 1366,
        self::TABLET => 980,
        self::MOBILE => 640,
    ];

    private static $deviceHeightById = [
        self::DESKTOP => 6000,
        self::TABLET => 6100,
        self::MOBILE => 7600,
    ];

    private static $deviceNameById = [
        self::DESKTOP => 'desktop',
        self::TABLET => 'tablet',
        self::MOBILE => 'mobile',
    ];

    public static function getWidth($deviceId = 1)
    {
        if (array_key_exists($deviceId, self::$deviceWidthById)) {
            return self::$deviceWidthById[$deviceId];
        } else {
            Assert::fail("Width for device #$deviceId not set.");
        }
    }

    public static function getHeight($deviceId = 1)
    {
        if (array_key_exists($deviceId, self::$deviceHeightById)) {
            return self::$deviceHeightById[$deviceId];
        } else {
            Assert::fail("Height for device #$deviceId not set.");
        }
    }

    public static function getName($deviceId = 1)
    {
        if (array_key_exists($deviceId, self::$deviceNameById)) {
            return self::$deviceNameById[$deviceId];
        } else {
            Assert::fail("Name for device #$deviceId not set.");
        }
    }
}
