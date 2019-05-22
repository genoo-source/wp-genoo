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

use WPMKTENGINE\RepositorySettings;
use WPMKTENGINE\Tracer;
use WPMKTENGINE\Utils\Json;
use WPMKTENGINE\Nette\Utils\SafeStream;

class Sidebars
{

    /**
     * Get All Sidebars
     *
     * @return mixed
     */

    public static function getAll()
    {
        global $wp_registered_sidebars;
        return $wp_registered_sidebars;
    }


    /**
     * Get all as id => name array
     *
     * @return array
     */
    public static function getSidebars()
    {
        $r = array();
        $sidebars = self::getAll();
        if($sidebars){
            $r[] = __('Select Sidebar', 'wpmktengine');
            foreach($sidebars as $name => $sidebar){
                $r[$name] = $sidebar['name'];
            }
        }
        return $r;
    }


    /**
     * Does sidebar exists?
     *
     * @param $key
     * @return bool
     */

    public static function exists($key){ return array_key_exists($key, self::getAll()); }


    /**
     * Un-register
     *
     * @param $name
     */

    public static function unRegister($name)
    {
        unregister_sidebar($name);
    }


    /**
     * Register Widget
     *
     * @param $id
     * @param $name
     * @param $output_callback
     * @param array $options
     */

    public static function registerWidget($id, $name, $output_callback, $options = array())
    {
        wp_register_sidebar_widget($id, $name, $output_callback, $options);
    }


    /**
     * Unregister Sidebar Widget
     *
     * @param $id
     */

    public static function unRegisterWidget($id)
    {
        wp_unregister_sidebar_widget($id);
    }
}