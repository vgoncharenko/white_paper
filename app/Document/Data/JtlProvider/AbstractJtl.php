<?php

namespace App\Document\Data\JtlProvider;

use App\Document\Data\Instance;
use App\Document\Data\InstanceInterface;
use App\Document\Data\JtlProvider\Jtl\ConditionPool;
use App\Document\Data\JtlProvider\Jtl\MetricPool;
use App\Document\Data\ProviderRegistry;

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/5/18
 * Time: 10:54 AM
 */
abstract class AbstractJtl implements DataProviderInterface
{
    const TIME_FORMAT = 'H:i';

    const RANGE_COUNT = 12;

    protected $data = [];

    /**
     * @var int
     */
    protected $maxValue;

    /**
     * @var int
     */
    protected $minValue;

    /**
     * @var array
     */
    protected $range = [];

    /**
     * @var MetricPool
     */
    protected $metricPool;

    /**
     * @var ConditionPool
     */
    protected $conditionPool;

    /**
     * @var InstanceInterface
     */
    protected $instance;

    /**
     * @var array
     */
    protected $measurementConfig;

    /**
     * @var int
     */
    protected $count;

    public function __construct(InstanceInterface $instance = null, array $measurementConfig = [])
    {
        $this->instance = $instance;
        $this->measurementConfig = $measurementConfig;
        $this->metricPool = new MetricPool();
        $this->conditionPool = new ConditionPool();
    }

    /**
     * @param array $data
     * @param int $index
     * @return array
     */
    public function getData(array $data, int $index)
    {
        $result = [];
        if (empty($this->data)) {
            $this->loadData($data);
        }

        $handleName = $data['data']['items'][$index]['title'];
        $handleCategory = $data['data']['category'];
        $handleValue = $data['data']['value'];

        $handle = $this->data[$handleName];
        $metricConfig = isset($data['data']['items'][$index]['metrics'])
            ? $data['data']['items'][$index]['metrics']
            : $data['metrics'];

        foreach ($metricConfig as $metricKey => $itemMetric) {
            $x = $this->getRange($data);
            $start = 0;
            $end = count($handle);
            for ($i = 0; $i < count($x); $i++) {
                $list = [];
                for ($j = $start; $j < $end; $j++) {
                    $item = $handle[$j];
                    $timestamp = (int)$item[$handleCategory] / 1000;
                    $time = date(self::TIME_FORMAT, $timestamp);
                    if (isset($x[$i + 1]) && strtotime($time) > strtotime($x[$i])) {
                        $start = $j;
                        break;
                    }
                    $list[] = (int)$item[$handleValue];
                }
                $result['values'][$metricKey][] = count($list) ? $this->metricPool->get($itemMetric)->calculate($list) : 0;
            }
        }

        return $result;
    }

    public function loadData($config)
    {
        $reportData = $this->instance->getData($this->measurementConfig['profile'] . $this->measurementConfig['type'] . ProviderRegistry::JTL)['filtered'];
        $data = [];
        foreach ($config['data']['items'] as $itemConfig) {
            $data[$itemConfig['title']] = [];
            foreach ($itemConfig['tags'] as $tag) {
                if (isset($reportData['by_tags'][$tag])) {
                    $data[$itemConfig['title']] = array_merge($data[$itemConfig['title']], $reportData['by_tags'][$tag]);
                }
            }
        }

        $this->minValue = (int)$reportData['all'][0][$config['data']['value']];
        $this->maxValue = (int)$reportData['all'][count($reportData['all']) - 1][$config['data']['value']];
        $this->count = count($config['data']['items']);

        foreach ($config['data']['items'] as $itemConfig) {
            $this->data[$itemConfig['title']] = [];
            foreach ($data[$itemConfig['title']] as $item) {
                $isConditionPass = true;
                if (isset($itemConfig['conditions'])) {
                    foreach ($itemConfig['conditions'] as $condition) {
                        if (!$this->conditionPool->get($condition)->check($item)) {
                            $isConditionPass = false;
                            break;
                        }
                    }
                }
                if ($isConditionPass) {
                    $this->data[$itemConfig['title']][] = $item;

                    if ($this->maxValue < $item[$config['data']['value']]) {
                        $this->maxValue = (int)$item[$config['data']['value']];
                    }
                }
            }
        }
    }

    public function getRange(array $data): array
    {
        if (empty($this->range)) {
            $result = [];
            $count = static::RANGE_COUNT;
            $reportData = $this->instance->getData($this->measurementConfig['profile'] . $this->measurementConfig['type'] . ProviderRegistry::JTL)['full'];

            $maxCategory = (int)$reportData[count($reportData) - 2]['timeStamp'] / 1000;
            $minCategory = (int)$reportData[0]['timeStamp'] / 1000;

            $delta = $maxCategory - $minCategory;
            $step = $delta / $count;

            for ($i = 0; $i <= $count; $i++) {
                $result[] = date(self::TIME_FORMAT, ($minCategory + $i * $step));
            }
            $this->range = $result;
        }

        return $this->range;
    }

    public function getSeriesTitle(array $data, int $index): string
    {
        return $data['data']['items'][$index]['title'];
    }

    public function getCount() : int
    {
        return $this->count;
    }
}