<?php
/**
 * Program status (cpu memory and etc)
 * User: moyo
 * Date: 19/10/2017
 * Time: 4:39 PM
 */

namespace Carno\Monitor\Builtin;

use Carno\Monitor\Metrics;
use Carno\Monitor\Metrics\Gauge;
use Carno\Monitor\Ticker;

class ProgramStatus
{
    /**
     * ProgramStatus constructor.
     */
    public function __construct()
    {
        Ticker::new([Metrics::gauge()->named('memory.usage.bytes')], static function (Gauge $memory) {
            $memory->set(memory_get_usage());
        });
    }
}
