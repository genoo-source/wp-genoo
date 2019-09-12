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

namespace WPME\Ecommerce;

/**
 * Class CartOrder
 *
 * @package WPME\Ecommerce
 */
class CartOrder
{
    /** @var int */
    public $id;
    /** @var array */
    public $_order_status = array('cart', 'order');
    /** @var array */
    public $_financial_status = array('unpaid', 'paid');
    /** @type array */
    public $_actions = array('new cart', 'new order', 'order fulfillment', 'order refund full', 'order refund partial', 'order cancelled',);
    /** @var object */
    public $object;
    /** @var object */
    public $changed;
    /** @type array */
    public $items;
    /** @type \WPMKTENGINE\Api | \Genoo\Api */
    private $api;

    /**
     * Constants
     */
    const STATUS_PAID = 'unpaid';
    const STATUS_UNPAID = 'paid';
    const STATUS_ORDER = 'order';
    const STATUS_CART = 'cart';
    const STATUS_FULFILLMENT = 'shipped';

    /**
     * @param null $order_id
     */
    public function __construct($order_id = NULL)
    {
        // Prep object
        $this->object = (object)(array(
            'action' => '',
            'completed_date' => '',
            'cancel_reason' => '',
            'cancel_date' => '',
            'refund_reason' => '',
            'refund_date' => '',
            'refund_amount' => '',
            'ship_date' => '',
            'currency' => '',
            'financial_status' => '',
            'fulfillment_status' => '',
            'ip' => '',
            'line_items' => array (), // 0 =>(object)(array('product_id' => 10022,'quantity' => 2,)),
            'note' => '',
            'order_number' => '',
            'order_shipped' => '',
            'order_status' => '',
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_price' => 0,
            'user_email' => '',
            'user_lid' => NULL, // lead_id
            'weight' => 0.0, // int
            'billing_address' =>
                (object)(array(
                    'address1' => '',
                    'address2' => '',
                    'city' => '',
                    'country' => '',
                    'phone' => '',
                    'postal_code' => '',
                    'province' => '',
                    'state' => '',
                )),
            'shipping_address' =>
                (object)(array(
                    'address1' => '',
                    'address2' => '',
                    'city' => '',
                    'country' => '',
                    'phone' => '',
                    'postal_code' => '',
                    'province' => '',
                    'state' => '',
                )),
        ));
        $this->changed = new \stdClass();
        $this->setAuto();
        if(!is_null($order_id)){
            $this->setId($order_id);
        }
    }


    /**
     * @param $api
     */
    public function setApi($api)
    {
        if(isset($api) && is_object($api)){
            if($api instanceof \Genoo\Api || $api instanceof \WPMKTENGINE\Api || $api instanceof \WPME\ApiFactory){
                $this->api = $api;
            }
        }
    }

    /**
     * @param $id
     */
    public function setId($id){ $this->id = $id; }

