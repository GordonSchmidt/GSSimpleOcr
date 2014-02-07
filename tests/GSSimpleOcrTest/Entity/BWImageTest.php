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

namespace GSSimpleOcrTest\Entity;

use GSImage\Entity\Image;
use GSSimpleOcr\Entity\BWImage;

/**
 * Test model for black and white images.
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class BWImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creation from data
     *
     * @param string $pixels
     * @param int    $width
     * @param int    $height
     * @covers \GSSimpleOcr\Entity\BWImage::__construct
     * @covers \GSSimpleOcr\Entity\BWImage::createFromData
     * @covers \GSSimpleOcr\Entity\BWImage::getPixels
     * @covers \GSSimpleOcr\Entity\BWImage::getWidth
     * @covers \GSSimpleOcr\Entity\BWImage::getHeight
     * @dataProvider provideData
     */
    public function testCreateFromData($pixels, $width, $height)
    {
        $bwImage = new BWImage($pixels, $width, $height);
        $this->assertSame($pixels, $bwImage->getPixels());
        $this->assertSame($width, $bwImage->getWidth());
        $this->assertSame($height, $bwImage->getHeight());
    }

    /**
     * Test creation from data
     *
     * @param string $pixels
     * @param int    $width
     * @param int    $height
     * @covers \GSSimpleOcr\Entity\BWImage::__construct
     * @covers \GSSimpleOcr\Entity\BWImage::createFromData
     * @dataProvider provideInvalidData
     */
    public function testCreateFromDataException($pixels, $width, $height)
    {
        $this->setExpectedException('\GSSimpleOcr\Exception\InvalidArgumentException');
        new BWImage($pixels, $width, $height);
    }

    /**
     * Test creation from data
     *
     * @param GSImage\Entity\Image $image
     * @param int                  $threshold
     * @param string               $pixels
     * @param int                  $width
     * @param int                  $height
     * @covers \GSSimpleOcr\Entity\BWImage::__construct
     * @covers \GSSimpleOcr\Entity\BWImage::createFromImage
     * @covers \GSSimpleOcr\Entity\BWImage::getPalette
     * @covers \GSSimpleOcr\Entity\BWImage::getGray
     * @covers \GSSimpleOcr\Entity\BWImage::getPixels
     * @covers \GSSimpleOcr\Entity\BWImage::getWidth
     * @covers \GSSimpleOcr\Entity\BWImage::getHeight
     * @dataProvider provideImage
     */
    public function testCreateFromImage(Image $image, $threshold, $pixels, $width, $height)
    {
        $bwImage = new BWImage($image, $threshold);
        $this->assertSame($pixels, $bwImage->getPixels());
        $this->assertSame($width, $bwImage->getWidth());
        $this->assertSame($height, $bwImage->getHeight());
    }

    /**
     * Test creation from data
     *
     * @param \GSSimpleOcr\Entity\BWImage $image
     * @param \GSSimpleOcr\Entity\BWImage $subImage
     * @param array                       $matches
     * @covers \GSSimpleOcr\Entity\BWImage::findSubImage
     * @covers \GSSimpleOcr\Entity\BWImage::getAsRegExp
     * @dataProvider provideSubImage
     */
    public function testFindSubImage($image, $subImage, $matches)
    {
        $this->assertSame($matches, $image->findSubImage($subImage));
    }

    /**
     * Provide valid data for creation
     *
     * @return array
     */
    public function provideData()
    {
        return array(
            array('1010', 2, 2),
            array('11110000', 4, 2),
            array('101010101', 3, 3),
        );
    }

    /**
     * Provide invalid data for creation
     *
     * @return array
     */
    public function provideInvalidData()
    {
        return array(
            array(1010, 2, 2),
            array(null, 2, 2),
            array(array(1, 0, 1, 0), 2, 2),
            array('abab', 2, 2),
            array('1010', 3, 2),
            array('101010101', 6, 1.5),
            array('101010101', '3', 3),
        );
    }

    /**
     * Provide image for creation
     *
     * @return array
     */
    public function provideImage()
    {
        $pixels = require('tests/assets/test.php');
        $basePath = dirname(dirname(dirname(__DIR__)));
        return array(
            array(new Image($basePath . '/vendor/gs/image/tests/assets/test.gif'), 127, $pixels, 30, 5),
            array(new Image($basePath . '/vendor/gs/image/tests/assets/test.jpg'), 127, $pixels, 30, 5),
            array(new Image($basePath . '/vendor/gs/image/tests/assets/test.png'), 127, $pixels, 30, 5),
        );
    }

    /**
     * Provide image, sub image and matches
     *
     * @return array
     */
    public function provideSubImage()
    {
        return array(
            array(
                new BWImage('01110010101111100101', 5, 4),
                new BWImage('111101111', 3, 3),
                array(array('x' => 1, 'y' => 0))
            ),
            array(
                new BWImage('010101100101011', 5, 3),
                new BWImage('0111', 2, 2),
                array(array('x' => 0, 'y' => 0), array('x' => 3, 'y' => 1))
            ),
            array(new BWImage('0110', 2, 2), new BWImage('011011011', 3, 3), array()),
        );
    }
}
