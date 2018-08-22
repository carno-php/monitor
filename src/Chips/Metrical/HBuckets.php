<?php
/**
 * Histogram buckets
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:49 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait HBuckets
{
    /**
     * @var array
     */
    private $bounds = [];

    /**
     * @param float ...$bounds
     * @return static
     */
    public function fixed(float ...$bounds) : self
    {
        return $this->bucketing($bounds);
    }

    /**
     * @param float $start
     * @param float $width
     * @param int $count
     * @return static
     */
    public function linear(float $start, float $width, int $count) : self
    {
        $bounds = [];

        for ($i = 0; $i < $count; $i ++) {
            $bounds[] = $start;
            $start += $width;
        }

        return $this->bucketing($bounds);
    }

    /**
     * @param float $start
     * @param float $factor
     * @param int $count
     * @return static
     */
    public function exponential(float $start, float $factor, int $count) : self
    {
        $bounds = [];

        for ($i = 0; $i < $count; $i ++) {
            $bounds[] = $start;
            $start *= $factor;
        }

        return $this->bucketing($bounds);
    }

    /**
     * @param array $bounds
     * @return static
     */
    private function bucketing(array $bounds) : self
    {
        $this->bounds = $bounds;

        foreach ($bounds as $idx => $bound) {
            $this->buckets[$idx] = 0;
        }

        return $this;
    }
}
