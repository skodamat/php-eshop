<?php

namespace Eshop;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging
{
    private static $logger;

    public static function init ()
    {
        self::$logger = new Logger('logger');
        self::$logger->pushHandler(new StreamHandler(__DIR__.'/../logs.log', Logger::INFO));
    }

    public static function info($string)
    {
        self::$logger->info($string);
    }

    public static function warning($string)
    {
        self::$logger->warning($string);
    }

    public static function error($string)
    {
        self::$logger->error($string);
    }
}