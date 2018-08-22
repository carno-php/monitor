<?php
/**
 * Metrics type "histogram"
 * User: moyo
 * Date: 2018/5/18
 * Time: 10:27 AM
 */

namespace Carno\Monitor\Metrics;

use Carno\Monitor\Chips\Metrical\HBuckets;
use Carno\Monitor\Chips\Metrical\HExporter;
use Carno\Monitor\Chips\Metrical\Labeled;
use Carno\Monitor\Chips\Metrical\Named;
use Carno\Monitor\Chips\Metrical\Reporting;
use Carno\Monitor\Contracts\HMRegistry;
use Carno\Monitor\Contracts\RAWMetrics;
use Carno\Monitor\Contracts\Telemetry;

class Histogram implements RAWMetrics, HMRegistry, Telemetry
{
    use Named, Labeled, HBuckets, HExporter, Reporting;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var float
     */
    private $sum = 0;

    /**
     * @var array
     */
    private $buckets = [];

    /**
     * @param float $v
     */
    public function observe(float $v) : void
    {
        foreach ($this->bounds as $idx => $bound) {
            if ($v < $bound) {
                $this->buckets[$idx] ++;
                break;
            }
        }

        $this->count ++;
        $this->sum += $v;
    }
}
