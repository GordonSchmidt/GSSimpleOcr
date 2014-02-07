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

namespace GSSimpleOcr\Exception;

use GSOcr\Exception\InvalidArgumentException as GSOcrInvalidArgumentException;

/**
 * Invalid argument exception.
 *
 * @author Gordon Schmidt <schmidt.gordon@web.de>
 */
class InvalidArgumentException extends GSOcrInvalidArgumentException implements Exception
{
}
