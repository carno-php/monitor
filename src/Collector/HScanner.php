<?php
/**
 * Harvester scanner
 * User: moyo
 * Date: 2018/5/19
 * Time: 2:44 PM
 */

namespace Carno\Monitor\Collector;

use Carno\Monitor\Chips\Timers;
use Carno\Monitor\Contracts\Telemetry;
use Carno\Monitor\Daemon;
use Carno\Process\Piping;

class HScanner
{
    use Timers;

    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @var Daemon
     */
    private $transport = null;

    /**
     * @var string
     */
    private $identify = null;

    /**
     * Scanner constructor.
     * @param Piping $daemon
     */
    public function __construct(Piping $daemon)
    {
        $this->transport = $daemon;
        $this->identify = sprintf('p-%d', getmypid());
    }

    /**
     * @param Telemetry $source
     * @param int $period
     */
    public function sourcing(Telemetry $source, int $period) : void
    {
        $this->tasks[$this->insert($period, function (string $ik) {
            $this->running($ik);
        })][] = $source;

        $this->gathering($source);
    }

    /**
     * @param Telemetry $source
     */
    public function removing(Telemetry $source) : void
    {
        foreach ($this->tasks as $ik => $sources) {
            foreach ($sources as $idx => $exists) {
                if ($exists === $source) {
                    unset($this->tasks[$ik][$idx]);
                    $this->transport->remove($this->identify, ...$source->reporting());
                }
            }
        }
    }

    /**
     */
    public function stopping() : void
    {
        $this->clearing(function (string $ik) {
            unset($this->tasks[$ik]);
        });
    }

    /**
     * @param string $ik
     */
    private function running(string $ik) : void
    {
        $w = $this->tasks[$ik] ?? [];
        array_walk($w, function (Telemetry $source) {
            $this->gathering($source);
        });
    }

    /**
     * @param Telemetry $source
     */
    private function gathering(Telemetry $source) : void
    {
        ($posted = $source->reporting()) && $this->transport->metrics($this->identify, ...$posted);
    }
}
