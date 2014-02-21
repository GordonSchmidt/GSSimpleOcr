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

namespace GSSimpleOcr\Service;

use GSImage\Entity\Image;
use GSOcr\Service\OcrServiceInterface;
use GSSimpleOcr\Exception;
use GSSimpleOcr\Entity\Font;
use GSSimpleOcr\Entity\BWImage;

/**
 * This class provides a simple OCR service.
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class SimpleOcrService implements OcrServiceInterface
{
    const MODE_GLYPHS = 0;
    const MODE_LINES = 1;
    const MODE_WORDS = 2;
    const MODE_ALL = 3;
    const MODE_TEXT = 4;

    /**
     * Maximum variation in the y for 2 glyphs to be on the same line
     * @var int
     */
    protected $maxYVariation = 1;

    /**
     * Maximum variation in the x for 2 glyphs to be in the same word
     * @var int
     */
    protected $maxXVariation = 2;

    /**
     * Seperator for 2 glyphs
     * @var string
     */
    protected $glyphSeperator = '';

    /**
     * Seperator for 2 words
     * @var string
     */
    protected $wordSeperator = ' ';

    /**
     * Seperator for 2 lines
     * @var string
     */
    protected $lineSeperator = PHP_EOL;

    /**
     * List of fonts
     * @var array
     */
    protected $fonts = array();

    /**
     * Set config to ocr service
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        if (isset($config['fonts'])) {
            $this->setFonts($config['fonts']);
        }
        if (isset($config['maxYVariation'])) {
            $this->maxYVariation = $config['maxYVariation'];
        }
        if (isset($config['maxXVariation'])) {
            $this->maxXVariation = $config['maxXVariation'];
        }
    }

    /**
     * Recognize image
     *
     * @param string $image
     * @param array  $options
     * @return string
     */
    public function recognize($image, $options)
    {
        if (isset($options['threshold'])) {
            $threshold = $options['threshold'];
        } else {
            $threshold = 127;
        }
        if (isset($options['mode'])) {
            $mode = $options['mode'];
        } else {
            $mode = self::MODE_TEXT;
        }
        $image = new Image($image);
        $bwImage = new BWImage($image, $threshold);
        if (isset($options['rotate']) && in_array($options['rotate'], array(-90, 90, 180, 270))) {
            $bwImage->rotate($options['rotate']);
        }
        return $this->workflow($bwImage, $mode, array(
            'recognizeGlyphs',
            'recognizeLines',
            'recognizeWords',
            'generateTexts',
        ));
    }

    /**
     * Perform workflow of given steps
     *
     * @param mixed $input
     * @param int   $mode
     * @param array $steps
     * @return mixed
     */
    protected function workflow($input, $mode, $steps)
    {
        if (empty($steps)) {
            //output only text
            return $input['text'];
        }

        //array_assoc_shift
        list($stepMode) = array_keys($steps);
        $stepMethod = $steps[$stepMode];
        unset($steps[$stepMode]);

        //execute workflow step
        $input = $this->$stepMethod($input);

        //stop if mode is reached
        if ($mode == $stepMode) {
            return $input;
        }

        //recursive call
        return $this->workflow($input, $mode, $steps);
    }

    /**
     * Recognize glyphs of known fonts in black and white image
     *
     * @param GSSimpleOcr\Entity\BWImage $image
     * @param int GSSimpleOcr\Entity\BWImage $image
     * @return array
     */
    protected function recognizeGlyphs(BWImage $image)
    {
        $chars = array();
        foreach ($this->fonts as $font) {
            $glyphs = $font->getGlyphs();
            foreach ($glyphs as $c => $glyph) {
                $results = $image->findSubImage($glyph);
                if (is_array($results)) {
                    $w = $glyph->getWidth();
                    $h = $glyph->getHeight();
                    foreach ($results as $result) {
                        $chars[] = array('text' => $c, 'x' => $result['x'], 'y' => $result['y'], 'w' => $w, 'h' => $h);
                    }
                }
            }
        }
        return array('glyphs' => $chars);
    }


    /**
     * Recognize lines in data array
     *
     * @param array $data
     */
    protected function recognizeLines($data)
    {
        $glyphs = $data['glyphs'];
        $this->sortByKey('y', $glyphs);
        $last = array(array_shift($glyphs));
        $lines = array();
        foreach ($glyphs as $glyph) {
            if ($this->maxYVariation >= abs($last[0]['y'] - $glyph['y'])) {
                $last[] = $glyph;
            } else {
                $lines[] = array('glyphs' => $last);
                $last = array($glyph);
            }
        }
        if (!empty($last)) {
            $lines[] = array('glyphs' => $last);
        }
        return array('lines' => $lines);
    }

    /**
     * Recognize words in char array
     *
     * @param array $data
     */
    protected function recognizeWords($data)
    {
        foreach ($data['lines'] as &$line) {
            $glyphs = $line['glyphs'];
            $this->sortByKey('x', $glyphs);
            $latest = array_shift($glyphs);
            $last = array($latest);
            $words = array();
            foreach ($glyphs as $glyph) {
                if (($this->maxXVariation + $latest['w']) > abs($glyph['x'] - $latest['x'])) {
                    $latest = $glyph;
                    $last[] = $latest;
                } else {
                    $latest = $glyph;
                    $words[] = array('glyphs' => $last);
                    $last = array($latest);
                }
            }
            if (!empty($last)) {
                $words[] = array('glyphs' => $last);
            }
            $line = array('words' => $words);
        }
        return $data;
    }

    /**
     * Generate texts from data array
     *
     * @param array $data
     */
    protected function generateTexts($data)
    {
        foreach ($data['lines'] as &$line) {
            foreach ($line['words'] as &$word) {
                $word['text'] = $this->implodeByKey($this->glyphSeperator, 'text', $word['glyphs']);
            }
            $line['text'] = $this->implodeByKey($this->wordSeperator, 'text', $line['words']);
        }
        $data['text'] = $this->implodeByKey($this->lineSeperator, 'text', $data['lines']);
        return $data;
    }

    /**
     * Set Fonts to simple ocr service
     *
     * @param array $fonts
     * @return self
     */
    protected function setFonts($fonts)
    {
        $this->fonts = array();
        foreach ($fonts as $font) {
            if ($font instanceof Font) {
                $this->fonts[] = $font;
            } else {
                $this->fonts[] = new Font($font);
            }
        }
        return $this;
    }

    /**
     * Order data by given key
     *
     * @param string $key
     * @param array  &$data
     * @return array
     */
    protected function sortByKey($key, &$data)
    {
        return usort($data, $this->generateSorter($key));
    }

    /**
     * Generate a sort function for given key
     *
     * @param string $key
     * @return function
     */
    protected function generateSorter($key)
    {
        return function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        };
    }

    /**
     * Implode data by given key
     *
     * @param string $seperator
     * @param string $key
     * @param array  $data
     * @return function
     */
    protected function implodeByKey($seperator, $key, $data)
    {
        $values = array();
        foreach ($data as $value) {
            $values[] = $value[$key];
        }
        return implode($seperator, $values);
    }
}
