<?php
/**
 * Summary quantiles
 * User: moyo
 * Date: 2018/5/18
 * Time: 11:31 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait SQuantiles
{
    /**
     * @var array
     */
    private $quantiles = [];

    /**
     * @param float ...$quantiles
     * @return static
     */
    public function quantiles(float ...$quantiles) : self
    {
        $this->quantiles = $quantiles;
        return $this;
    }
}
