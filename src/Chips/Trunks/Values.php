<?php
/**
 * Trunking of "counter" and "gauge"
 * User: moyo
 * Date: 2018/5/19
 * Time: 1:18 PM
 */

namespace Carno\Monitor\Chips\Trunks;

trait Values
{
    /**
     * @param array $input
     * @param array $output
     */
    protected function trkValues(array $input, array &$output) : void
    {
        empty($output) ? $output = $input : $output['value'] += $input['value'];
    }
}
