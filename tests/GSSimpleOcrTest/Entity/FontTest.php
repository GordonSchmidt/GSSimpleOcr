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

use GSSimpleOcr\Entity\BWImage;
use GSSimpleOcr\Entity\Font;

/**
 * Test model for fonts.
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class FontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creation
     *
     * @param int   $width
     * @param int   $height
     * @param array $glyphsConfig
     * @param array $glyphs
     * @covers \GSSimpleOcr\Entity\Font::__construct
     * @covers \GSSimpleOcr\Entity\Font::addGlyphs
     * @covers \GSSimpleOcr\Entity\Font::getGlyphs
     * @dataProvider provideConstruct
     */
    public function testConstruct($width, $height, $glyphsConfig, $glyphs)
    {
        $font = new Font(array('width' => $width, 'height' => $height, 'glyphs' => $glyphsConfig));
        $this->assertEquals($glyphs, $font->getGlyphs());
    }

    /**
     * Provide valid data for creation
     *
     * @return array
     */
    public function provideConstruct()
    {
        $config1 = array('0' => '111101111', '1' => '010010010');
        $config2 = array('0' => array('data' => '111101111', 'width' => 3, 'height' => 3), '1' => array('data' => '010010010', 'width' => 3, 'height' => 3));
        $result1 = array('0' => new BWImage('111101111', 3, 3), '1' => new BWImage('010010010', 3, 3));
        return array(
            array(0, 0, array(), array()),
            array(3, 3, $config1, $result1),
            array(0, 0, $config2, $result1),
        );
    }
}
