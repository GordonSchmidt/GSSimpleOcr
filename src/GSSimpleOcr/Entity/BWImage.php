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

namespace GSSimpleOcr\Entity;

use GSImage\Entity\Image;
use GSSimpleOcr\Exception;

/**
 * Model for a black and white image
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class BWImage
{
    /**
     * All pixels of image as string '0' = black, '1' = white
     *
     * @var string
     */
    protected $pixels;

    /**
     * Width of image
     *
     * @var int
     */
    protected $width;

    /**
     * Height of image
     *
     * @var int
     */
    protected $height;

    /**
     * Get pixels of image
     *
     * @return string
     */
    public function getPixels()
    {
        return $this->pixels;
    }

    /**
     * Get width of image
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get height of image
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Create image
     *
     * @param string|GSImage\Entity\Image $pixels
     * @param int                         $width
     * @param int                         $height
     */
    public function __construct($pixels = '', $width = 0, $height = 0)
    {
        if ($pixels instanceof Image) {
            $this->createFromImage($pixels, $width);
        } else {
            $this->createFromData($pixels, $width, $height);
        }
    }

    /**
     * Create image from image data
     *
     * @param string $pixels
     * @param int    $width
     * @param int    $height
     * @return self
     */
    public function createFromData($pixels, $width, $height)
    {
        if (!is_string($pixels) || 0 !== preg_match('/[^01]/', $pixels)) {
            throw new Exception\InvalidArgumentException('invalid pixel string');
        }
        if (!is_int($width) || !is_int($height)  || strlen($pixels) !== $width * $height) {
            throw new Exception\InvalidArgumentException('invalid width or heigth');
        }
        $this->pixels = $pixels;
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    /**
     * Create image from image resource
     *
     * @param GSImage\Entity\Image $image
     * @param int                  $threshold
     * @return self
     */
    public function createFromImage(Image $image, $threshold = 127)
    {
        $pixels = '';
        $width = $image->getWidth();
        $height = $image->getHeight();
        $palette = $this->getPalette($threshold);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = $image->getPixel($x, $y);
                $gray = $this->getGray($rgb);
                $pixels .= $palette[$gray];
            }
        }
        return $this->createFromData($pixels, $width, $height);
    }

    /**
     * Find all occurrences of subimage within this image
     *
     * @param BWImage $subImage
     * @return array
     */
    public function findSubImage(BWImage $subImage)
    {
        $regExp = $subImage->getAsRegExp($this->width);
        $results = array();
        if (false !== preg_match_all($regExp, $this->pixels, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $offset = $match[1];
                $y = (int) ($offset / $this->width);
                $x = $offset % $this->width;
                $results[] = array('x' => $x, 'y' => $y);
            }
        }
        return $results;
    }

    /**
     * Get pixels als regular expression to find in a bigger image
     *
     * @param int $width
     * @return string
     */
    public function getAsRegExp($width)
    {
        return '/' . implode('.{' . ($width - $this->width) . '}', str_split($this->pixels, $this->width)) . '/';
    }

    /**
     * Get Palette for given treshold
     *
     * @param int threshold
     * @return array
     */
    protected function getPalette($threshold)
    {
        $palette = array();
        for ($c = 0; $c < 256; $c++) {
            $palette[$c] = (($c > $threshold) ? '0' : '1');
        }
        return $palette;
    }

    /**
     * Create grayscale pixel
     *
     * @param int $rgb
     * @return int
     */
    protected function getGray($rgb)
    {
        return (($rgb['red'] * 0.299) + ($rgb['green'] * 0.587) + ($rgb['blue'] * 0.114));
    }
}
