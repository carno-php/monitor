<?php
/**
 * Pool stats exporter
 * User: moyo
 * Date: 2018/5/19
 * Time: 5:11 PM
 */

namespace Carno\Monitor\Builtin;

use Carno\Monitor\Metrics;
use Carno\Monitor\Metrics\Gauge;
use Carno\Monitor\Ticker;
use Carno\Pool\Pool;

class PoolStatsExporter
{
    /**
     * @var string
     */
    private $tick = null;

    /**
     * PoolStatsExporter constructor.
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $labels = [
            'resource' => $pool->resource(),
            'pid' => spl_object_hash($pool),
        ];

        $this->tick = Ticker::new(
            [
                Metrics::gauge()->named('pool.conn.idle')->labels($labels),
                Metrics::gauge()->named('pool.conn.busy')->labels($labels),
                Metrics::gauge()->named('pool.select.wait')->labels($labels),
            ],
            static function (Gauge $idle, Gauge $busy, Gauge $wait) use ($pool) {
                $idle->set($pool->stats()->cIdling());
                $busy->set($pool->stats()->cBusying());
                $wait->set($pool->stats()->sPending());
            }
        );
    }

    /**
     */
    public function stop() : void
    {
        Ticker::stop($this->tick);
    }
}
