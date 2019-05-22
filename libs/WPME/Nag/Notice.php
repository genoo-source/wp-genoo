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

namespace WPME\Nag;

/**
 * Class Nag
 * Bit more clever nag the ones before
 *
 * @package WPME\Nag
 */
class Notice extends \WPMKTENGINE\Wordpress\Notice
{
    /** @var string  */
    public static $noticeDissmisiable = 'is-dismissible';

    /**
     * Renderer
     *
     * @return string
     */
    public function __toString()
    {
        return (string)('<div class="notice wpme-notice '. static::$noticeDissmisiable .' notice-' . static::$noticeType . '">' . static::$noticeText . '</div>');
    }
}