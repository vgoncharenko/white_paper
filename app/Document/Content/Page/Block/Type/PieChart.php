<?php
/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/4/18
 * Time: 8:25 PM
 */

namespace App\Document\Content\Page\Block\Type;

use App\Document\Content\Page\Block\TypeInterface;
use App\Document\Font;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

class PieChart implements TypeInterface
{
    const TYPE = 'pie';

    /**
     * @var PhpWord
     */
    private $phpWord;

    /**
     * @var DataProviderPool
     */
    private $dataProviderPool;

    public function __construct(PhpWord $phpWord)
    {
        $this->phpWord = $phpWord;
        $this->dataProviderPool = new DataProviderPool();
    }

    public function add(Section $section, $content, $subTitle = false)
    {
        $title = $content['title'];
        $section->addTitle(
            $title,
            2
        );

        $style = [
            'width' => Converter::cmToEmu(18),
            'height' => Converter::cmToEmu(16),
            '3d' => true,
            'showAxisLabels' => true,
            'showGridX' => true,
            'showGridY' => true,
            'yRotation' => 30,
            'showLabels' => true
        ];

        $section->addChart(
            self::TYPE,
            $this->dataProviderPool->get($content['type'])->getRange($content),
            $this->dataProviderPool->get($content['type'])->getData($content, 0),
            $style
        );
    }
}