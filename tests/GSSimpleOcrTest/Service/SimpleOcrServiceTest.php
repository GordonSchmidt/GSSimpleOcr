<?php
/**
 * This file is part of GSSimpleOcr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2014 Gordon Schmidt
 * @license   MIT
 */

namespace GSSimpleOcrTest\Service;

use GSSimpleOcr\Entity\BWImage;
use GSSimpleOcr\Entity\Font;
use GSSimpleOcr\Service\SimpleOcrService;

/**
 * Test simple ocr service.
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class SimpleOcrServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of ocr service
     *
     * @var \GSOcr\Service\OcrServiceInterface
     */
    protected $service;

    /**
     * Initialize service instance
     */
    protected function setUp()
    {
        $this->service = new SimpleOcrService(array());
    }

    /**
     * Test setting a config to the ocr service
     *
     * @param array $config
     * @param array $fonts
     * @covers \GSSimpleOcr\Service\SimpleOcrService::setConfig
     * @covers \GSSimpleOcr\Service\SimpleOcrService::setFonts
     * @dataProvider provideConfig
     */
    public function testSetConfig($config, $fonts)
    {
        $this->service->setConfig($config);
        $this->assertAttributeEquals($fonts, 'fonts', $this->service);
    }

    /**
     * Provide data for setConfig method
     *
     * @return array
     */
    public function provideConfig()
    {
        $fontsConfig = array(
            array('width' => 3, 'height' => 3, 'glyphs' => array('*' => '000010000', '/' => '001010100')),
            array('width' => 3, 'height' => 3, 'glyphs' => array('+' => '010111010', '-' => '000111000')),
        );
        $fonts = array(
            new Font(array('width' => 3, 'height' => 3, 'glyphs' => array('*' => new BWImage('000010000', 3, 3), '/' => new BWImage('001010100', 3, 3)))),
            new Font(array('width' => 3, 'height' => 3, 'glyphs' => array('+' => new BWImage('010111010', 3, 3), '-' => new BWImage('000111000', 3, 3)))),
        );
        return array(
            array(null, array()),
            array(array(), array()),
            array(array('fonts' => array()), array()),
            array(array('fonts' => $fontsConfig), $fonts),
            array(array('fonts' => $fonts), $fonts),
        );
    }

    /**
     * Test recognize method
     *
     * @param string $image
     * @param array  $options
     * @param mixed  $text
     * @covers \GSSimpleOcr\Service\SimpleOcrService::recognize
     * @covers \GSSimpleOcr\Service\SimpleOcrService::workflow
     * @covers \GSSimpleOcr\Service\SimpleOcrService::recognizeGlyphs
     * @covers \GSSimpleOcr\Service\SimpleOcrService::recognizeLines
     * @covers \GSSimpleOcr\Service\SimpleOcrService::recognizeWords
     * @covers \GSSimpleOcr\Service\SimpleOcrService::generateTexts
     * @covers \GSSimpleOcr\Service\SimpleOcrService::sortByKey
     * @covers \GSSimpleOcr\Service\SimpleOcrService::generateSorter
     * @covers \GSSimpleOcr\Service\SimpleOcrService::implodeByKey

     * @dataProvider provideRecognize
     */
    public function testRecognize($image, $options, $text)
    {
        $config = array('fonts' => array(require('tests/assets/font.php')));
        $this->service->setConfig($config);
        $result = $this->service->recognize($image, $options);
        $this->assertEquals($text, $result);
    }

    /**
     * Provide data for recognize method
     *
     * @return array
     */
    public function provideRecognize()
    {
        $glyphsResult = array('glyphs' => array(
            array('text' => 0, 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 1, 'x' => 3, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 2, 'x' => 6, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 3, 'x' => 9, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 4, 'x' => 12, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 5, 'x' => 15, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 6, 'x' => 18, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 7, 'x' => 21, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 8, 'x' => 24, 'y' => 0, 'w' => 3, 'h' => 5),
            array('text' => 9, 'x' => 27, 'y' => 0, 'w' => 3, 'h' => 5),
        ));
        $basePath = dirname(dirname(dirname(__DIR__)));
        return array(
            array($basePath . '/tests/assets/test2.png', array(), '23 567' . PHP_EOL . '01 4 89'),
            array($basePath . '/vendor/gs/image/tests/assets/test.gif', array('mode' => SimpleOcrService::MODE_GLYPHS), $glyphsResult),
            array($basePath . '/vendor/gs/image/tests/assets/test.jpg', array('threshold' => 16), '0123456789'),
            array($basePath . '/vendor/gs/image/tests/assets/test.png', array(), '0123456789'),
        );
    }
}
