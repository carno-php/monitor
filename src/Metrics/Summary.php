<?php
/**
 * Metrics type "summary"
 * User: moyo
 * Date: 2018/5/17
 * Time: 11:06 PM
 * @see http://infolab.stanford.edu/~datar/courses/cs361a/papers/quantiles.pdf
 */

namespace Carno\Monitor\Metrics;

use Carno\Monitor\Chips\Metrical\Labeled;
use Carno\Monitor\Chips\Metrical\Named;
use Carno\Monitor\Chips\Metrical\Reporting;
use Carno\Monitor\Chips\Metrical\SExporter;
use Carno\Monitor\Chips\Metrical\SQuantiles;
use Carno\Monitor\Contracts\HMRegistry;
use Carno\Monitor\Contracts\RAWMetrics;
use Carno\Monitor\Contracts\Telemetry;

class Summary implements RAWMetrics, HMRegistry, Telemetry
{
    use Named, Labeled, SQuantiles, SExporter, Reporting;

    /**
     * @var float
     */
    private $epsilon = 0;

    /**
     * @var int
     */
    private $threshold = 0;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var float
     */
    private $sum = 0;

    /**
     * @var SItem[]
     */
    private $samples = [];

    /**
     * Summary constructor.
     * @param float $epsilon
     * @param int $threshold
     */
    public function __construct(float $epsilon = 0.001, int $threshold = 2000)
    {
        $this->epsilon = $epsilon;
        $this->threshold = $threshold;
    }

    /**
     * @param float $v
     */
    public function observe(float $v) : void
    {
        $rank = $this->ranked($v);

        $delta = $rank === 0 || $rank === count($this->samples) ? 0 : floor(2 * $this->epsilon * $this->count);

        array_splice($this->samples, $rank, 0, [new SItem($v, 1, $delta)]);

        count($this->samples) > $this->threshold && $this->compress();

        $this->count ++;
        $this->sum += $v;
    }

    /**
     * @param float $quantile
     * @return float
     */
    private function query(float $quantile) : float
    {
        $rank = 0;

        $desired = intval($quantile * $this->count);

        for ($i = 1; $i < count($this->samples); $i ++) {
            $sp = $this->samples[$i - 1];
            $sc = $this->samples[$i];

            if (($rank += $sp->g) + $sc->g + $sc->d > $desired + (2 * $this->epsilon * $this->count)) {
                return $sp->v;
            }
        }

        return end($this->samples)->v;
    }

    /**
     */
    private function compress() : void
    {
        for ($i = 0; $i < count($this->samples) - 1; $i ++) {
            $sa = $this->samples[$i];
            $sb = $this->samples[$i + 1];

            if ($sa->g + $sb->g + $sb->d <= floor(2 * $this->epsilon * $this->count)) {
                $sb->g += $sa->g;
                array_splice($this->samples, $i, 1);
            }
        }
    }

    /**
     * @param float $v
     * @return int
     */
    private function ranked(float $v) : int
    {
        if (empty($this->samples)) {
            return 0;
        }

        $start = $rank = 0;
        $end = count($this->samples) - 1;

        while ($start <= $end) {
            $rank = intval(($start + $end) / 2);
            if (($curr = $this->samples[$rank]->v) < $v) {
                $start = $rank + 1;
            } elseif ($curr > $v) {
                $end = $rank - 1;
            } else {
                return $rank;
            }
        }

        return $rank;
    }
}
