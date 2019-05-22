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

namespace WPMKTENGINE;

use WPMKTENGINE\Utils\Strings;


class Tracer
{

    /**
     * Was this file in trace of function leading to it?
     *
     * @param $filename
     * @return bool
     */

    public static function ranFrom($filename)
    {
        $trace = debug_backtrace();
        foreach($trace as $file){
            if(isset($file['file'])){
                if(Strings::endsWith($file['file'], '/' . $filename)){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Debug
     *
     * @param $stuff
     * @return mixed
     */

    public static function debug($stuff)
    {
        if(class_exists('\Tracy\Debugger')){
            return \Tracy\Debugger::dump($stuff);
        }
        return false;
    }


    /**
     * Debug bar
     *
     * @param $stuff
     * @param null $title
     * @return bool
     */

    public static function debugBar($stuff, $title = null)
    {
        if(class_exists('\Tracy\Debugger')){
            return \Tracy\Debugger::barDump($stuff, $title);
        }
        return false;
    }
}