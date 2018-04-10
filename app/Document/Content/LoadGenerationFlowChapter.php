<?php

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/4/18
 * Time: 4:37 PM
 */

namespace App\Document\Content;

use App\Document\Data\Instance;
use PhpOffice\PhpWord\PhpWord;

class LoadGenerationFlowChapter extends AbstractChapter implements ChapterInterface
{
    private $map = [
        Instance::SS_TYPE => 'Server Side'
    ];

    public function add(PhpWord $phpWord, $content)
    {
        $section = $this->addPage($phpWord);

        if (isset($content['title'])) {
            $this->addTitle($section, $content['title']);
        }

        foreach ($this->config->getInstances() as $instance) {
            $instanceObject = new Instance($instance, $content['pages']);
            foreach ($instance['profiles'] as $key => $profileConfig) {
                $this->addTitle($section, $instance['type'] . ' with "' . $profileConfig['name'] . '" profile');
                foreach ($profileConfig['measurements'] as $measurementKey => $item) {
                    $this->addTitle($section, $this->map[$item['type']]);
                    $this->addPages($phpWord, $section, $content['pages'], $instanceObject, $item);

                    if (isset($profileConfig['measurements'][$measurementKey + 1])) {
                        $section = $phpWord->addSection();
                    }
                }

                if (isset($instance['profiles'][$key + 1])) {
                    $section = $phpWord->addSection();
                }
            }
        }
    }
}