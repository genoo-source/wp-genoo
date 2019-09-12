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

namespace WPME\Extensions\LandingPages;

/**
 * Class LandingPages
 *
 * @package WPME\Extensions\LandingPages
 */
class LandingPages
{
    /**
     * Register
     */
    public static function register()
    {
        // Return landing page URL as permalink
        add_filter('post_type_link', function ($url, $post){
            //
            if (
                'wpme-landing-pages' == get_post_type($post)
                ||
                'wpme_landing_pages' == get_post_type($post)
            ){
                $link = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
                $link = \WPMKTENGINE\RepositoryLandingPages::base() . $link;
                return $link;
            }
            return $url;
        }, 999, 2);
        // New landing metabox
        $metabox = new \WPME\Extensions\LandingPages\Metabox('wpme-landing-pages');
    }
}