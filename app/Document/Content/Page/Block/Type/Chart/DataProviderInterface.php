<?php

namespace App\Document\Content\Page\Block\Type\Chart;

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/5/18
 * Time: 10:54 AM
 */
interface DataProviderInterface
{
    /**
     * @param array $data
     * @param int $index
     * @return array
     */
    public function getData(array $data, int $index) : array;

    /**
     * @param array $data
     * @return array
     */
    public function getX(array $data) : array;
}