    /**
     * @return bool
     */
    public function isApiSet()
    {
        if(isset($this->api) && !empty($this->api)){
            if($this->api instanceof \Genoo\Api || $this->api instanceof \WPMKTENGINE\Api || $api instanceof \WPME\ApiFactory){
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }

    /**
     * @param array $items
     *
     * @throws \Exception
     */
    public function startCart($items = array())
    {
        if(!$this->isApiSet()){
            throw new \Exception('Api not set, you need to set API using CartOrder::setApi() first.');
        }
        // Add first Items
        $this->addItemsArray($items);
        // Create first cart with API
        $this->actionNewCart();
        // Start cart in API, and save ID
        try {
            $result = $this->api->setCart((array)$this->getPayload());
            if(is_object($result) && $result->result == 'success'){
                // Ok, we have an order object created
                $this->setId($result->order_id);
            }
        } catch (\Exception $e){
            if(function_exists('wpme_simple_log')){
                wpme_simple_log('ERROR Starting CART : ' . $e->getMessage());
            }
        }
    }

    /**
     * @param bool $finish
     *
     * @return bool|object|string
     */
    public function updateOrder($finish = FALSE)
    {
        try {
            $this->order_status = $finish === TRUE ? 'order' : 'cart';
            $result = $this->api->updateCart($this->id, (array)$this->getPayload());
            return $result;
        } catch (\Exception $e){
            if(function_exists('wpme_simple_log')){
                wpme_simple_log('ERROR UPDATING ORDER : ' . $e->getMessage());
            }
        }
        return FALSE;
    }

    /**
     * Starts new order from scratch
     *
     * @return bool
     */
    public function startNewOrder()
    {
        try {
            $this->order_status = 'order';
            $this->changed->order_status = 'order';
            $this->action = 'new order';
            $this->changed->action = 'new order';
            $result = $this->api->setCart((array)$this->getPayload());
            if(is_object($result) && $result->result == 'success'){
                // Ok, we have an order object created
                $this->setId($result->order_id);
            }
            return $result;
        } catch (\Exception $e){
            wpme_simple_log('ERROR STARTING NEW ORDER : ' . $e->getMessage());
        }
        return FALSE;
    }

    /**
     * Start new cart action
     */
    public function actionNewCart()
    {
        $this->action = 'new cart';
        $this->changed->action = 'new cart';
        $this->order_number = NULL;
        $this->changed->order_number = NULL;
        $this->order_status = 'cart';
        $this->changed->order_status = 'cart';
    }

    /**
     * New order (not the band)
     */
    public function actionNewOrder()
    {
        $this->action = 'new order';
        $this->changed->action = 'new order';
    }

    /**
     * Paid
     */
    public function actionOrderFullfillment()
    {
        $this->action = 'order fulfillment';
        $this->changed->action = 'order fulfillment';
        $this->financial_status = 'paid';
        $this->changed->financial_status = 'paid';
        $this->completed_date = \WPME\Ecommerce\Utils::getDateTime();
        $this->changed->completed_date = \WPME\Ecommerce\Utils::getDateTime();
    }

    /**
     * @param string $reason
     */
    public function actionRefundFull($reason = '')
    {
        $this->action = 'order refund full';
        $this->changed->action = 'order refund full';
        $this->refund_reason = $reason;
        $this->changed->refund_reason = $reason;
        $this->refund_date = \WPME\Ecommerce\Utils::getDateTime();
        $this->changed->refund_date = \WPME\Ecommerce\Utils::getDateTime();

    }

    /**
     * @param string $reason
     * @param int    $amount
     */
    public function actionRefundPartial($reason = '', $amount = 0)
    {
        $this->action = 'order refund partial';
        $this->changed->action = 'order refund partial';
        $this->refund_reason = $reason;
        $this->changed->refund_reason = $reason;
        $this->refund_date = \WPME\Ecommerce\Utils::getDateTime();
        $this->changed->refund_date = \WPME\Ecommerce\Utils::getDateTime();
        $this->refund_amount = $amount;
        $this->changed->refund_amount = $amount;
    }

    /**
     * @param string $reason
     */
    public function actionCancelled($reason = '')
    {
        $this->action = 'order cancelled';
        $this->changed->action = 'order cancelled';
        $this->cancel_reason = $reason;
        $this->changed->cancel_reason = $reason;
        $this->cancel_date = \WPME\Ecommerce\Utils::getDateTime();
        $this->changed->cancel_date = \WPME\Ecommerce\Utils::getDateTime();
    }

    /**
     * @param $total
     */
    public function setTotal($total)
    {
        $this->total_price = (int)$total;
        $this->changed->total_price = (int)$total;
    }

    /**
     * @param $id
     */
    public function setUser($id)
    {
        $this->user_lid = $id;
        $this->changed->user_lid = $id;
    }

    /**
     * @param array $items
     */
    public function addItemsArray($items = array())
    {
        $this->line_items = $items;
        $this->changed->line_items = $items;
    }

    /**
     * Set Auto values
     * those who change automatically
     */
    public function setAuto()
    {
        $this->ip = \WPME\Ecommerce\Utils::getIpAddress();
    }


    /**
     * @return bool
     */
    public function isPaid(){ return $this->object->financial_status === self::STATUS_PAID; }

    /**
     * @return $this
     */
    public function setToPaid()
    {
        $this->financial_status = self::STATUS_PAID;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShipped(){ return $this->object->fulfillment_status === self::STATUS_FULFILLMENT; }

    /**
     * @return $this
     */
    public function setToShipped()
    {
        $this->fulfillment_status = self::STATUS_FULFILLMENT;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted(){ return $this->isOrder() && $this->isShipped() && $this->isPaid(); }


    /**
     * Set order as completed at a date
     *
     * @param null $date
     * @return $this
     */
    public function setToCompleted($date = NULL)
    {
        $this->setToPaid();
        $this->setToShipped();
        $this->setToCart();
        $this->completed_date = \WPME\Ecommerce\Utils::getDateTime($date);
        return $this;
    }

    /**
     * @return bool
     */
    public function isOrder()
    {
        return $this->object->order_status === self::STATUS_ORDER;
    }

    /**
     * @param $order_id
     * @return $this
     */
    public function setToOrder($order_id)
    {
        $this->order_status = self::STATUS_ORDER;
        $this->order_number = $order_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCart()
    {
        return $this->object->order_status === self::STATUS_CART;
    }

    /**
     * @return $this
     */
    public function setToCart()
    {
        $this->order_status = self::STATUS_CART;
        return $this;
    }


    public function setCartProducts($products)
    {
        $this->line_items = $products;
    }


    /**
     * @return mixed
     */
    public function getItems(){ return $this->object->line_items; }

    /**
     * @return mixed
     */
    public function getStatus(){ return $this->object->order_status; }

    /**
     * @return object
     */
    public function getObject(){ return $this->object; }

    /**
     * Get payload to be saved, if set to true, returns full object (for order saving)
     * returnes only changed data for updating.
     *
     * @param bool $full
     * @return object
     */
    public function getPayload($full = FALSE)
    {
        if($full){
            return $this->object;
        }
        return $this->changed;
    }

    /**
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $country
     * @param string $phone
     * @param string $postal_code
     * @param string $province
     * @param string $state
     * @return $this
     */
    public function setBillingAddress($address1 = '', $address2 = '', $city = '', $country = '', $phone = '', $postal_code = '', $province = '', $state = '')
    {
        $this->changed->billing_address = new \stdClass();
        $this->billing_address->address1 = $address1;
        $this->billing_address->address2 = $address2;
        $this->billing_address->city = $city;
        $this->billing_address->country = $country;
        $this->billing_address->phone = $phone;
        $this->billing_address->postal_code = $postal_code;
        $this->billing_address->province = $province;
        $this->billing_address->state = $state;
        // Changed
        $this->changed->billing_address->address1 = $address1;
        $this->changed->billing_address->address2 = $address2;
        $this->changed->billing_address->city = $city;
        $this->changed->billing_address->country = $country;
        $this->changed->billing_address->phone = $phone;
        $this->changed->billing_address->postal_code = $postal_code;
        $this->changed->billing_address->province = $province;
        $this->changed->billing_address->state = $state;
        return $this;
    }

    /**
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $country
     * @param string $phone
     * @param string $postal_code
     * @param string $province
     * @param string $state
     * @return $this
     */
    public function setShippingAddress($address1 = '', $address2 = '', $city = '', $country = '', $phone = '', $postal_code = '', $province = '', $state = '')
    {
        $this->changed->shipping_address = new \stdClass();
        $this->shipping_address->address1 = $address1;
        $this->shipping_address->address2 = $address2;
        $this->shipping_address->city = $city;
        $this->shipping_address->country = $country;
        $this->shipping_address->phone = $phone;
        $this->shipping_address->postal_code = $postal_code;
        $this->shipping_address->province = $province;
        $this->shipping_address->state = $state;
        // Changed
        $this->changed->shipping_address->address1 = $address1;
        $this->changed->shipping_address->address2 = $address2;
        $this->changed->shipping_address->city = $city;
        $this->changed->shipping_address->country = $country;
        $this->changed->shipping_address->phone = $phone;
        $this->changed->shipping_address->postal_code = $postal_code;
        $this->changed->shipping_address->province = $province;
        $this->changed->shipping_address->state = $state;
        return $this;
    }

    /**
     * Set shipping address same as billing
     * @return $this
     */
    public function setAddressShippingSameAsBilling()
    {
        $this->shipping_address = $this->billing_address;
        return $this;
    }

    /**
     * Set shipping address same as billing
     * @return $this
     */
    public function setAddressBillingSameAsShipping()
    {
        $this->billing_address = $this->shipping_address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function __toString(){ return $this->id; }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        if(isset($this->object->{$key})){
            return $this->object->{$key};
        }
        throw new \Exception('No such attribute in the object');
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        // Save to global object
        $this->object->{$key} = $value;
        // Payload for order update
        $this->changed->{$key} = $value;
    }


    public function __call($method, $arguments){}
}