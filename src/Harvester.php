<?php
/**
 * Metrics harvester
 * User: moyo
 * Date: 28/12/2017
 * Time: 4:05 PM
 */

namespace Carno\Monitor;

use Carno\Container\DI;
use Carno\Monitor\Collector\HScanner;
use Carno\Monitor\Contracts\Telemetry;

class Harvester
{
    /**
     * @var HScanner
     */
    private static $scanner = null;

    /**
     * @param Telemetry $source
     * @param int $period
     * @return Telemetry
     */
    public static function join(Telemetry $source, int $period = 5) : Telemetry
    {
        if (DI::has(Daemon::class)) {
            (self::$scanner ?? self::$scanner = new HScanner(DI::get(Daemon::class)))
                ->sourcing($source, $period)
            ;
        }
        return $source;
    }

    /**
     * @param Telemetry $source
     */
    public static function forget(Telemetry $source) : void
    {
        self::$scanner && self::$scanner->removing($source);
    }

    /**
     */
    public static function shutdown() : void
    {
        self::$scanner && self::$scanner->stopping();
    }
}
