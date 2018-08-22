<?php
/**
 * Summary item
 * User: moyo
 * Date: 2018/5/17
 * Time: 11:21 PM
 */

namespace Carno\Monitor\Metrics;

final class SItem
{
    /**
     * value
     * @var float
     */
    public $v = 0;

    /**
     * lower delta
     * @var int
     */
    public $g = 1;

    /**
     * delta
     * @var int
     */
    public $d = 0;

    /**
     * Item constructor.
     * @param float $v
     * @param int $g
     * @param int $d
     */
    public function __construct(float $v, int $g, int $d)
    {
        $this->v = $v;
        $this->g = $g;
        $this->d = $d;
    }
}
