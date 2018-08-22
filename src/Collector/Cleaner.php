<?php
/**
 * Crashed program metrics cleaner
 * User: moyo
 * Date: 18/03/2018
 * Time: 1:08 PM
 */

namespace Carno\Monitor\Collector;

use Carno\Monitor\Contracts\Metrical;
use Carno\Monitor\Daemon;
use Carno\Timer\Timer;

class Cleaner
{
    // special identify for crash reporting
    private const SP_IDENTIFY = 'px0';

    /**
     * @var string
     */
    private $daemon = null;

    /**
     * @var Daemon
     */
    private $source = null;

    /**
     * @var int
     */
    private $patrol = null;

    /**
     * @var int
     */
    private $expired = null;

    /**
     * @var array
     */
    private $activity = [];

    /**
     * @var int
     */
    private $crashed = 0;

    /**
     * Cleaner constructor.
     * @param Daemon $daemon
     * @param int $patrol
     * @param int $expired
     */
    public function __construct(Daemon $daemon, int $patrol = 5, int $expired = 55)
    {
        $this->source = $daemon;
        $this->patrol = $patrol;
        $this->expired = $expired;
    }

    /**
     * @return static
     */
    public function start() : self
    {
        $this->daemon = Timer::loop($this->patrol * 1000, [$this, 'checking']);
        return $this;
    }

    /**
     */
    public function stop() : void
    {
        $this->daemon && Timer::clear($this->daemon);
    }

    /**
     */
    public function checking() : void
    {
        foreach ($this->source->lived() as $pid => $time) {
            if ($pid === self::SP_IDENTIFY) {
                continue;
            }

            $last = $this->activity[$pid] ?? $time;
            $this->activity[$pid] = $time;

            if (time() - $last >= $this->expired) {
                unset($this->activity[$pid]);
                $this->source->forget($pid);
                $this->reporting();
            }
        }
    }

    /**
     */
    private function reporting() : void
    {
        $this->source->metrics(
            self::SP_IDENTIFY,
            Metrical::COUNTER,
            'program.crashed',
            'system',
            '',
            [],
            ['value' => ++ $this->crashed]
        );
    }
}
