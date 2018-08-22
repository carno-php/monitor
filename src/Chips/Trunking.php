<?php
/**
 * Metrics trunking
 * User: moyo
 * Date: 28/12/2017
 * Time: 5:09 PM
 */

namespace Carno\Monitor\Chips;

use Carno\Monitor\Chips\Trunks\Histogram;
use Carno\Monitor\Chips\Trunks\Summary;
use Carno\Monitor\Chips\Trunks\Values;
use Carno\Monitor\Contracts\Labeled;
use Carno\Monitor\Contracts\Metrical;
use Closure;

trait Trunking
{
    use Histogram, Summary, Values;

    /**
     * @var array
     */
    private $metrics = [];

    /**
     * @var array
     */
    private $lived = [];

    /**
     * @return array
     */
    public function lived() : array
    {
        return $this->lived;
    }


    /**
     * @param string $identify
     * @param string $typed
     * @param string $named
     * @param string $grouped
     * @param string $description
     * @param array $labels
     * @param array $data
     */
    public function metrics(
        string $identify,
        string $typed,
        string $named,
        string $grouped,
        string $description,
        array $labels,
        array $data
    ) : void {
        $this->lived[$identify] = time();
        $this->metrics[$typed][$named][$grouped][$identify] = [$data, $labels, $description];
    }

    /**
     * @param string $identify
     * @param string $typed
     * @param string $named
     * @param string $grouped
     */
    public function remove(string $identify, string $typed, string $named, string $grouped) : void
    {
        unset($this->metrics[$typed][$named][$grouped][$identify]);
    }

    /**
     * @param string $identify
     */
    public function forget(string $identify) : void
    {
        unset($this->lived[$identify]);
        foreach ($this->metrics as $typed => $metrics) {
            foreach ($metrics as $named => $groups) {
                foreach ($groups as $grouped => $stack) {
                    foreach ($stack as $pid => $last) {
                        $pid === $identify && $this->remove($identify, $typed, $named, $grouped);
                    }
                }
            }
        }
    }

    /**
     * @param Closure $receiver
     */
    public function spouting(Closure $receiver) : void
    {
        foreach ($this->metrics as $typed => $metrics) {
            foreach ($metrics as $named => $groups) {
                foreach ($groups as $grouped => $stack) {
                    $stack && $receiver($typed, $named, $grouped, ...$this->expanding($typed, $stack));
                }
            }
        }
    }

    /**
     * @param string $typed
     * @param array $stack
     * @return array
     */
    private function expanding(string $typed, array $stack) : array
    {
        $data = [];

        foreach ($stack as $pid => $last) {
            // data,label,desc in last
            list($part, $labels, $description) = $last;

            // checking data is global
            if (isset($labels[Labeled::GLOBAL])) {
                unset($labels[Labeled::GLOBAL]);
                $data = $part;
                break;
            }

            // type switch
            switch ($typed) {
                case Metrical::COUNTER:
                case Metrical::GAUGE:
                    $this->trkValues($part, $data);
                    break;
                case Metrical::HISTOGRAM:
                    $this->trkHistogram($part, $data);
                    break;
                case Metrical::SUMMARY:
                    $this->trkSummary($part, $data);
                    break;
            }
        }

        return [$description ?? '', $labels ?? [], $data];
    }
}
