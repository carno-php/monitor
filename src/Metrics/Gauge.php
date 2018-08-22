<?php
/**
 * Metrics type "gauge"
 * User: moyo
 * Date: 2018/5/18
 * Time: 10:27 AM
 */

namespace Carno\Monitor\Metrics;

use Carno\Monitor\Chips\Metrical\Labeled;
use Carno\Monitor\Chips\Metrical\Named;
use Carno\Monitor\Chips\Metrical\Reporting;
use Carno\Monitor\Chips\Metrical\VExporter;
use Carno\Monitor\Contracts\HMRegistry;
use Carno\Monitor\Contracts\RAWMetrics;
use Carno\Monitor\Contracts\Telemetry;

class Gauge implements RAWMetrics, HMRegistry, Telemetry
{
    use Named, Labeled, VExporter, Reporting;

    /**
     * @var float
     */
    private $value = 0;

    /**
     * Gauge constructor.
     * @param float $initial
     */
    public function __construct(float $initial = 0)
    {
        $this->value = $initial;
    }

    /**
     * @param float $v
     * @return float
     */
    public function inc(float $v = 1) : float
    {
        return $this->value += $v;
    }

    /**
     * @param float $v
     * @return float
     */
    public function dec(float $v = 1) : float
    {
        return $this->value -= $v;
    }

    /**
     * @param float $v
     * @return float
     */
    public function set(float $v) : float
    {
        return $this->value = $v;
    }
}
