<?php
/**
 * Monitor ticker
 * User: moyo
 * Date: 2018/5/19
 * Time: 2:43 PM
 */

namespace Carno\Monitor;

use Carno\Monitor\Collector\TWorker;

class Ticker
{
    /**
     * @var int
     */
    private static $idg = 0;

    /**
     * @var TWorker
     */
    private static $worker = null;

    /**
     * @param array $metrics
     * @param callable $worker
     * @param int $period
     * @return string
     */
    public static function new(array $metrics, callable $worker, int $period = 5) : string
    {
        $idg = sprintf('tw-%d', self::$idg ++);

        (self::$worker ?? self::$worker = new TWorker)
            ->adding($idg, $metrics, $worker, $period)
        ;

        return $idg;
    }

    /**
     * @param string $id
     */
    public static function stop(string $id) : void
    {
        self::$worker && self::$worker->removing($id);
    }

    /**
     */
    public static function exit() : void
    {
        self::$worker && self::$worker->stopping();
    }
}
