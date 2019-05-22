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

namespace WPME\Customizer\Generator;

/**
 * Class Template
 *
 * @package WPME\Customizer\Generator
 */
class Template
{

    /**
     * Is default theme?
     *
     * @param $post
     * @return bool
     */
    public function isDefaultTheme($post)
    {
        $currentTheme = get_post_meta($post, 'form_theme', true);
        return ($currentTheme == 'themeDefault' || $currentTheme == '');
    }

    /**
     * @return mixed
     */
    public function getTheme($post)
    {
        return get_post_meta($post, 'form_theme', true);
    }

    /**
     * Is default modal theme?
     *
     * @param $post
     * @return bool
     */
    public function isDefaultModalTheme($post)
    {
        $currentTheme = get_post_meta($post, '_wpme_modal_theme', true);
        return ($currentTheme == 'default' || $currentTheme == '' || empty($currentTheme));
    }

    /**
     * @param $post
     * @return mixed
     */
    public function getModalTheme($post)
    {
        if($this->isDefaultModalTheme($post)){
            return 'default';
        }
        return get_post_meta($post, '_wpme_modal_theme', true);
    }
}