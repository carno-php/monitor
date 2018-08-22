<?php
/**
 * Telemetry API
 * User: moyo
 * Date: 2018/5/19
 * Time: 11:20 AM
 */

namespace Carno\Monitor\Contracts;

interface Telemetry
{
    /**
     * [(string)typed, (string)named, (string)grouped, (string)description, (array)labels, (array)data] as report
     * [] if not ready
     * @return array
     */
    public function reporting() : array;
}
