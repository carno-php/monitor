<?php
/**
 * Ticker worker
 * User: moyo
 * Date: 2018/5/19
 * Time: 2:52 PM
 */

namespace Carno\Monitor\Collector;

use Carno\Monitor\Chips\Timers;
use Carno\Monitor\Contracts\HMRegistry;
use Throwable;

class TWorker
{
    use Timers;

    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @param string $id
     * @param array $metrics
     * @param callable $worker
     * @param int $period
     */
    public function adding(string $id, array $metrics, callable $worker, int $period) : void
    {
        $this->tasks[$this->insert($period, function (string $ik) {
            $this->running($ik);
        })][$id] = [$worker, $metrics];

        $this->invoking($worker, $metrics);
    }

    /**
     * @param string $id
     */
    public function removing(string $id) : void
    {
        foreach ($this->tasks as $ik => $programs) {
            if (isset($programs[$id])) {
                $this->deregistering($programs[$id][1]);
                unset($this->tasks[$ik][$id]);
            }
        }
    }

    /**
     */
    public function stopping() : void
    {
        $this->clearing(function (string $ik) {
            foreach (array_keys($this->tasks[$ik] ?? []) as $id) {
                $this->removing($id);
            }
        });
    }

    /**
     * @param array $metrics
     */
    private function deregistering(array $metrics) : void
    {
        array_walk($metrics, function (HMRegistry $registry) {
            $registry->deregister();
        });
    }

    /**
     * @param string $ik
     */
    private function running(string $ik) : void
    {
        $w = $this->tasks[$ik] ?? [];
        array_walk($w, function (array $stack) {
            $this->invoking(...$stack);
        });
    }

    /**
     * @param callable $worker
     * @param array $metrics
     */
    private function invoking(callable $worker, array $metrics) : void
    {
        try {
            call_user_func_array($worker, $metrics);
        } catch (Throwable $e) {
            // skip
        }
    }
}
