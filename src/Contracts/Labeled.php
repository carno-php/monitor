<?php
/**
 * Special labels
 * User: moyo
 * Date: 2018/6/15
 * Time: 10:18 AM
 */

namespace Carno\Monitor\Contracts;

interface Labeled
{
    /**
     * "global" will make data raw in collector & aggregator
     */
    public const GLOBAL = '%!#:global';
}
