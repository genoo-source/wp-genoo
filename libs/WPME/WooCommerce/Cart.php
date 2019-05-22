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

namespace WPME\WooCommerce;

use WPME\WooCommerce\Product;

/**
 * Class Cart
 *
 * @package WPME\WooCommerce
 */
class Cart
{

    /**
     * @param array $cart_contents
     *
     * @return array
     */
    public static function convertCartToObject($cart_contents = array())
    {
        // Return
        $r = array();
        // Only if it's array
        if(is_array($cart_contents) && !empty($cart_contents)){
            foreach($cart_contents as $object_id => $item)
            {
                $id = (int)get_post_meta($item['product_id'], WPMKTENGINE_PRODUCT_KEY, TRUE);
                if(is_numeric($id) && $id > 0){
                    $array['product_id'] = $id;
                    $array['quantity'] = $item['quantity'];
                    $array['total_price'] = $item['line_total'];
                    $array['unit_price'] = $item['line_total'] / $array['quantity'];
                    $array['external_product_id'] = $item['product_id'];
                    $array['name'] = get_the_title($item['product_id']);
                    $r[] = $array;
                }
            }
        }
        return $r;
    }

    /**
     * @param array $cart_contents
     *
     * @return array
     */
    public static function convertOrderToObject($cart_contents = array())
    {
        // Return
        $r = array();
        // Only if it's array
        if(is_array($cart_contents) && !empty($cart_contents)){
            foreach($cart_contents as $object_id => $item)
            {
                $itemData = $item['item_meta_array'];
                if(is_array($itemData) && !empty($itemData)){
                    foreach($itemData as $key => $object) {
                        switch($object->key){
                            case '_product_id':
                                $array['product_id'] = $object->value;
                                break;
                            case '_qty':
                                $array['quantity'] = (int)$object->value;
                                break;
                        }
                    }
                }
                // Meta
                $id = (int)get_post_meta($array['product_id'], WPMKTENGINE_PRODUCT_KEY, TRUE);
                if(!empty($id)){
                    $array['product_id'] = $id;
                    $r[] = $array;
                }
            }
        }
        return $r;
    }
}