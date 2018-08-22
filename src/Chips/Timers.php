<?php
/**
 * Timers manager
 * User: moyo
 * Date: 2018/5/19
 * Time: 3:07 PM
 */

namespace Carno\Monitor\Chips;

use Carno\Timer\Timer;
use Closure;

trait Timers
{
    /**
     * @var array array
     */
    private $timers = [];

    /**
     * @param int $period
     * @param callable $do
     * @return string
     */
    protected function insert(int $period, callable $do) : string
    {
        $ik = sprintf('ik-%d', $period);

        if (isset($this->timers[$ik])) {
            return $ik;
        }

        $this->timers[$ik] = Timer::loop($period * 1000, static function () use ($ik, $do) {
            call_user_func($do, $ik);
        });

        return $ik;
    }

    /**
     * @param Closure $sync
     */
    protected function clearing(Closure $sync) : void
    {
        foreach ($this->timers as $ik => $timer) {
            $sync($ik);
            Timer::clear($timer);
        }
    }
}
