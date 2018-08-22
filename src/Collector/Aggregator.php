<?php
/**
 * Metrics aggregator
 * User: moyo
 * Date: 28/12/2017
 * Time: 5:56 PM
 */

namespace Carno\Monitor\Collector;

use Carno\Monitor\Daemon;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Carno\Timer\Timer;

class Aggregator
{
    /**
     * @var array
     */
    private $metrics = [];

    /**
     * @var Daemon
     */
    private $source = null;

    /**
     * @var string
     */
    private $timer = null;

    /**
     * @var int
     */
    private $cycled = null;

    /**
     * Aggregation constructor.
     * @param Daemon $daemon
     * @param int $cycled
     */
    public function __construct(Daemon $daemon, int $cycled = 2)
    {
        $this->source = $daemon;
        $this->cycled = $cycled;
    }

    /**
     * @return array
     */
    public function metrics() : array
    {
        return $this->metrics;
    }

    /**
     * @return static
     */
    public function start() : self
    {
        $this->timer = Timer::loop($this->cycled * 1000, function () {
            $metrics = [];

            $this->source->spouting(static function (
                string $typed,
                string $named,
                string $grouped,
                string $description,
                array $labels,
                array $data
            ) use (&$metrics) {
                $metrics[$typed][$named][$grouped] = [$data, $labels, $description];
            });

            $this->metrics = $metrics;
        });

        return $this;
    }

    /**
     * @return Promised
     */
    public function stop() : Promised
    {
        Timer::clear($this->timer);
        return Promise::resolved();
    }
}
