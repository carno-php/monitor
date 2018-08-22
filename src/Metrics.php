<?php
/**
 * Metrics register
 * User: moyo
 * Date: 2018/5/17
 * Time: 9:49 PM
 */

namespace Carno\Monitor;

use Carno\Monitor\Metrics\Counter;
use Carno\Monitor\Metrics\Gauge;
use Carno\Monitor\Metrics\Histogram;
use Carno\Monitor\Metrics\Summary;

class Metrics
{
    /**
     * @return Counter
     */
    public static function counter() : Counter
    {
        return (new Counter)->register();
    }

    /**
     * @param float $initial
     * @return Gauge
     */
    public static function gauge(float $initial = 0) : Gauge
    {
        return (new Gauge($initial))->register();
    }

    /**
     * @return Histogram
     */
    public static function histogram() : Histogram
    {
        return (new Histogram)->register();
    }

    /**
     * @param float ...$quantiles
     * @return Summary
     */
    public static function summary(float ...$quantiles) : Summary
    {
        return (new Summary)->quantiles(...$quantiles)->register();
    }
}
