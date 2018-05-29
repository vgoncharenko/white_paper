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

class Table implements TypeInterface
{
    /**
     * @var PhpWord
     */
    private $phpWord;

    public function __construct(PhpWord $phpWord)
    {
        $this->phpWord = $phpWord;
    }

    public function add(Section $section, $content)
    {
        $table = $section->addTable(Font::DEFAULT_TABLE_STYLE);
//        $table->setWidth(Font::DEFAULT_TABLE_WIDTH);
        $count = count($content['rows'][0]) - 1;
        $cellRowSpan = ['vMerge' => 'restart', 'valign' => 'center'];
        $cellRowContinue = ['vMerge' => 'continue'];
        $cellColSpan = ['gridSpan' => $count, 'valign' => 'center'];

        foreach ($content['rows'] as $key => $row) {
            $table->addRow(Font::DEFAULT_TABLE_ROW_HEIGHT);
            $color = Font::DEFAULT_TABLE_CELL_COLOR;
            if ($key == 0) {
                $color = Font::DEFAULT_TABLE_TH_COLOR;
            }

            foreach ($row as $index => $item) {
                if (null == $item['text']) {
                    continue;
                }

                $width = empty($item['width']) ? 1500 : $item['width'];

                $textAlignment = empty($item['textAlignment'])
                    ? \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                    :  $item['textAlignment'];


                $cellRow = ['valign' => 'center'];
                if ($item['type'] == 'cell') {
                    $cellRow = $cellColSpan;
                    $width = $width * $count;
                }
                if ($item['type'] == 'row') {
                    $cellRow = $cellRowSpan;
                }
                if (null == $item['text']) {
                    $cellRow = $cellRowContinue;
                    $width = null;
                }

                $cell = $table->addCell($width, $cellRow);
                $cell->addText(
                    $item['text'],
                    [
                        'name' => Font::DEFAULT_TITLE_FONT,
                        'size' => Font::DEFAULT_TABLE_TEXT_SIZE,
                        'color' => $color,
                        'bold' => !empty($item['bold'])
                    ],
                    [
                        'alignment' => $textAlignment,
                        'indentation' => [
                            'left' => 100
                        ]
                    ]
                );
            }

        }
    }
}