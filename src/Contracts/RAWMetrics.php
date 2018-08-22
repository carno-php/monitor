<?php
/**
 * RAW metrics exporter
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:37 AM
 */

namespace Carno\Monitor\Contracts;

interface RAWMetrics
{
    /**
     * @return array
     */
    public function data() : array;
}
