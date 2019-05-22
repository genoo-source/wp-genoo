<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General public static License Ver. 2 (GPL)
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

namespace WPME\Customizer;

/**
 * Class Dummy_Customize
 * - This class servers as a "placeholder" or dummy class
 * for wp_customize, so we can use it in frotend without
 * breaking the renderer script which was designed bit poorly
 * in this regard.
 *
 *
 * @package WPME\Customizer
 */
class DummyCustomize
{

    /**
     * @param string $url
     */
    public function set_return_url($url = ''){}

    /**
     * @param string $url
     */
    public function set_autofocus($url = ''){}

    /**
     * @param string $url
     */
    public function set_preview_url($url = ''){}

    /**
     * @param string $id
     * @param array $args
     */
    public function add_control($id = '', $args = array()){}

    /**
     * @param string $id
     * @param array $args
     */
    public function add_setting($id = '', $args = array()){}

    /**
     * @param string $id
     * @param array $args
     */
    public function add_section($id = '', $args = array()){}
}