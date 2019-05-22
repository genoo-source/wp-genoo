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

namespace WPME\WPApi;

/**
 * Class CTAs
 * /?feed=wpmktengine_cta
 * /wp-json/wpmktengine/cta
 *
 *
 * @package WPME\WPApi
 */
class CTAs extends \WPME\WPApi\Base
{
    /**
     * @param null $request
     */
    public static function getData($request = null)
    {
        global $WPME_API;
        global $WPME_CACHE;
        $data = array();
        try {
            $ctas = new \WP_Query(
                array(
                    'post_type' => 'cta',
                    'posts_per_page' => -1,
                )
            );
            // Sort data
            if (isset($ctas->posts) && !empty($ctas->posts) && is_array($ctas->posts)) {
                foreach ($ctas->posts as $cta) {
                    // Start assigning
                    $data[$cta->ID]['id'] = $cta->ID;
                    $data[$cta->ID]['title'] = $cta->post_title;
                    // Get post meta
                    $meta = get_post_meta($cta->ID);
                    // Remove unwanted post meta
                    if (isset($meta['_edit_last'])) {
                        unset($meta['_edit_last']);
                    }
                    if (isset($meta['_edit_lock'])) {
                        unset($meta['_edit_lock']);
                    }
                    // Filter
                    if (is_array($meta) && !empty($meta)) {
                        // Add image
                        foreach ($meta as $key => $met) {
                            if (is_array($met) && isset($met[0]) && !empty($met[0])) {
                                $data[$cta->ID][$key] = $met[0];
                                if ($key == 'formpop') {
                                    $data[$cta->ID][$key] = unserialize($met[0]);
                                }
                                if(self::is_serialized($met[0])){
                                    $data[$cta->ID][$key] = unserialize($met[0]);
                                }
                                if($key === 'button_image' && is_numeric($met[0])){
                                    $id = $met[0];
                                    $data[$cta->ID][$key] = array();
                                    $data[$cta->ID][$key]['__src'] = wp_get_attachment_url($id);
                                    $data[$cta->ID][$key]['__id'] = $id;
                                }
                                if($key === 'button_hover_image' && is_numeric($met[0])){
                                    $id = $met[0];
                                    $data[$cta->ID][$key] = array();
                                    $data[$cta->ID][$key]['__src'] = wp_get_attachment_url($id);
                                    $data[$cta->ID][$key]['__id'] = $id;
                                }
                            }
                        }
                    }

                    // Unset meta
                    unset($meta);
                }
            }
            return $data;
        } catch(\Exception $e){
            return $data;
        }
    }

    public static function is_serialized($string) {
        return (@unserialize($string) !== false);
    }

    /**
     * @return string
     */
    public static function getEndpoint()
    {
        return 'cta';
    }
}