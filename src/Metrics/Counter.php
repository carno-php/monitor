<?php
/**
 * Metrics type "counter"
 * User: moyo
 * Date: 2018/5/18
 * Time: 10:26 AM
 */

namespace Carno\Monitor\Metrics;

use Carno\Monitor\Chips\Metrical\Labeled;
use Carno\Monitor\Chips\Metrical\Named;
use Carno\Monitor\Chips\Metrical\Reporting;
use Carno\Monitor\Chips\Metrical\VExporter;
use Carno\Monitor\Contracts\HMRegistry;
use Carno\Monitor\Contracts\RAWMetrics;
use Carno\Monitor\Contracts\Telemetry;

class Counter implements RAWMetrics, HMRegistry, Telemetry
{
    use Named, Labeled, VExporter, Reporting;

    /**
     * @var float
     */
    private $value = 0;

    /**
     * @param float $v
     * @return float
     */
    public function inc(float $v = 1) : float
    {
        return $v > 0 ? $this->value += $v : $this->value;
    }
}
