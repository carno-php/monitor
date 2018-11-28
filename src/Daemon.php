<?php
/**
 * Daemon program
 * User: moyo
 * Date: 28/12/2017
 * Time: 3:47 PM
 */

namespace Carno\Monitor;

use Carno\Container\DI;
use Carno\Monitor\Chips\Trunking;
use Carno\Monitor\Collector\Aggregator;
use Carno\Monitor\Collector\Cleaner;
use Carno\Monitor\Output\Prometheus;
use Carno\Net\Address;
use Carno\Process\Piping;
use Carno\Process\Program;
use Carno\Promise\Promised;

class Daemon extends Program
{
    use Trunking;

    /**
     * @var string
     */
    protected $name = 'metrics.exporter';

    /**
     * @var string
     */
    private $host = null;

    /**
     * @var string
     */
    private $app = null;

    /**
     * @var Cleaner
     */
    private $cls = null;

    /**
     * @var Aggregator
     */
    private $agg = null;

    /**
     * @var Prometheus
     */
    private $out = null;

    /**
     * @var Address
     */
    private $listener = null;

    /**
     * @var Address
     */
    private $gateway = null;

    /**
     * Daemon constructor.
     * @param string $app
     * @param Address $listener
     * @param Address $gateway
     */
    public function __construct(string $app, Address $listener = null, Address $gateway = null)
    {
        $this->host = gethostname();
        $this->app = $app;
        $this->listener = $listener;
        $this->gateway = $gateway;
    }

    /**
     * @param Piping $piping
     */
    protected function forking(Piping $piping) : void
    {
        DI::set(Daemon::class, $piping);
    }

    /**
     * triggered when process started
     */
    protected function starting() : void
    {
        $this->agg = (new Aggregator($this))->start();
        $this->out = (new Prometheus($this->host, $this->app, $this->agg))->start($this->listener, $this->gateway);
        $this->cls = (new Cleaner($this))->start();
    }

    /**
     * triggered when process exiting
     * @param Promised $wait
     */
    protected function stopping(Promised $wait) : void
    {
        $this->cls->stop();
        $this->out->stop();
        $this->agg->stop()->sync($wait);
    }
}
