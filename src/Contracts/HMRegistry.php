<?php
/**
 * Harvested metrics registry
 * User: moyo
 * Date: 2018/5/19
 * Time: 11:15 PM
 */

namespace Carno\Monitor\Contracts;

interface HMRegistry
{
    /**
     * @param int $period
     * @return static
     */
    public function register(int $period);

    /**
     */
    public function deregister() : void;
}
