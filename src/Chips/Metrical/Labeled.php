<?php
/**
 * Metrics labeled
 * User: moyo
 * Date: 2018/5/18
 * Time: 10:32 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait Labeled
{
    /**
     * @var array
     */
    private $labels = [];

    /**
     * @var string
     */
    private $grouped = 'user';

    /**
     * @param array $set
     * @return static
     */
    public function labels(array $set) : self
    {
        $this->labels = array_merge($this->labels, $set);
        $this->grouped = md5(serialize($this->labels));
        return $this;
    }
}
