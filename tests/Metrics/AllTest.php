<?php
/**
 * All metrics test
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:25 AM
 */

namespace Carno\Monitor\Tests\Metrics;

use Carno\Monitor\Metrics;
use PHPUnit\Framework\TestCase;

class AllTest extends TestCase
{
    public function testCounter()
    {
        $counter = Metrics::counter()->named('counter')->labels(['type' => 'counter']);

        $sum = 0;

        for ($i = 0; $i < 10000; $i ++) {
            $counter->inc($v = mt_rand(0, 1000));
            $sum += $v;
        }

        for ($j = 0; $i < 128; $j ++) {
            $counter->inc(mt_rand(-10000, 0));
        }

        $metrics = $counter->data();

        $this->assertEquals($sum, $metrics['value']);
    }

    public function testGauge()
    {
        $gauge = Metrics::gauge(100)->named('gauge')->labels(['type' => 'gauge']);

        $this->assertEquals(100, $gauge->data()['value']);

        $inc = 0;

        for ($i = 0; $i < 12000; $i ++) {
            $gauge->inc($v = mt_rand(0, 1000));
            $inc += $v;
        }

        $this->assertEquals(100 + $inc, $gauge->data()['value']);

        $dec = 0;

        for ($j = 0; $j < 12000; $j ++) {
            $gauge->dec($v = mt_rand(0, 1000));
            $dec += $v;
        }

        $this->assertEquals(100 + $inc - $dec, $gauge->data()['value']);

        $this->assertEquals($gauge->set(999), $gauge->data()['value']);
    }

    public function testHistogram()
    {
        $h1 = Metrics::histogram()->fixed(.01, .05, .2, 1)->named('histogram')->labels(['histogram' => 'fixed']);

        $h1->observe(.005);
        $h1->observe(.01);
        $h1->observe(.03);
        $h1->observe(.1);
        $h1->observe(.8);
        $h1->observe(1);
        $h1->observe(2);

        $m1 = $h1->data();

        $this->assertEquals(7, $m1['count']);
        $this->assertEquals(3.945, $m1['sum']);

        foreach ($m1['buckets'] as $bucket) {
            list($bound, $count) = $bucket;
            switch ($bound) {
                case .01:
                    $this->assertEquals(1, $count);
                    break;
                case .05:
                    $this->assertEquals(3, $count);
                    break;
                case .2:
                    $this->assertEquals(4, $count);
                    break;
                case 1:
                    $this->assertEquals(5, $count);
                    break;
                case '+Inf':
                    $this->assertEquals(7, $count);
                    break;
            }
        }

        $h2 = Metrics::histogram()->linear(1, 2, 5)->named('histogram')->labels(['histogram' => 'linear']);

        for ($i = 0; $i < 10; $i ++) {
            $h2->observe($i);
        }

        $this->assertArraySubset([
            'count' => 10,
            'sum' => 45.0,
            'buckets' => [
                [1.0, 1],
                [3.0, 3],
                [5.0, 5],
                [7.0, 7],
                [9.0, 9],
                ['+Inf', 10],
            ]
        ], $h2->data(), true);

        $h3 = Metrics::histogram()->exponential(0.1, 2, 5)->named('histogram')->labels(['histogram' => 'exponential']);

        for ($i = 0; $i < 2; $i += 0.05) {
            $h3->observe($i);
        }

        $this->assertEquals(39, $h3->data()['sum']);

        $this->assertArraySubset([
            'count' => 40,
            'buckets' => [
                [0.1, 2],
                [0.2, 4],
                [0.4, 9],
                [0.8, 16],
                [1.6, 32],
                ['+Inf', 40],
            ]
        ], $h3->data(), true);
    }

    public function testSummary()
    {
        $summary = Metrics::summary(0.1, 0.2, 0.5, 0.8, 0.95, 0.99)->named('summary')->labels(['type' => 'summary']);

        $sum = 0;

        for ($i = 0; $i < 50000; $i ++) {
            $summary->observe($v = mt_rand(0, 1000));
            $sum += $v;
        }

        $metrics = $summary->data();

        $this->assertEquals(50000, $metrics['count']);
        $this->assertEquals($sum, $metrics['sum']);

        foreach ($metrics['quantiles'] as $data) {
            list($quantile, $value) = $data;
            $this->assertTrue(abs($quantile * 1000 - $value) < 100);
        }
    }
}
