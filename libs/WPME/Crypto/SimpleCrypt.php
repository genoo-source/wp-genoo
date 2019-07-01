<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General Public License Ver. 2 (GPL)
 *  Licensed "As-Is"; all warranties are disclaimed.
 *  HTML: http://www.gnu.org/copyleft/gpl.html
 *  Text: http://www.gnu.org/copyleft/gpl.txt
 *
 * Proprietary Licensing:
 *  Remaining code elements, including without limitation:
 *  images, cascading style sheets, and JavaScript elements
 *  are licensed under restricted license.
 *  http://www.wpmktgengine.com/terms-of-service
 *  Copyright 2016 Genoo LLC. All rights reserved worldwide.
 */

namespace WPME\Crypto;

/**
 * Class SimpleCrypt
 */
class SimpleCrypt
{

	/**
	 * "Encrypt", well, sort of.
	 *
	 * @param string $string
	 * @return string
	 */
    public static function encrypt($string = '')
    {
	    // Random two at begining
	    $randomFirst  = self::getRandomString(2);
	    // Randomg two at the end
	    $randomSecond  = self::getRandomString(2);
        return $randomFirst . base64_encode($string) . $randomSecond;
    }

	/**
	 * "Decrypt", sort of
	 *
	 * @param string $string
	 * @return string
	 */
    public static function decrypt($string = '')
    {
	    // Remove first two
	    $string = substr($string, 2);
	    // Remove last two
	    $string = substr($string, 0, -2);
        return base64_decode($string);
    }

	/**
	 * Random String
	 *
	 * @param int $length
	 * @return string
	 */
	public static function getRandomString($length = 12)
	{
		return substr(str_shuffle("=012345=!6789abcdefghi=jklmnopqrstuvwx===yzABCDEFGHIJKLMNOPQRSTUVWXYZ=_!"), 0, $length);
	}
}