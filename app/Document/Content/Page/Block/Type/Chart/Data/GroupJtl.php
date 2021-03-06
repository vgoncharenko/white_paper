<?php

namespace App\Document\Content\Page\Block\Type\Chart\Data;

use App\Document\Data\JtlProvider\AbstractJtl;
use App\Document\Data\JtlProvider\DataProviderInterface;
use App\Document\Data\ProviderRegistry;

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/5/18
 * Time: 10:54 AM
 */
class GroupJtl extends AbstractJtl implements DataProviderInterface
{
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
        $handleValue = $data['data']['value'];
        $handle = $this->data[$handleName];
        $metricConfig = isset($data['data']['items'][$index]['metrics'])
            ? $data['data']['items'][$index]['metrics']
            : $data['metrics'];

        foreach ($metricConfig as $metricKey => $itemMetric) {
            $x = $this->getRange($data);
            for ($i = 0; $i < count($x); $i++) {
                $list = [];

                if (isset($handle["$x[$i]"]) || isset($handle[(string)($x[$i] + 0.1)])) {
                    if (isset($handle["$x[$i]"])) {
                        foreach ($handle["$x[$i]"] as $item) {
                            $list[] = (int)$item[$handleValue];
                        }
                    }
                    if (isset($handle[(string)($x[$i] + 0.1)])) {
                        foreach ($handle[(string)($x[$i] + 0.1)] as $item) {
                            $list[] = (int)$item[$handleValue];
                        }
                    }
                }
                $result['values'][$metricKey][$i] = count($list)
                    ? $this->metricPool->get($itemMetric)->calculate($list)
                    : 0;
            }
        }

        return $result;
    }

    public function loadData($config)
    {
        $reportData = $this->instance->getData($this->measurementConfig['profile'] . $this->measurementConfig['type'] . ProviderRegistry::CONCURRENCY_JTL)['filtered'];
        $data = [];
        foreach ($config['data']['items'] as $itemConfig) {
            $data[$itemConfig['title']] = [];
            foreach ($itemConfig['tags'] as $tag) {
                if (isset($reportData['by_tags'][$tag])) {
                    $data[$itemConfig['title']] = array_merge($data[$itemConfig['title']], $reportData['by_tags'][$tag]);
                }
            }
        }

        $this->minValue = (float)$this->measurementConfig['src'][0]['build']['threads']['start'];
        $this->maxValue = (float)$this->measurementConfig['src'][count($this->measurementConfig['src']) - 1]['build']['threads']['end'];
        $precision = max(
            strlen(substr(strrchr($this->minValue, "."), 1)),
            strlen(substr(strrchr($this->maxValue, "."), 1))
        );
        $this->count = count($config['data']['items']);
        $cores = (float)str_replace("Pro", "", $this->instance->getInstanceType());

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
                    $load = round($item[$config['data']['category']] / $cores, $precision);
                    $this->data[$itemConfig['title']]["${load}"][] = $item;

                    if ($this->maxValue < $load) {
                        $this->maxValue = (float)$load;
                    }
                }
            }
            ksort($this->data[$itemConfig['title']], SORT_NUMERIC);
        }
    }

    public function getRange(array $data): array
    {
        if (empty($this->range)) {
            foreach ($this->measurementConfig['src'] as $item) {
                for ($i = $item['build']['threads']['start']; $i <=  $item['build']['threads']['end']; $i += $item['build']['threads']['step']) {
                    $this->range[] = $i;
                }
            }
        }

        return $this->range;
    }
}