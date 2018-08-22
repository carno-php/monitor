<?php
/**
 * Metric types
 * User: moyo
 * Date: 28/12/2017
 * Time: 5:26 PM
 */

namespace Carno\Monitor\Contracts;

interface Metrical
{
    /**
     * A counter is a cumulative metric that represents a single numerical value that only ever goes up.
     * A counter is typically used to count requests served, tasks completed, errors occurred,
     * etc. Counters should not be used to expose current counts of items whose number can also go down,
     * e.g. the number of currently running goroutines. Use gauges for this use case.
     */
    public const COUNTER = 'counter';

    /**
     * A gauge is a metric that represents a single numerical value that can arbitrarily go up and down.
     * Gauges are typically used for measured values like temperatures or current memory usage,
     * but also "counts" that can go up and down, like the number of running goroutines.
     */
    public const GAUGE = 'gauge';

    /**
     * A histogram samples observations (usually things like request durations or response sizes)
     * and counts them in configurable buckets. It also provides a sum of all observed values.
     */
    public const HISTOGRAM = 'histogram';

    /**
     * Similar to a histogram, a summary samples observations
     * (usually things like request durations and response sizes).
     * While it also provides a total count of observations and a sum of all observed values,
     * it calculates configurable quantiles over a sliding time window.
     */
    public const SUMMARY = 'summary';
}
