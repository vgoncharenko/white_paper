<?php

/**
 * Created by PhpStorm.
 * User: vgoncharenko
 * Date: 4/4/18
 * Time: 4:37 PM
 */

namespace App\Document;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use App\Document\Settings\Header;
use PhpOffice\PhpWord\Shared\Converter;

class Settings
{
    /**
     * @var Header
     */
    private $header;

    public function __construct()
    {
        $this->header = new Header();
    }

    public function setDefaultPageStyle(PhpWord $phpWord)
    {
        $PidPageSettings = array(
            'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.01),
            'footerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
            'marginLeft'  => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
            'marginTop'   => 0,
            'marginBottom'=> 0,
        );
        $phpWord->addSection($PidPageSettings);
    }

    public function setDefaultHeader(PhpWord $phpWord)
    {
        $section = $phpWord->getSections()[0];
        $this->header->addHeader($section);
    }

    public function setDefaultParagraphStyle(PhpWord $phpWord)
    {
        $phpWord->addParagraphStyle(Font::DEFAULT_CHAPTER_CONTENT,
            [

                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
                'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
                'spacing' => 120,
                'lineHeight' => 1,
            ]
        );
    }

    public function setLeftOrientedParagraphStyle(PhpWord $phpWord)
    {
        $phpWord->addParagraphStyle(Font::LEFT_ORIENTED_CHAPTER_CONTENT,
            [

                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
                'spacing' => 120,
            ]
        );
    }

    public function setDefaultChapterTitleParagraphStyle(PhpWord $phpWord)
    {
        $phpWord->addParagraphStyle(Font::DEFAULT_CHAPTER_TITLE,
            [

                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START,
            ]
        );
    }

    public function setDefaultChartTitleStyleStyle(PhpWord $phpWord)
    {
        $phpWord->addParagraphStyle(Font::DEFAULT_CHART_TITLE,
            [

                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]
        );
    }

    public function setDefaultTableStyle(PhpWord $phpWord)
    {
        $phpWord->addTableStyle(
            Font::DEFAULT_TABLE_STYLE,
            [
                'borderSize' => 4,
                'borderColor' => '0061ff',
                'cellMargin' => 0,
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                'cellSpacing' => 0,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            ],
            [
                'borderBottomSize' => 4,
                'borderBottomColor' => '0061ff',
                'bgColor' => '006699',
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            ]
        );
    }

    public function setDefaultTitleStyle(PhpWord $phpWord)
    {
        $phpWord->addTitleStyle(
            1,
            ['color' => 'E36C0A', 'size' => Font::DEFAULT_TITLE_SIZE, 'name' => Font::DEFAULT_TITLE_FONT],
            [
                'spaceBefore' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
                'spacing' => 120,
            ]
        );
        $phpWord->addTitleStyle(
            2,
            ['color' => '365F91', 'size' => 13, 'name' => Font::DEFAULT_TEXT_FONT],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'spaceBefore' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(2),
                'spacing' => 120,
            ]
        );
    }
}