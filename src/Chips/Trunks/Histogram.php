<?php
/**
 * Trunking of "histogram"
 * User: moyo
 * Date: 2018/5/19
 * Time: 1:03 PM
 */

namespace Carno\Monitor\Chips\Trunks;

trait Histogram
{
    /**
     * @param array $input
     * @param array $output
     */
    protected function trkHistogram(array $input, array &$output) : void
    {
        if (empty($output)) {
            $output = $input;
            return;
        }

        $output['count'] += $input['count'];
        $output['sum'] += $input['sum'];

        foreach ($input['buckets'] as $idx => $bucket) {
            $output['buckets'][$idx][1] += $bucket[1];
        }
    }
}
