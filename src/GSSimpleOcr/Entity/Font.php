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

use GSSimpleOcr\Exception;

/**
 * Model for a font
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class Font
{
    /**
     * All known characters as assoiative array of BWImages
     *
     * @var BWImage[]
     */
    protected $glyphs = array();

    /**
     * Default width of every glyph
     *
     * @var int
     */
    protected $defaultWidth = 1;

    /**
     * Default height of every glyph
     *
     * @var int
     */
    protected $defaultHeight = 1;

    /**
     * Create font from options
     *
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['width'])) {
            $this->defaultWidth = $options['width'];
        }
        if (isset($options['height'])) {
            $this->defaultHeight = $options['height'];
        }
        if (isset($options['glyphs'])) {
            $this->addGlyphs($options['glyphs']);
        }
    }

    /**
     * Add glyphs to font
     *
     * @param BWImage[]
     */
    public function addGlyphs($glyphs)
    {
        foreach ($glyphs as $char => $glyph) {
            if (is_string($glyph)) {
                $glyph = new BWImage($glyph, $this->defaultWidth, $this->defaultHeight);
            } else if (is_array($glyph)) {
                $glyph = new BWImage($glyph['data'], $glyph['width'], $glyph['height']);
            }
            $this->glyphs[$char] = $glyph;
        }
    }

    /**
     * Get glyphs of font
     *
     * @return BWImage[]
     */
    public function getGlyphs()
    {
        return $this->glyphs;
    }
}
