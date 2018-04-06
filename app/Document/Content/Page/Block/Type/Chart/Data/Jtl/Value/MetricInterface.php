<?php

namespace App\Document\Content\Page\Block\Type\Chart\Data\Jtl\Value;

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/5/18
 * Time: 10:54 AM
 */
interface MetricInterface
{
    /**
     * @param array $list
     * @return float
     */
    public function calculate(array $list): float;
}