<?php
/**
 * Metrics named
 * User: moyo
 * Date: 2018/5/18
 * Time: 10:29 AM
 */

namespace Carno\Monitor\Chips\Metrical;

trait Named
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @param string $name
     * @param string $description
     * @return static
     */
    public function named(string $name, string $description = '') : self
    {
        $this->name = $name;
        $this->description = $description;
        return $this;
    }
}
