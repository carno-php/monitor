<?php
/**
 * As reporter
 * User: moyo
 * Date: 2018/5/18
 * Time: 5:54 PM
 */

namespace Carno\Monitor\Chips\Metrical;

use Carno\Monitor\Contracts\Metrical;
use Carno\Monitor\Harvester;
use Carno\Monitor\Metrics\Counter;
use Carno\Monitor\Metrics\Gauge;
use Carno\Monitor\Metrics\Histogram;
use Carno\Monitor\Metrics\Summary;

trait Reporting
{
    /**
     * @param int $period
     * @return static
     */
    public function register(int $period = 5) : self
    {
        return Harvester::join($this, $period);
    }

    /**
     */
    public function deregister() : void
    {
        Harvester::forget($this);
    }

    /**
     * @return array
     */
    public function reporting() : array
    {
        return empty($this->name) ? [] : [
            $this->typed(),
            $this->name,
            $this->grouped,
            $this->description,
            $this->labels,
            $this->data()
        ];
    }

    /**
     * @return string
     */
    private function typed() : string
    {
        return [
            Counter::class => Metrical::COUNTER,
            Gauge::class => Metrical::GAUGE,
            Histogram::class => Metrical::HISTOGRAM,
            Summary::class => Metrical::SUMMARY,
        ][static::class];
    }
}
