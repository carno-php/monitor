<?php
/**
 * Summary exporter
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:38 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait SExporter
{
    /**
     * @return array
     */
    public function data() : array
    {
        $s = ['count' => $this->count, 'sum' => $this->sum];

        foreach ($this->quantiles as $quantile) {
            $s['quantiles'][] = [$quantile, $this->query($quantile)];
        }

        return $s;
    }
}
