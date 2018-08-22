<?php
/**
 * Value exporter for "counter" and "gauge"
 * User: moyo
 * Date: 2018/5/18
 * Time: 3:04 PM
 */

namespace Carno\Monitor\Chips\Metrical;

trait VExporter
{
    /**
     * @return array
     */
    public function data() : array
    {
        return ['value' => $this->value];
    }
}
