<?php
/**
 * Histogram exporter
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:49 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait HExporter
{
    /**
     * @return array
     */
    public function data() : array
    {
        $s = ['count' => $this->count, 'sum' => $this->sum];

        foreach ($this->bounds as $idx => $bound) {
            $s['buckets'][] = [$bound, $last = $this->buckets[$idx] + ($last ?? 0)];
        }

        $s['buckets'][] = ['+Inf', $this->count];

        return $s;
    }
}
