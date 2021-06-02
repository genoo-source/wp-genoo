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
 * Class Base
 *
 * @package WPME\WPApi
 */
abstract class Base
{

    public static function register()
    {
        self::registerRSSFeed();
        self::registerApi();
    }

    public static function getData(){}
    public static function getEndpoint(){}

    /**
     * JSON API
     */
    public static function registerApi()
    {
        add_action('rest_api_init', function(){
            $class = get_called_class();
            register_rest_route(
                'wpmktengine', '/' . call_user_func_array(array(get_called_class(), 'getEndpoint'), array()),
                array(
                    'methods' => 'GET',
                    'callback' => array(get_called_class(), 'getData'),
                    'permission_callback' => '__return_true'
                )
            );
        });
    }

    /**
     * RSS FEED
     */
    public static function registerRSSFeed()
    {
        // Add feed renderer
        add_feed(
            'wpmktengine_' . call_user_func_array(array(get_called_class(), 'getEndpoint'), array()),
            function () {
                // Send headers
                self::sendJsonHeader();
                // Get data
                $data = array();
                $data =  call_user_func_array(array(get_called_class(), 'getData'), array());
                // Return
                echo \WPMKTENGINE\Utils\Json::encode($data);
                die;
        });
    }

    public static function sendJsonHeader()
    {
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60))); // 1 hour
        header('Content-type: application/json');
    }
}
