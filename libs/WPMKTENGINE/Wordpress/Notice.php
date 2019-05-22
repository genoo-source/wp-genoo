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

namespace WPMKTENGINE\Wordpress;


class Notice
{
    /** @var string */
    public static $notice;
    /** @var string */
    public static $noticeType;
    /** @var string */
    public static $noticeText;
    /** @var array */
    public static $types = array('error', 'updated');


    /**
     * Type of message
     *
     * @param $type
     * @return Notice
     */

    public static function type($type)
    {
        if(!in_array($type, self::$types)){ $type = 'updated'; }
        static::$noticeType = $type;
        return new static;
    }


    /**
     * Actual text
     *
     * @param $text
     * @return Notice
     */

    public static function text($text)
    {
        self::$noticeText = $text;
        return new static;
    }


    /**
     * Renderer
     *
     * @return string
     */

    public function __toString()
    {
        return (string)('<div class="notice strong is-dismissible notice-' . static::$noticeType . '"><p>' . static::$noticeText . '</p></div>');
    }
}