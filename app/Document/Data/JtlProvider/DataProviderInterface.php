<?php

namespace App\Document\Data\JtlProvider;

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
    public function getData(array $data, int $index);

    /**
     * @param array $data
     * @return array
     */
    public function getRange(array $data) : array;

    /**
     * @param array $data
     * @param int $index
     * @return string
     */
    public function getSeriesTitle(array $data, int $index) : string;

    /**
     * @return int
     */
    public function getCount() : int;
}