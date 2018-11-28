<?php
/**
 * Output to prometheus
 * User: moyo
 * Date: 08/01/2018
 * Time: 4:34 PM
 */

namespace Carno\Monitor\Output;

use function Carno\Coroutine\co;
use Carno\HTTP\Client;
use Carno\HTTP\Server;
use Carno\HTTP\Server\Connection;
use Carno\HTTP\Standard\Response;
use Carno\Monitor\Collector\Aggregator;
use Carno\Monitor\Contracts\Metrical;
use Carno\Net\Address;
use Carno\Net\Contracts\HTTP;
use Carno\Timer\Timer;
use Generator;
use Throwable;

class Prometheus
{
    // active push interval in milliseconds
    private const ACT_PUSH_INV = 5000;

    /**
     * @var HTTP
     */
    private $httpd = null;

    /**
     * @var string
     */
    private $pushd = null;

    /**
     * @var string
     */
    private $gate = null;

    /**
     * @var string
     */
    private $host = 'localhost';

    /**
     * @var string
     */
    private $app = 'app';

    /**
     * @var Aggregator
     */
    private $agg = null;

    /**
     * Prometheus constructor.
     * @param string $host
     * @param string $app
     * @param Aggregator $agg
     */
    public function __construct(string $host, string $app, Aggregator $agg)
    {
        $this->host = $host;
        $this->app = $app;
        $this->agg = $agg;
    }

    /**
     * @param Address $listen
     * @param Address $push
     * @return static
     */
    public function start(Address $listen = null, Address $push = null) : self
    {
        if (is_null($listen)) {
            goto GATEWAY;
        }

        try {
            $this->httpd = Server::httpd(
                $listen,
                function (Connection $conn) {
                    switch ($conn->request()->getUri()->getPath()) {
                        case '/metrics':
                            $conn->reply(new Response(
                                200,
                                ['Content-Type' => 'text/plain; version=0.0.4'],
                                $this->exporting()
                            ));
                            break;
                        default:
                            $conn->reply(new Response(404));
                    }
                },
                'prom-exporter'
            );
            $this->httpd->serve();
        } catch (Throwable $e) {
            logger('monitor')->notice(
                'Prometheus exporter listening failed',
                ['error' => sprintf('%s::%s', get_class($e), $e->getMessage())]
            );
        }

        GATEWAY:

        if (is_null($push)) {
            goto END;
        }

        $this->pushd = Timer::loop(self::ACT_PUSH_INV, co(function () use ($listen, $push) {
            yield $this->pushing(Client::post(
                $this->gate = sprintf(
                    'http://%s:%d/metrics/job/%s/instance/%s',
                    $push->host(),
                    $push->port(),
                    $this->host,
                    $listen ?? 'default'
                ),
                $this->exporting(true),
                ['Connection' => 'keep-alive']
            ), 'pushing');
        }));

        END:

        return $this;
    }

    /**
     */
    public function stop() : void
    {
        $this->httpd && $this->httpd->shutdown();
        $this->pushd && Timer::clear($this->pushd) && co(function () {
            $this->gate && yield $this->pushing(Client::delete($this->gate), 'removing');
        })();
    }

    /**
     * @param Generator $caller
     * @param string $action
     */
    private function pushing(Generator $caller, string $action)
    {
        /**
         * @var Client\Responding $resp
         */
        try {
            ($resp = yield $caller) && $resp->data();
        } catch (Throwable $e) {
            logger('monitor')->notice(
                sprintf('Prometheus metrics %s failed', $action),
                ['error' => sprintf('%s::%s', get_class($e), $e->getMessage())]
            );
        }
    }

    /**
     * @param bool $push
     * @return string
     */
    private function exporting(bool $push = false) : string
    {
        $now = $push ? 0 : (int)(microtime(true) * 1000);

        $lines = $types = [];

        foreach ($this->agg->metrics() as $typed => $metrics) {
            foreach ($metrics as $named => $groups) {
                foreach ($groups as $grouped => $stack) {
                    $named = $this->format($named);

                    list($data, $labels, $description) = $stack;

                    $types[$named] = $typed;
                    $description && $lines[] = sprintf('# HELP %s %s', $named, $description);

                    if (in_array($typed, [Metrical::COUNTER, Metrical::GAUGE])) {
                        $lines[] = sprintf('%s{%s} %g', $named, $this->labeled($labels), $data['value'])
                            . ($now ? sprintf(' %d', $now) : '')
                        ;
                    } elseif (in_array($typed, [Metrical::HISTOGRAM, Metrical::SUMMARY])) {
                        $lines[] = sprintf('%s_sum{%s} %g', $named, $this->labeled($labels), $data['sum']);
                        $lines[] = sprintf('%s_count{%s} %d', $named, $this->labeled($labels), $data['count']);
                        if ($typed === Metrical::HISTOGRAM) {
                            foreach ($data['buckets'] as $bucket) {
                                list($bound, $observed) = $bucket;
                                $lines[] = sprintf(
                                    '%s_bucket{%s} %d',
                                    $named,
                                    $this->labeled($labels, ['le' => $bound]),
                                    $observed
                                );
                            }
                        } elseif ($typed === Metrical::SUMMARY) {
                            foreach ($data['quantiles'] as $quantile) {
                                list($position, $value) = $quantile;
                                $lines[] = sprintf(
                                    '%s{%s} %g',
                                    $named,
                                    $this->labeled($labels, ['quantile' => $position]),
                                    $value
                                );
                            }
                        }
                    }
                }
            }
        }

        foreach ($types as $named => $typed) {
            array_unshift($lines, sprintf('# TYPE %s %s', $named, $typed));
        }

        $push && $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * @param array ...$stack
     * @return string
     */
    private function labeled(array ...$stack) : string
    {
        $merged = ['host' => $this->host, 'app' => $this->app];

        foreach ($stack as $labels) {
            $merged = array_merge($merged, $labels);
        }

        $labeling = [];

        array_walk($merged, function (string $v, string $k) use (&$labeling) {
            $labeling[] = sprintf('%s="%s"', $k, $v);
        });

        return implode(',', $labeling);
    }

    /**
     * @param string $name
     * @return string
     */
    private function format(string $name) : string
    {
        return str_replace(['-', '.'], '_', $name);
    }
}
