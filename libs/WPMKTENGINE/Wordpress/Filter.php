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

use WPMKTENGINE\Utils\Strings;

class Filter
{
    /** @var  */
    static $tag;


    /**
     * Get filters
     */

    public static function filters()
    {
        global $wp_filter;
        $hooks = $wp_filter;
        ksort($hooks);
        return $hooks;
    }


    /**
     * Get all
     *
     * @return array
     */

    public static function getAll()
    {
        return self::filters();
    }


    /**
     * Get tag
     *
     * @param string $tag
     * @return null
     */

    public static function get($tag = '')
    {
        global $wp_filter;
        if (isset($wp_filter[$tag]) && is_array($wp_filter[$tag])){
            return $wp_filter[$tag];
        }
        if (isset($wp_filter[$tag]) && is_object($wp_filter[$tag])){
            return $wp_filter[$tag]->callbacks;
        }
        return null;
    }


    /**
     * Add filter
     *
     * @param $tag
     * @param $f
     * @param int $p
     * @param null $args
     */

    public static function add($tag, $f, $p = 10, $args = null)
    {
        add_filter($tag, $f, $p, $args);
    }


    /**
     * Remove filter
     *
     * @param $tag
     * @param $f
     * @param int $p
     */

    public static function remove($tag, $f, $p = 10)
    {
        remove_filter($tag, $f, $p);
    }


    /** ----------------------------------------------------- */
    /**                   Static bindings                     */
    /** ----------------------------------------------------- */

    /**
     * Select
     *
     * @param string $tag
     * @return Filter
     */

    public static function select($tag = '')
    {
        self::$tag = $tag;
        return new static;
    }


    /**
     * Remove from (hook)
     *
     * @param string $tag
     * @return Filter
     */

    public static function removeFrom($tag = '')
    {
        self::select($tag);
        return new static;
    }


    /**
     * Everything Except %LIKE%
     *
     * @param string $like
     */

    public static function everythingExceptLike($like = null)
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go through
            foreach($filters as $priority => $hooks){
                // functions
                if(is_array($hooks)){
                    // go through hooked functions
                    foreach($hooks as $hook){
                        // do we have a winner here?
                        // hook that is not like excpected one? is it string / array arg?
                        if(is_array($like)){
                            foreach($like as $lik){
                                $remove = false;
                                if(!Strings::contains((string)$hook['function'], (string)$lik)){
                                    $remove = true;
                                }
                            }
                            // none of those functions in array is the hold one, remove hook
                            if($remove){
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        } elseif (is_string($like)){
                            if(!Strings::contains((string)$hook['function'], (string)$like)){
                                // remove hook
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $like
     */
    public static function everythingThatStartsWith($like)
    {
        $filters = self::get(self::$tag);
        if($filters && is_array($filters) && !empty($filters)){
            // hooks, go through
            foreach($filters as $priority => $hooks){
                // functions
                if(is_array($hooks)){
                    // go through hooked functions
                    foreach($hooks as $hook){
                        // do we have a winner here?
                        // hook that is not like excpected one? is it string / array arg?
                        if(is_array($like)){
                            foreach($like as $lik){
                                $remove = false;
                                if(Strings::startsWith((string)$hook['function'], (string)$lik)){
                                    $remove = true;
                                }
                            }
                            // none of those functions in array is the hold one, remove hook
                            if($remove){
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        } elseif (is_string($like) && is_string($hook['function'])){
                            if(Strings::startsWith((string)$hook['function'], (string)$like)){
                                // remove hook
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Removed already hooked functions,
     * followes after "removeFrom->" static binding
     *
     * @param $function
     */

    public static function hooked($function)
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go through
            foreach($filters as $priority => $hooks){
                if(is_array($hooks)){
                    // go through hooked functions
                    foreach($hooks as $hook){
                        if(Strings::contains((string)$hook['function'], (string)$function)){
                            // remove hook
                            self::remove(self::$tag, $hook['function'], $priority);
                        }
                    }
                }
            }
        }
    }


    /**
     * Removes everything hooked to "action",
     * binds to "removeFrom->" to remove everything hooked there.
     */

    public static function everything()
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go through
            foreach($filters as $priority => $hooks){
                // functions
                if(is_array($hooks)){
                    // go through hooked functions
                    foreach($hooks as $hook){
                        // do we have a winner here?
                        // hook that is not like excpected one? is it string / array arg?
                        self::remove(self::$tag, $hook['function'], $priority);
                    }
                }
            }
        }
    }
}
