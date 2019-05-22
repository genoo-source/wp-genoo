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


class Action
{

    /**
     * Add an Action
     *
     * @param $h
     * @param $f
     * @param int $p
     * @param null $args
     */

    public static function add($h, $f, $p = 10, $args = 1)
    {
        add_action($h, $f, $p, $args);
    }


    /**
     * Remove Action
     *
     * @param $t
     * @param $f
     * @param null $p
     */

    public static function remove($t, $f, $p = null)
    {
        remove_action($t, $f, $p);
    }


    /**
     * Remove All
     *
     * @param $t
     * @param null $p
     */

    public static function removeAll($t, $p = null)
    {
        remove_all_actions($t, $p);
    }


    /**
     * Has Action?
     *
     * @param $t
     * @param $f
     */

    public static function has($t, $f)
    {
        return has_action($t, $f);
    }



    /**
     * Run Action (do_action)
     *
     * @param $t
     * @param $args 
     */

    public static function run($t, $arg)
    {
        // Deconstruct arguments
        $args = array();
        if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ){
            $args[] =& $arg[0];
        } else {
            $args[] = $arg;
            for ($a = 2, $num = func_num_args(); $a < $num; $a++){
                $args[] = func_get_arg($a);
            }
        }
        // Push tag as first
        array_unshift($args, $t);
        // Call do_action
        call_user_func_array('do_action', $args);
    }
}