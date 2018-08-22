<?php
/**
 * Trunking of "summary"
 * User: moyo
 * Date: 2018/5/19
 * Time: 1:03 PM
 */

namespace Carno\Monitor\Chips\Trunks;

trait Summary
{
    /**
     * @param array $input
     * @param array $output
     */
    protected function trkSummary(array $input, array &$output) : void
    {
        if (empty($output)) {
            $output = $input;
            return;
        }

        $output['count'] += $input['count'];
        $output['sum'] += $input['sum'];

        foreach ($input['quantiles'] as $idx => $quantile) {
            $old = &$output['quantiles'][$idx][1];
            $new = $quantile[1];
            $old = ($old + $new) / 2;
        }
    }
}
